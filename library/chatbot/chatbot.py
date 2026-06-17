"""
Library AI Chatbot  -  Flask Backend (FREE VERSION)
====================================================
Uses Ollama (free, runs locally) instead of the paid Anthropic Claude API.
No API key required — everything runs on your own computer.

HOW TO SET UP (one-time only):
    1. Download & install Ollama from https://ollama.com
    2. Open a terminal and run:   ollama pull llama3.2
       (downloads the free AI model, ~2 GB, one time only)
    3. pip install -r requirements.txt
    4. python -m spacy download en_core_web_sm   (optional, for better NLP)
    5. Start XAMPP (MySQL must be running)
    6. python chatbot.py

That's it — no paid API key needed, works 100% offline.

ALTERNATIVE FREE MODELS (just change OLLAMA_MODEL below):
    - "llama3.2"        (recommended, 2GB, fast)
    - "mistral"         (good for chat, 4GB)
    - "gemma2:2b"       (very lightweight, 1.6GB)
    - "phi3:mini"       (tiny but capable, 2.3GB)
"""

import os
import re
import json
import urllib.request
import urllib.error
import mysql.connector
from flask import Flask, request, jsonify
from flask_cors import CORS
from rapidfuzz import process as fuzz_process, fuzz
from dotenv import load_dotenv

# ── spaCy (optional but recommended) ─────────────────────────────────────────
try:
    import spacy
    nlp = spacy.load("en_core_web_sm")
    SPACY_LOADED = True
except Exception:
    SPACY_LOADED = False

load_dotenv()

app = Flask(__name__)
CORS(app)

# ── Configuration ──────────────────────────────────────────────────────────────
DB_CONFIG = {
    "host":     os.getenv("DB_HOST", "localhost"),
    "port":     int(os.getenv("DB_PORT", 3306)),
    "user":     os.getenv("DB_USER", "root"),
    "password": os.getenv("DB_PASS", ""),
    "database": os.getenv("DB_NAME", "library"),
}

# Ollama settings — no API key needed!
OLLAMA_URL   = os.getenv("OLLAMA_URL", "http://localhost:11434")
OLLAMA_MODEL = os.getenv("OLLAMA_MODEL", "llama3.2")   # Change model here if needed

# ── Database helpers ───────────────────────────────────────────────────────────

def get_db():
    return mysql.connector.connect(**DB_CONFIG)

def query(sql, params=()):
    conn = get_db()
    cur  = conn.cursor(dictionary=True)
    cur.execute(sql, params)
    rows = cur.fetchall()
    cur.close()
    conn.close()
    return rows

# ── Live DB fetch functions ────────────────────────────────────────────────────

def get_all_books():
    return query("""
        SELECT b.id, b.BookName, b.ISBNNumber, b.bookQty,
               a.AuthorName, c.CategoryName,
               (SELECT COUNT(*) FROM tblissuedbookdetails i
                WHERE i.BookId = b.id
                  AND (i.RetrunStatus=0 OR i.RetrunStatus IS NULL OR i.RetrunStatus='')
               ) AS issuedCount
        FROM tblbooks b
        LEFT JOIN tblauthors a ON b.AuthorId = a.id
        LEFT JOIN tblcategory c ON b.CatId   = c.id
        ORDER BY b.BookName ASC
    """)

def get_categories():
    # Always fetch live from DB — no hardcoded list anywhere
    return query(
        "SELECT id, CategoryName FROM tblcategory WHERE Status=1 ORDER BY CategoryName ASC"
    )

def get_authors():
    return query("SELECT id, AuthorName FROM tblauthors ORDER BY AuthorName ASC")

