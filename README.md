# Lim Library Management System
### Final Year Project (FYP)

A web-based library management system with two AI features:
- **AI Chatbot** - conversational assistant powered by Ollama (free, local)
- **Book Cover Image Search** - CNN-based visual search using MobileNetV2

---

## Project Structure

```
library/
├── README.md                        <- This file
├── SQL file/
│   └── library.sql                  <- Import this into phpMyAdmin
└── library/
    ├── start_ai.py                  <- START BOTH AI SERVICES HERE
    ├── requirements.txt             <- Install all Python packages (one file)
    │
    ├── chatbot/
    │   ├── chatbot.py               <- AI Chatbot Flask API  (port 5000)
    │   └── .env                     <- Database + Ollama settings
    │
    ├── image_search/
    │   ├── image_search_api.py      <- CNN Image Search Flask API  (port 5001)
    │   └── embeddings_cache.json    <- Auto-generated on first run
    │
    ├── includes/
    │   ├── config.php               <- PHP database connection
    │   ├── header.php               <- Navigation bar (text search + camera button)
    │   └── footer.php
    │
    ├── admin/
    │   └── bookimg/                 <- Book cover images stored here
    │
    └── assets/
        └── js/
            └── librarybot.js        <- Chatbot floating widget (frontend)
```

---

## One-Time Setup

### 1. Install XAMPP
Download from https://www.apachefriends.org and install.
Start **Apache** and **MySQL** from the XAMPP Control Panel.

Import the database:
1. Open http://localhost/phpmyadmin
2. Create a new database named `library`
3. Click **Import** and select `SQL file/library.sql`

### 2. Install Ollama (free AI for chatbot)
1. Download from https://ollama.com
2. Install and run it (it starts automatically in background)
3. Open a terminal and pull the AI model (one-time, ~2 GB):
```
ollama pull llama3.2
```

### 3. Install Python packages
Open a terminal inside `library/library/` and run:
```
pip install pymysql
pip install -r requirements.txt
pip install spacy --prefer-binary
```
Then download the spaCy language model for better chatbot NLP:
```
python -m spacy download en_core_web_sm
pip install tensorflow / pip install tensorflow-cpu
```

### 4. Check database password
If your MySQL has a password, update it in these three files:

`chatbot/.env`
```
DB_PASS=your_password_here
```

`image_search/image_search_api.py`
```python
"password": "your_password_here",
```

`includes/config.php`
```php
define('DB_PASS','your_password_here');
```

---

## How to Run (Every Time)

**Step 1** - Open XAMPP Control Panel, start Apache and MySQL.

**Step 2** - Open a terminal inside `library/library/` and run:
```
python start_ai.py
```
This starts BOTH AI services together. You will see:
```
[CHATBOT   ]  Running at http://localhost:5000
[IMG-SEARCH]  Running at http://localhost:5001
[IMG-SEARCH]  Books indexed: 12
```

**Step 3** - Open the website in your browser:
```
http://localhost/library/library/
```

Log in and you will see:
- **Text search bar** - type a book name or author
- **Camera button** next to the search bar - upload a book cover photo to search
- **Floating chat bubble** at the bottom right - click to chat with LibraBot

Press **Ctrl+C** in the terminal to stop both AI services.

---

## AI Features

### 1. AI Chatbot (port 5000)
- Powered by Ollama running Llama 3.2 locally - 100% free, no API key needed
- Uses spaCy NLP and fuzzy matching - understands typos and casual language
- Reads live data from your database (books, availability, authors, categories)
- Knows which books the logged-in student is currently borrowing
- Frontend: floating purple chat bubble (`assets/js/librarybot.js`)

### 2. Book Cover Image Search (port 5001)
- Powered by MobileNetV2 CNN (Google pre-trained model, runs locally)
- Startup: reads all books from DB and computes a visual fingerprint for each cover
- User uploads a photo: system compares it to all covers and returns the best match
- Works with slightly blurry or different-angle photos (75% similarity threshold)
- Results show: book name, author, category, ISBN, availability, and confidence %

---

## Files Changed vs Original

| File | Status | What Changed |
|------|--------|--------------|
| `includes/header.php` | Updated | Added camera button + image search modal |
| `dashboard.php` | Updated | Added `data-student-id` to body tag (chatbot personalisation) |
| `image_search/image_search_api.py` | New | CNN image search API |
| `start_ai.py` | New | Single launcher for both AI services |
| `requirements.txt` | New | Merged requirements for both AI features |

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Chatbot says cannot connect to Ollama | Run `ollama serve` in a terminal, or reinstall Ollama from https://ollama.com |
| Chatbot says model not found | Run `ollama pull llama3.2` |
| Database error on any page | Check XAMPP MySQL is started and password matches in all config files |
| Camera button gives error | `start_ai.py` is not running - open a terminal and run it |
| No books indexed on startup | Check that `admin/bookimg/` contains the .jpg files |
| Port already in use | Restart your computer or kill the process using port 5000 or 5001 |
| ImportError tensorflow | Run `pip install tensorflow` in your terminal |

---

## Health Check URLs

After running `start_ai.py`, visit these to check everything is working:

- Chatbot: http://localhost:5000/health
- Image Search: http://localhost:5001/health
