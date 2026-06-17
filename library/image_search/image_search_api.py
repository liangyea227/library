"""
image_search_api.py  -  Book Cover Image Search API
====================================================
Part of Lim Library FYP.
Uses MobileNetV2 CNN to match an uploaded photo against all book
cover images stored in the database.

Run via launcher (recommended):
    python start_ai.py          (starts BOTH AI services together)

Or standalone:
    python image_search_api.py  (port 5001 only)

Endpoint:
    POST /search-by-image
    multipart/form-data key: "image"

Returns:
    { "success": true,  "book": { ... }, "confidence": 0.94 }
    { "success": false, "message": "..." }
"""

from __future__ import annotations  # Python 3.7+ compatible type hints

import os
import io
import json
import logging
import numpy as np
import pymysql
from flask import Flask, request, jsonify
from flask_cors import CORS
from PIL import Image

# Silence TensorFlow startup messages before importing
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"
import tensorflow as tf
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.applications.mobilenet_v2 import preprocess_input
from tensorflow.keras.preprocessing.image import img_to_array

# ---------------------------------------------------------------------------
# App setup
# ---------------------------------------------------------------------------
app = Flask(__name__)
CORS(app)
logging.basicConfig(level=logging.INFO, format="%(levelname)s  %(message)s")
log = logging.getLogger(__name__)

# ---------------------------------------------------------------------------
# Configuration  -  edit DB password if your MySQL has one
# ---------------------------------------------------------------------------
DB_CONFIG = {
    "host":        "localhost",
    "user":        "root",
    "password":    "",          # add your MySQL password here if needed
    "db":          "library",
    "charset":     "utf8",
    "cursorclass": pymysql.cursors.DictCursor,
}

# Path to book cover images  (admin/bookimg/)
BOOK_IMG_DIR = os.path.abspath(
    os.path.join(os.path.dirname(__file__), "..", "admin", "bookimg")
)

# Similarity thresholds for partial/cropped image matching:
#   MATCH:      >= 0.60  full confidence result
#   BEST GUESS: >= 0.35  shows result with a "low confidence" warning
#   NO MATCH:   <  0.35  truly unrecognisable image
SIMILARITY_THRESHOLD      = 0.60   # confirmed match
SIMILARITY_BEST_GUESS     = 0.35   # show best guess with warning

# Cache file: stores computed embeddings so restarts are instant
# Delete this file to force a full rebuild
EMBED_CACHE = os.path.join(os.path.dirname(__file__), "embeddings_cache.json")

# ---------------------------------------------------------------------------
# Load CNN model once at startup
# ---------------------------------------------------------------------------
log.info("Loading MobileNetV2 model ...")
base_model = MobileNetV2(
    weights="imagenet",
    include_top=False,
    pooling="avg",
    input_shape=(224, 224, 3),
)
base_model.trainable = False
log.info("MobileNetV2 ready.")


# ---------------------------------------------------------------------------
# Helper functions
# ---------------------------------------------------------------------------

def extract_features(image_bytes: bytes) -> np.ndarray:
    """Convert raw image bytes to an L2-normalised CNN feature vector."""
    img  = Image.open(io.BytesIO(image_bytes)).convert("RGB").resize((224, 224))
    arr  = img_to_array(img)
    arr  = np.expand_dims(arr, axis=0)
    arr  = preprocess_input(arr)
    feat = base_model.predict(arr, verbose=0)[0]
    norm = np.linalg.norm(feat)
    return (feat / norm) if norm > 0 else feat


def cosine_similarity(a: np.ndarray, b: np.ndarray) -> float:
    """Return cosine similarity. Both vectors must be L2-normalised."""
    return float(np.dot(a, b))


def get_db():
    """Open and return a new database connection."""
    return pymysql.connect(**DB_CONFIG)


def load_book_embeddings() -> list:
    """
    Fetch all books from the database, compute (or load from cache)
    a CNN embedding for each cover image, and return a list of:
        { "book": <db row dict>, "embedding": <np.ndarray> }
    """
    # 1. Load all books with author and category
    # Detect actual columns first to handle DB version differences
    conn = get_db()
    try:
        with conn.cursor() as cur:
            cur.execute("SHOW COLUMNS FROM tblbooks")
            existing_cols = {row["Field"] for row in cur.fetchall()}

        price_col = "b.BookPrice" if "BookPrice" in existing_cols else "NULL"

        with conn.cursor() as cur:
            cur.execute(f"""
                SELECT b.id,
                       b.BookName,
                       b.bookImage,
                       b.ISBNNumber,
                       {price_col} AS BookPrice,
                       b.bookQty,
                       b.isIssued,
                       a.AuthorName,
                       c.CategoryName
                FROM   tblbooks b
                LEFT JOIN tblauthors  a ON a.id = b.AuthorId
                LEFT JOIN tblcategory c ON c.id = b.CatId
            """)
            books = cur.fetchall()
    finally:
        conn.close()

    # 2. Load embedding cache if available
    cache = {}
    if os.path.exists(EMBED_CACHE):
        try:
            with open(EMBED_CACHE) as f:
                raw = json.load(f)
            cache = {k: np.array(v) for k, v in raw.items()}
            log.info("Loaded %d cached embeddings.", len(cache))
        except Exception as e:
            log.warning("Cache unreadable (%s) - rebuilding.", e)
            cache = {}

    results     = []
    cache_dirty = False

    for book in books:
        img_name = book.get("bookImage") or ""
        img_path = os.path.join(BOOK_IMG_DIR, img_name)

        if not img_name or not os.path.exists(img_path):
            log.debug("Skipping book id=%s - image not found: %s", book["id"], img_path)
            continue

        cache_key = f"{book['id']}:{img_name}"

        if cache_key in cache:
            embedding = cache[cache_key]
        else:
            try:
                with open(img_path, "rb") as fh:
                    embedding = extract_features(fh.read())
                cache[cache_key] = embedding
                cache_dirty      = True
                log.info("Embedded book id=%s  (%s)", book["id"], img_name)
            except Exception as e:
                log.warning("Could not embed book id=%s: %s", book["id"], e)
                continue

        results.append({"book": book, "embedding": embedding})

    # 3. Save updated cache to disk
    if cache_dirty:
        try:
            with open(EMBED_CACHE, "w") as f:
                json.dump({k: v.tolist() for k, v in cache.items()}, f)
            log.info("Embedding cache saved (%d entries).", len(cache))
        except Exception as e:
            log.warning("Could not save cache: %s", e)

    log.info("Books indexed: %d", len(results))
    return results