def get_student_borrowed_books(student_id):
    return query("""
        SELECT b.BookName, a.AuthorName, c.CategoryName,
               iss.IssuesDate, iss.RetrunStatus
        FROM tblissuedbookdetails iss
        JOIN tblbooks b  ON iss.BookId  = b.id
        LEFT JOIN tblauthors a  ON b.AuthorId = a.id
        LEFT JOIN tblcategory c ON b.CatId    = c.id
        WHERE iss.StudentID = %s
          AND (iss.RetrunStatus=0 OR iss.RetrunStatus IS NULL OR iss.RetrunStatus='')
        ORDER BY iss.IssuesDate DESC
    """, (student_id,))

def get_available_books():
    return query("""
        SELECT b.id, b.BookName, b.bookQty, a.AuthorName, c.CategoryName,
               (SELECT COUNT(*) FROM tblissuedbookdetails i
                WHERE i.BookId = b.id
                  AND (i.RetrunStatus=0 OR i.RetrunStatus IS NULL OR i.RetrunStatus='')
               ) AS issuedCount
        FROM tblbooks b
        LEFT JOIN tblauthors a ON b.AuthorId = a.id
        LEFT JOIN tblcategory c ON b.CatId   = c.id
        HAVING (b.bookQty - issuedCount) > 0
        ORDER BY b.BookName ASC
    """)

def get_books_by_category(category_name):
    """
    FIX: Fetch books for a specific category directly from the DB.
    This avoids the AI hallucinating books — the results come purely
    from the database, so the titles and availability are always accurate.
    """
    return query("""
        SELECT b.id, b.BookName, b.ISBNNumber, b.bookQty,
               a.AuthorName, c.CategoryName,
               (SELECT COUNT(*) FROM tblissuedbookdetails i
                WHERE i.BookId = b.id
                  AND (i.RetrunStatus=0 OR i.RetrunStatus IS NULL OR i.RetrunStatus='')
               ) AS issuedCount
        FROM tblbooks b
        LEFT JOIN tblauthors a ON b.AuthorId = a.id
        LEFT JOIN tblcategory c ON b.CatId   = c.id
        WHERE LOWER(c.CategoryName) = LOWER(%s)
          AND c.Status = 1
        ORDER BY b.BookName ASC
    """, (category_name,))

def get_new_books(days=30):
    """Return books added within the last `days` days, newest first."""
    return query("""
        SELECT b.id, b.BookName, b.ISBNNumber, b.bookQty, b.RegDate,
               a.AuthorName, c.CategoryName,
               (SELECT COUNT(*) FROM tblissuedbookdetails i
                WHERE i.BookId = b.id
                  AND (i.RetrunStatus=0 OR i.RetrunStatus IS NULL OR i.RetrunStatus='')
               ) AS issuedCount
        FROM tblbooks b
        LEFT JOIN tblauthors a ON b.AuthorId = a.id
        LEFT JOIN tblcategory c ON b.CatId   = c.id
        WHERE b.RegDate >= DATE_SUB(NOW(), INTERVAL %s DAY)
        ORDER BY b.RegDate DESC
    """, (days,))

def get_new_categories(days=30):
    """Return categories created within the last `days` days, newest first."""
    return query("""
        SELECT id, CategoryName, CreationDate
        FROM tblcategory
        WHERE Status=1
          AND CreationDate >= DATE_SUB(NOW(), INTERVAL %s DAY)
        ORDER BY CreationDate DESC
    """, (days,))

# ── Build synonym map dynamically from DB ──────────────────────────────────────
# Instead of a hardcoded CATEGORY_SYNONYMS dict, we build it live from the
# real category names in the database. This means any new category you add
# automatically works — no code changes needed.

def build_category_synonyms(categories):
    """
    For each category name in the DB, auto-generate common synonym tokens.
    e.g. "Science Fiction" → {"science": "Science Fiction", "fiction": "Science Fiction", ...}
    Also keeps the original single-word mapping for robustness.
    """
    synonyms = {}
    for cat in categories:
        name = cat["CategoryName"]
        # The category name itself (lowercase) always maps to itself
        synonyms[name.lower()] = name
        # Each individual word in multi-word categories also maps to it
        for word in name.lower().split():
            if len(word) > 2:  # skip tiny words like "of", "in", "a"
                synonyms[word] = name
    return synonyms

# ── Intent keywords ───────────────────────────────────────────────────────────
INTENT_KEYWORDS = {
    "available":      ["available", "in stock", "can borrow", "got copies", "left"],
    "recommend":      ["recommend", "suggest", "suggestion", "what should", "good book",
                       "nice book", "best book", "popular", "any book"],
    "borrow":         ["borrow", "loan", "take out", "check out", "get a book"],
    "return":         ["return", "give back", "bring back", "how to return"],
    "my_loans":       ["my loan", "my borrow", "i borrowed", "i have", "currently borrowing",
                       "what i took", "my book", "my books"],
    "search_title":   ["find", "search", "look for", "looking for", "do you have", "is there",
                       "got a book", "show me"],
    "list_category":  ["list", "all books", "show all", "what books", "books in"],
    "author":         ["author", "written by", "who wrote", "by the author"],
    "new_additions":  ["new book", "new books", "new arrival", "new arrivals", "latest book",
                       "latest books", "recently added", "just added", "what's new",
                       "whats new", "new category", "new categories", "newly added",
                       "added recently", "recent book", "recent books"],
}

# ══════════════════════════════════════════════════════════════════════════════
#  NLP / FUZZY MATCHING LAYER
# ══════════════════════════════════════════════════════════════════════════════

def detect_intent(text):
    lower = text.lower()
    found = []
    for intent, keywords in INTENT_KEYWORDS.items():
        if any(kw in lower for kw in keywords):
            found.append(intent)
    return found if found else ["general"]


def resolve_category_synonyms(text, category_synonyms):
    lower  = text.lower()
    tokens = re.findall(r"[a-z]+", lower)
    hits   = set()
    for token in tokens:
        if token in category_synonyms:
            hits.add(category_synonyms[token])
    return list(hits)


def fuzzy_match_books(user_text, all_books, threshold=65):
    if not all_books:
        return []

    book_titles  = [b["BookName"]   for b in all_books]
    author_names = [b["AuthorName"] for b in all_books]
    matched_ids  = set()

    title_hits = fuzz_process.extract(
        user_text, book_titles,
        scorer=fuzz.partial_ratio, limit=5, score_cutoff=threshold
    )
    for _, score, idx in title_hits:
        matched_ids.add(idx)

    author_hits = fuzz_process.extract(
        user_text, author_names,
        scorer=fuzz.partial_ratio, limit=5, score_cutoff=threshold
    )
    for _, score, idx in author_hits:
        matched_ids.add(idx)

    return [all_books[i] for i in matched_ids]


def fuzzy_match_categories(user_text, categories, threshold=70):
    cat_names = [c["CategoryName"] for c in categories]
    hits = fuzz_process.extract(
        user_text, cat_names,
        scorer=fuzz.partial_ratio, limit=3, score_cutoff=threshold
    )
    return [name for name, score, _ in hits]


def extract_named_entities(text):
    if not SPACY_LOADED:
        return []
    doc = nlp(text)
    return [ent.text for ent in doc.ents
            if ent.label_ in ("PERSON", "ORG", "PRODUCT", "WORK_OF_ART")]