# ---------------------------------------------------------------------------
# Pre-load embeddings at startup
# ---------------------------------------------------------------------------
log.info("Indexing book covers from database ...")
BOOK_EMBEDDINGS = load_book_embeddings()
log.info("Image search ready - %d books indexed.", len(BOOK_EMBEDDINGS))


# ---------------------------------------------------------------------------
# Routes
# ---------------------------------------------------------------------------

@app.route("/health", methods=["GET"])
def health():
    """Quick health check endpoint."""
    db_ok = True
    try:
        c = get_db()
        c.close()
    except Exception:
        db_ok = False

    return jsonify({
        "status":        "ok" if db_ok else "db_error",
        "books_indexed": len(BOOK_EMBEDDINGS),
        "db":            "connected" if db_ok else "ERROR - check MySQL",
        "model":         "MobileNetV2",
    })


@app.route("/reload-embeddings", methods=["POST"])
def reload_embeddings():
    """
    Call this after adding new books so new covers are indexed
    without restarting the server.
    """
    global BOOK_EMBEDDINGS
    if os.path.exists(EMBED_CACHE):
        os.remove(EMBED_CACHE)
    BOOK_EMBEDDINGS = load_book_embeddings()
    return jsonify({"success": True, "books_indexed": len(BOOK_EMBEDDINGS)})


@app.route("/search-by-image", methods=["POST"])
def search_by_image():
    """
    Accept a book cover photo and return the closest matching book.
    Form-data key: "image"
    """
    if "image" not in request.files:
        return jsonify({
            "success": False,
            "message": "No image uploaded. Use form-data key 'image'."
        }), 400

    file = request.files["image"]
    if not file or file.filename == "":
        return jsonify({"success": False, "message": "Empty file received."}), 400

    # Validate file extension
    allowed_ext = {".jpg", ".jpeg", ".png", ".gif", ".webp", ".bmp"}
    ext = os.path.splitext(file.filename)[1].lower()
    if ext not in allowed_ext:
        return jsonify({
            "success": False,
            "message": f"Unsupported format '{ext}'. Please use JPG, PNG, or WEBP."
        }), 400

    # Extract CNN features from uploaded photo
    try:
        query_bytes = file.read()
        query_emb   = extract_features(query_bytes)
    except Exception as e:
        log.error("Feature extraction failed: %s", e)
        return jsonify({
            "success": False,
            "message": "Could not read the image. Please try a clearer photo."
        }), 500

    if not BOOK_EMBEDDINGS:
        return jsonify({
            "success": False,
            "message": "No book covers indexed yet. Please add book images via the admin panel."
        }), 503

    # Find the book with the highest cosine similarity score
    best_score = -1.0
    best_entry = None
    for entry in BOOK_EMBEDDINGS:
        score = cosine_similarity(query_emb, entry["embedding"])
        if score > best_score:
            best_score = score
            best_entry = entry

    log.info(
        "Best match -> id=%s  score=%.4f  title=%s",
        best_entry["book"]["id"]       if best_entry else "?",
        best_score,
        best_entry["book"]["BookName"] if best_entry else "?",
    )

    # Completely unrecognisable - score too low even for a guess
    if best_score < SIMILARITY_BEST_GUESS:
        return jsonify({
            "success":    False,
            "message": (
                f"Could not recognise this image (similarity: {best_score:.0%}). "
                "Try cropping closer to the book cover, or improve lighting."
            ),
            "best_score": round(best_score, 4),
        })

    book = best_entry["book"]

    # Decide match quality label based on score
    if best_score >= SIMILARITY_THRESHOLD:
        match_label   = "High confidence"
        is_best_guess = False
    elif best_score >= 0.50:
        match_label   = "Possible match"
        is_best_guess = True
    else:
        match_label   = "Low confidence - best guess"
        is_best_guess = True

    return jsonify({
        "success":       True,
        "confidence":    round(best_score, 4),
        "is_best_guess": is_best_guess,
        "match_label":   match_label,
        "book": {
            "id":           book["id"],
            "BookName":     book["BookName"],
            "AuthorName":   book["AuthorName"]   or "Unknown",
            "CategoryName": book["CategoryName"] or "-",
            "ISBNNumber":   book["ISBNNumber"]   or "-",
            "BookPrice":    float(book["BookPrice"]) if book["BookPrice"] else 0,
            "bookQty":      book["bookQty"]      or 0,
            "isIssued":     book["isIssued"],
            "bookImage":    book["bookImage"],
        },
    })


# ---------------------------------------------------------------------------
# Entry point
# ---------------------------------------------------------------------------
if __name__ == "__main__":
    print("=" * 55)
    print("  Image Search API  -  Lim Library")
    print(f"  Running at  http://localhost:5001")
    print(f"  Books indexed: {len(BOOK_EMBEDDINGS)}")
    print(f"  Book images:   {BOOK_IMG_DIR}")
    print("=" * 55)
    app.run(host="0.0.0.0", port=5001, debug=False)