def build_enrichment_note(user_message, all_books, categories, category_synonyms):
    notes = []

    intents = detect_intent(user_message)
    if intents and intents != ["general"]:
        notes.append(f"[Detected intent: {', '.join(intents)}]")

    synonym_cats = resolve_category_synonyms(user_message, category_synonyms)
    if synonym_cats:
        notes.append(f"[User likely means category: {', '.join(synonym_cats)}]")

    fuzzy_cats  = fuzzy_match_categories(user_message, categories)
    extra_cats  = [c for c in fuzzy_cats if c not in synonym_cats]
    if extra_cats:
        notes.append(f"[Fuzzy-matched category: {', '.join(extra_cats)}]")

    fuzzy_books = fuzzy_match_books(user_message, all_books)
    if fuzzy_books:
        titles = [f'"{b["BookName"]}" by {b["AuthorName"]}' for b in fuzzy_books[:4]]
        notes.append(f"[Fuzzy-matched book(s): {'; '.join(titles)}]")

    entities = extract_named_entities(user_message)
    if entities:
        notes.append(f"[Named entities in message: {', '.join(entities)}]")

    return "\n".join(notes)

# ══════════════════════════════════════════════════════════════════════════════
#  FIX: DIRECT DB RESPONSE (bypasses AI for category-based queries)
# ══════════════════════════════════════════════════════════════════════════════

def build_direct_category_response(matched_category_name):
    """
    FIX — ROOT CAUSE SOLUTION:
    When the user asks to recommend or list books in a category, we fetch the
    books directly from the database and build the reply ourselves WITHOUT
    passing it to the AI. This completely prevents the AI from hallucinating
    book titles or wrong availability numbers.

    Before this fix: AI received the DB data but ignored it and invented books
    like "Fire Force" and "That Time I Got Reincarnated as a Slime" with made-up
    copy counts, because local LLMs (LLaMA, etc.) don't reliably follow
    "only use the data I gave you" instructions.

    After this fix: the response is built from real DB rows — titles, authors,
    and available copy counts are always 100% accurate.
    """
    books = get_books_by_category(matched_category_name)

    if not books:
        return (
            f"Sorry, we currently have no books in the **{matched_category_name}** category. "
            "Please check back later or ask me about another category!"
        )

    lines = []
    for b in books:
        avail = (b["bookQty"] or 0) - (b["issuedCount"] or 0)
        if avail > 0:
            status = f"✅ {avail} cop{'y' if avail == 1 else 'ies'} available"
        else:
            status = "❌ Currently not available"
        lines.append(
            f"• **{b['BookName']}** by {b['AuthorName']}\n"
            f"  ISBN: {b['ISBNNumber']} | {status}"
        )

    header = (
        f"Here are the **{matched_category_name}** books we have in our library:\n\n"
    )
    footer = (
        "\n\nTo borrow a book, click on it from the dashboard and select **Borrow**. "
        "Need help finding something specific? Just ask! 😊"
    )
    return header + "\n\n".join(lines) + footer


def try_direct_response(user_message, intents, categories, category_synonyms):
    """
    FIX: Check if the query is a category-based recommend/list/available request.
    If yes, resolve the category from the DB and return a direct response —
    no AI involved, so no hallucination possible.

    Returns a string reply if we can handle it directly, or None to fall
    through to the normal AI path.
    """
    is_category_query = any(i in intents for i in ("recommend", "list_category", "available"))
    if not is_category_query:
        return None  # not a category query — let the AI handle it

    # Try to resolve the category from the user's message
    synonym_cats = resolve_category_synonyms(user_message, category_synonyms)
    fuzzy_cats   = fuzzy_match_categories(user_message, categories, threshold=65)

    # Merge both lists, synonym hits take priority
    candidate_cats = synonym_cats[:]
    for c in fuzzy_cats:
        if c not in candidate_cats:
            candidate_cats.append(c)

    if not candidate_cats:
        return None  # no category recognised — let AI handle general queries

    # Use the first (best) matched category
    matched_category = candidate_cats[0]
    return build_direct_category_response(matched_category)

# ── Build library context string ───────────────────────────────────────────────

def build_library_context():
    # Every call to this function re-queries the database fresh.
    # No caching, no static lists — always reflects the current state of the DB.
    books      = get_all_books()
    categories = get_categories()
    authors    = get_authors()

    lines = []
    for b in books:
        avail  = (b["bookQty"] or 0) - (b["issuedCount"] or 0)
        status = "Available" if avail > 0 else "Not Available"
        lines.append(
            f'  - [{b["CategoryName"]}] "{b["BookName"]}" by {b["AuthorName"]}'
            f' | ISBN: {b["ISBNNumber"]} | Qty: {b["bookQty"]}'
            f' | Copies Available: {avail} | Status: {status}'
        )

    context = (
        "LIBRARY DATABASE (live, real-time data from MySQL — trust this list only):\n\n"
        "CATEGORIES: " + ", ".join(c["CategoryName"] for c in categories) + "\n\n"
        "AUTHORS: "    + ", ".join(a["AuthorName"]   for a in authors)    + "\n\n"
        "BOOKS IN LIBRARY:\n" + "\n".join(lines)
    )

    # ── NEW ADDITIONS section (last 30 days) ──────────────────────────────────
    new_books = get_new_books(days=30)
    new_cats  = get_new_categories(days=30)

    if new_books or new_cats:
        context += "\n\n── NEW ADDITIONS (last 30 days) ──"

    if new_cats:
        cat_lines = [
            f'  - [NEW CATEGORY] "{c["CategoryName"]}" (added {c["CreationDate"]})'
            for c in new_cats
        ]
        context += "\nNEWLY ADDED CATEGORIES:\n" + "\n".join(cat_lines)

    if new_books:
        book_lines = []
        for b in new_books:
            avail  = (b["bookQty"] or 0) - (b["issuedCount"] or 0)
            status = "Available" if avail > 0 else "Not Available"
            book_lines.append(
                f'  - [NEW] [{b["CategoryName"]}] "{b["BookName"]}" by {b["AuthorName"]}'
                f' | Added: {b["RegDate"]} | Copies Available: {avail} | Status: {status}'
            )
        context += "\nNEWLY ADDED BOOKS:\n" + "\n".join(book_lines)

    if not new_books and not new_cats:
        context += "\n\nNEW ADDITIONS (last 30 days): None."

    return context

# ── System prompt ──────────────────────────────────────────────────────────────

SYSTEM_PROMPT_TEMPLATE = """\
You are "LibraBot", a friendly and intelligent AI library assistant for Lim Library.
Your job is to help students find books, check availability, get recommendations, and answer library-related questions.

{library_context}

CRITICAL RULES — YOU MUST FOLLOW THESE:
1. The LIBRARY DATABASE above is the ONLY source of truth. It is fetched live from the database every time.
2. ONLY recommend or mention books that are explicitly listed in the BOOKS IN LIBRARY section above.
3. NEVER invent, guess, or suggest any book title, author, or ISBN that is not in the list above.
4. NEVER rely on your own training knowledge to name books — only use what is in the database above.
5. If a category the user asks about has no books in the list, say: "We currently have no books in that category."
6. If a specific book the user asks about is not in the list, say: "That book is not in our collection."
7. When a user asks about new books, new arrivals, latest additions, or what's new — look at the
   "NEW ADDITIONS (last 30 days)" section above and list those books and/or categories. If that
   section says "None", tell the user no new books have been added in the last 30 days.

UNDERSTANDING USER INTENT:
- Each user message may include [bracketed analysis notes] at the top.
  Use these hints to understand what the user means even if they spelled something wrong.
- If the user uses informal words like "coding books" or "money books", use the hints to find the right category.
- Never tell the user you are using fuzzy matching. Just answer naturally.
- If the user's spelling is wrong but you can tell what they mean, answer correctly without mentioning the mistake.

GUIDELINES:
8.  When recommending books, show: Title, Author, Category, and Copies Available.
9.  If a book has 0 copies available, say so and suggest alternatives from the same category (only from the list).
10. Be warm, helpful, and concise. Use bullet points for lists.
11. Borrowing: click the book on the dashboard then click Borrow. Returning: go to the library counter.
12. If asked something unrelated to the library, politely redirect.
13. Keep answers focused and not too long.
14. Be conversational and friendly.

Answer the user's question directly and helpfully using ONLY the books and categories in the database above.
"""

# ══════════════════════════════════════════════════════════════════════════════
#  OLLAMA AI CALL (free, local)
# ══════════════════════════════════════════════════════════════════════════════

def call_ollama(system_prompt, messages):
    payload = {
        "model": OLLAMA_MODEL,
        "stream": False,
        "options": {
            "temperature": 0.3,   # lowered from 0.7 → reduces hallucination/invention
            "num_predict": 1024,
        },
        "messages": [
            {"role": "system", "content": system_prompt},
            *messages
        ]
    }

    data    = json.dumps(payload).encode("utf-8")
    req     = urllib.request.Request(
        f"{OLLAMA_URL}/api/chat",
        data=data,
        headers={"Content-Type": "application/json"},
        method="POST"
    )

    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            body = json.loads(resp.read().decode("utf-8"))
            return body["message"]["content"]
    except urllib.error.URLError as e:
        raise ConnectionError(
            f"Cannot connect to Ollama at {OLLAMA_URL}. "
            "Make sure Ollama is installed and running. "
            "Download from https://ollama.com then run: ollama serve"
        ) from e


def check_ollama_running():
    try:
        req = urllib.request.Request(f"{OLLAMA_URL}/api/tags", method="GET")
        with urllib.request.urlopen(req, timeout=5) as resp:
            data   = json.loads(resp.read().decode("utf-8"))
            models = [m["name"].split(":")[0] for m in data.get("models", [])]
            target = OLLAMA_MODEL.split(":")[0]
            if target not in models:
                return False, (
                    f"Model '{OLLAMA_MODEL}' not found. "
                    f"Run: ollama pull {OLLAMA_MODEL}"
                )
            return True, "ok"
    except Exception as e:
        return False, str(e)

# ── Chat endpoint ──────────────────────────────────────────────────────────────

@app.route("/chat", methods=["POST"])
def chat():
    data = request.get_json()
    if not data:
        return jsonify({"error": "No JSON body"}), 400

    user_message = data.get("message", "").strip()
    history      = data.get("history", [])
    student_id   = data.get("student_id", "")

    if not user_message:
        return jsonify({"error": "Empty message"}), 400

    # 1. Load live DB data — fresh on every request
    try:
        all_books  = get_all_books()
        categories = get_categories()
        authors    = get_authors()
    except Exception as e:
        return jsonify({"error": f"Database error: {e}"}), 500

    # Build synonym map from live DB categories (not a hardcoded dict)
    category_synonyms = build_category_synonyms(categories)

    # ── FIX: Detect intent FIRST, then try a direct DB response ──────────────
    # This is the key change that stops hallucination.
    # For "recommend / list / available + category" queries, we build the
    # reply straight from a fresh DB query and return it immediately —
    # the AI never gets a chance to invent books or wrong copy counts.
    intents = detect_intent(user_message)
    direct_reply = try_direct_response(user_message, intents, categories, category_synonyms)
    if direct_reply is not None:
        return jsonify({"reply": direct_reply})
    # ── End of direct-response shortcut ──────────────────────────────────────

    # 2. Build library context string (fresh from DB) for the AI path
    try:
        library_context = build_library_context()
    except Exception as e:
        library_context = f"(Could not load library data: {e})"

    # 3. Add student's borrowed books if logged in
    if student_id:
        try:
            borrowed = get_student_borrowed_books(student_id)
            if borrowed:
                lines = "\n".join(
                    f'  - "{b["BookName"]}" by {b["AuthorName"]} (borrowed on {b["IssuesDate"]})'
                    for b in borrowed
                )
                library_context += f"\n\nTHIS STUDENT'S CURRENTLY BORROWED BOOKS:\n{lines}"
            else:
                library_context += "\n\nTHIS STUDENT'S CURRENTLY BORROWED BOOKS: None."
        except Exception:
            pass

    # 4. NLP / fuzzy enrichment — uses live category_synonyms
    try:
        enrichment = build_enrichment_note(user_message, all_books, categories, category_synonyms)
    except Exception:
        enrichment = ""

    enriched_message = (
        f"{enrichment}\n\nUser message: {user_message}" if enrichment else user_message
    )

    # 5. Build message list
    system_prompt = SYSTEM_PROMPT_TEMPLATE.format(library_context=library_context)

    messages = []
    for turn in history[-10:]:
        if turn.get("role") in ("user", "assistant") and turn.get("content"):
            messages.append({"role": turn["role"], "content": turn["content"]})
    messages.append({"role": "user", "content": enriched_message})

    # 6. Call Ollama (free local AI) — only reached for non-category queries
    try:
        reply = call_ollama(system_prompt, messages)
    except ConnectionError as e:
        return jsonify({"error": str(e)}), 503
    except Exception as e:
        return jsonify({"error": f"AI error: {e}"}), 500

    return jsonify({"reply": reply})

# ── REST helpers ───────────────────────────────────────────────────────────────

@app.route("/books/available", methods=["GET"])
def api_available():
    return jsonify({"books": get_available_books()})

@app.route("/books/search", methods=["GET"])
def api_search():
    kw = request.args.get("q", "").strip()
    if not kw:
        return jsonify({"books": []})
    matched = fuzzy_match_books(kw, get_all_books(), threshold=60)
    return jsonify({"books": matched})

@app.route("/books/new", methods=["GET"])
def api_new():
    """Return books and categories added in the last N days (default 30)."""
    try:
        days = int(request.args.get("days", 30))
    except ValueError:
        days = 30
    return jsonify({
        "new_books":      get_new_books(days=days),
        "new_categories": get_new_categories(days=days),
        "days":           days,
    })

@app.route("/health", methods=["GET"])
def health():
    try:
        conn = get_db()
        conn.close()
        db_status = "connected"
    except Exception as e:
        db_status = str(e)

    ollama_ok, ollama_msg = check_ollama_running()

    # Show live DB counts in health check
    try:
        book_count = len(get_all_books())
        cat_count  = len(get_categories())
    except Exception:
        book_count = cat_count = "?"

    return jsonify({
        "status":      "ok" if db_status == "connected" and ollama_ok else "degraded",
        "db":          db_status,
        "books_in_db": book_count,
        "categories":  cat_count,
        "spacy":       SPACY_LOADED,
        "ai":          f"Ollama ({OLLAMA_MODEL})",
        "ollama":      "running" if ollama_ok else f"ERROR: {ollama_msg}",
        "cost":        "FREE — no API key needed!",
    })


if __name__ == "__main__":
    import sys
    sys.stdout.reconfigure(encoding="utf-8")

    print("=" * 60)
    print("  LibraBot  -  Library AI Chatbot  (FREE Version)")
    print(f"  Running at  http://localhost:5000")
    print(f"  AI:         Ollama ({OLLAMA_MODEL})  - FREE, no API key!")
    print(f"  spaCy NLP:  {'loaded' if SPACY_LOADED else 'NOT loaded - run: python -m spacy download en_core_web_sm'}")
    print()

    ollama_ok, ollama_msg = check_ollama_running()
    if ollama_ok:
        print(f"  Ollama:     RUNNING [OK]  model '{OLLAMA_MODEL}' ready")
    else:
        print(f"  Ollama:     NOT RUNNING [ERROR]")
        print(f"  Fix:        {ollama_msg}")
        print(f"  Download:   https://ollama.com")
        print(f"  Then run:   ollama pull {OLLAMA_MODEL}")

    print()
    print("  Make sure XAMPP MySQL is running!")
    print("=" * 60)
    app.run(debug=True, port=5000)