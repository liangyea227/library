/**
 * LibraBot Chat Widget  v2
 * ========================
 * Drop into:  library/library/assets/js/librarybot.js
 *
 * Add to any PHP page before </body>:
 *   <script src="assets/js/librarybot.js"></script>
 *
 * For personalised answers, add to <body> in includes/header.php:
 *   <body data-student-id="<?php echo $_SESSION['stdid'] ?? ''; ?>">
 *
 * FIX: Quick buttons and suggestions now only show generic prompts.
 *      The actual book/category names shown come from the chatbot backend
 *      (which reads the live database), not from this JS file.
 */

(function () {
  "use strict";

  const CHATBOT_URL = "http://localhost:5000/chat";
  const BOT_NAME    = "LibraryBot";
  const STUDENT_ID  = document.body?.dataset?.studentId ?? "";

  let history = [];
  let isOpen  = false;

  // ── Inject CSS ──────────────────────────────────────────────────────────────
  const style = document.createElement("style");
  style.textContent = `
    #lb-fab {
      position:fixed; bottom:28px; right:28px; z-index:9999;
      width:58px; height:58px; border-radius:50%;
      background:linear-gradient(135deg,#4f46e5,#7c3aed);
      border:none; cursor:pointer;
      box-shadow:0 4px 18px rgba(79,70,229,.45);
      display:flex; align-items:center; justify-content:center;
      transition:transform .2s, box-shadow .2s;
    }
    #lb-fab:hover { transform:scale(1.08); box-shadow:0 6px 24px rgba(79,70,229,.6); }
    #lb-fab svg   { width:26px; height:26px; fill:#fff; }
    #lb-badge {
      position:absolute; top:-4px; right:-4px;
      width:18px; height:18px; border-radius:50%;
      background:#ef4444; color:#fff; font-size:11px;
      display:none; align-items:center; justify-content:center;
      font-weight:700; border:2px solid #fff;
    }
    #lb-badge.show { display:flex; }

    #lb-window {
      position:fixed; bottom:100px; right:28px; z-index:9998;
      width:375px; max-height:580px;
      background:#fff; border-radius:18px;
      box-shadow:0 8px 40px rgba(0,0,0,.16);
      display:flex; flex-direction:column;
      font-family:'DM Sans',system-ui,sans-serif;
      overflow:hidden;
      transform:scale(.88) translateY(18px); opacity:0;
      pointer-events:none;
      transition:transform .25s cubic-bezier(.34,1.56,.64,1), opacity .2s;
    }
    #lb-window.open { transform:scale(1) translateY(0); opacity:1; pointer-events:all; }

    #lb-header {
      background:linear-gradient(135deg,#4f46e5,#7c3aed);
      padding:13px 15px; display:flex; align-items:center; gap:10px; flex-shrink:0;
    }
    .lb-avatar {
      width:36px; height:36px; border-radius:50%;
      background:rgba(255,255,255,.22);
      display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .lb-avatar svg { width:18px; height:18px; fill:#fff; }
    .lb-hd-text  { flex:1; }
    .lb-hd-name  { color:#fff; font-weight:600; font-size:14px; }
    .lb-hd-sub   { color:rgba(255,255,255,.72); font-size:11px; margin-top:1px; }
    #lb-close {
      background:none; border:none; cursor:pointer;
      color:rgba(255,255,255,.75); font-size:19px; line-height:1; padding:0 2px;
    }
    #lb-close:hover { color:#fff; }

    #lb-messages {
      flex:1; overflow-y:auto; padding:13px 11px;
      display:flex; flex-direction:column; gap:9px; scroll-behavior:smooth;
    }
    #lb-messages::-webkit-scrollbar { width:4px; }
    #lb-messages::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:4px; }

    .lb-msg { max-width:86%; display:flex; flex-direction:column; gap:3px; }
    .lb-msg.bot  { align-self:flex-start; }
    .lb-msg.user { align-self:flex-end; }

    .lb-bubble {
      padding:9px 13px; border-radius:16px;
      font-size:13.5px; line-height:1.58; word-wrap:break-word;
    }
    .lb-msg.bot  .lb-bubble {
      background:#f1f5f9; color:#1e293b; border-bottom-left-radius:4px;
    }
    .lb-msg.user .lb-bubble {
      background:#4f46e5; color:#fff; border-bottom-right-radius:4px;
    }
    /* Markdown rendering inside bot bubbles */
    .lb-bubble b       { font-weight:600; }
    .lb-bubble ul      { margin:6px 0 4px 16px; padding:0; }
    .lb-bubble ul li   { margin-bottom:3px; }
    .lb-bubble p       { margin:0 0 6px; }
    .lb-bubble p:last-child { margin-bottom:0; }
    .lb-bubble code    {
      background:rgba(0,0,0,.07); border-radius:4px;
      padding:1px 5px; font-size:12px; font-family:monospace;
    }

    .lb-time { font-size:10px; color:#94a3b8; }
    .lb-msg.user .lb-time { text-align:right; }

    .lb-typing { align-self:flex-start; }
    .lb-typing .lb-bubble { display:flex; align-items:center; gap:4px; padding:11px 14px; }
    .lb-dot {
      width:7px; height:7px; border-radius:50%; background:#94a3b8;
      animation:lb-bounce .9s infinite ease-in-out;
    }
    .lb-dot:nth-child(2) { animation-delay:.15s; }
    .lb-dot:nth-child(3) { animation-delay:.30s; }
    @keyframes lb-bounce { 0%,80%,100%{transform:scale(0)} 40%{transform:scale(1)} }

    /* Suggestions strip */
    #lb-suggestions {
      padding:4px 11px 8px; display:flex; flex-wrap:wrap; gap:5px; flex-shrink:0;
    }
    .lb-sug {
      background:#f0fdf4; color:#166534; border:0.5px solid #bbf7d0;
      border-radius:20px; padding:4px 11px; font-size:11.5px;
      cursor:pointer; transition:background .15s;
    }
    .lb-sug:hover { background:#dcfce7; }

    #lb-quick-btns {
      padding:0 11px 9px; display:flex; flex-wrap:wrap; gap:5px; flex-shrink:0;
    }
    .lb-qbtn {
      background:#ede9fe; color:#5b21b6; border:none;
      border-radius:20px; padding:5px 12px; font-size:12px;
      cursor:pointer; transition:background .15s;
    }
    .lb-qbtn:hover { background:#ddd6fe; }

    #lb-footer {
      border-top:1px solid #e2e8f0; padding:9px 11px;
      display:flex; gap:7px; align-items:flex-end; flex-shrink:0; background:#fff;
    }
    #lb-input-wrap { flex:1; position:relative; }
    #lb-input {
      width:100%; border:1.5px solid #e2e8f0; border-radius:12px;
      padding:9px 12px; font-size:13.5px; resize:none;
      outline:none; font-family:inherit; max-height:96px;
      line-height:1.4; transition:border-color .2s;
      background:#fff; color:#1e293b; display:block;
    }
    #lb-input:focus { border-color:#7c3aed; }
    #lb-send {
      width:38px; height:38px; border-radius:10px; flex-shrink:0;
      background:#4f46e5; border:none; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
      transition:background .2s;
    }
    #lb-send:hover    { background:#4338ca; }
    #lb-send:disabled { background:#c7d2fe; cursor:default; }
    #lb-send svg { width:17px; height:17px; fill:#fff; }

    #lb-clear {
      font-size:11px; color:#94a3b8; text-align:center;
      padding:0 11px 8px; cursor:pointer; text-decoration:underline; flex-shrink:0;
    }
    #lb-clear:hover { color:#64748b; }

    @media(max-width:420px){
      #lb-window { width:calc(100vw - 20px); right:10px; bottom:88px; }
      #lb-fab    { bottom:16px; right:16px; }
    }
  `;
  document.head.appendChild(style);

  // ── Build DOM ───────────────────────────────────────────────────────────────
  const fab = document.createElement("button");
  fab.id = "lb-fab";
  fab.setAttribute("aria-label", "Open library chatbot");
  fab.innerHTML = `
    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.03 2 11c0 2.71 1.24 5.14 3.2 6.84L4 22l4.67-1.56A10.6 10.6 0 0 0 12 21c5.52 0 10-4.03 10-9S17.52 2 12 2zm1 13H7v-2h6v2zm3-4H7V9h9v2z"/></svg>
    <span id="lb-badge">1</span>`;
  document.body.appendChild(fab);

  const win = document.createElement("div");
  win.id = "lb-window";
  win.setAttribute("role","dialog");
  win.setAttribute("aria-label","LibraBot chat");
  win.innerHTML = `
    <div id="lb-header">
      <div class="lb-avatar">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.03 2 11c0 2.71 1.24 5.14 3.2 6.84L4 22l4.67-1.56A10.6 10.6 0 0 0 12 21c5.52 0 10-4.03 10-9S17.52 2 12 2zm1 13H7v-2h6v2zm3-4H7V9h9v2z"/></svg>
      </div>
      <div class="lb-hd-text">
        <div class="lb-hd-name">${BOT_NAME}</div>
        <div class="lb-hd-sub">&#9679; Online &mdash; Library Assistant</div>
      </div>
      <button id="lb-close" aria-label="Close chat">&times;</button>
    </div>
    <div id="lb-messages"></div>
    <div id="lb-suggestions"></div>
    <div id="lb-quick-btns">
      <button class="lb-qbtn" data-q="What books do you have?">📚 All Books</button>
      <button class="lb-qbtn" data-q="What's new? Show me the latest arrivals">🆕 New Arrivals</button>
      <button class="lb-qbtn" data-q="What categories do you have?">🗂 Categories</button>
      <button class="lb-qbtn" data-q="Which books are available now?">✅ Available</button>
      <button class="lb-qbtn" data-q="Recommend me a book">💡 Recommend</button>
      <button class="lb-qbtn" data-q="How do I borrow a book?">❓ How to Borrow</button>
      <button class="lb-qbtn" data-q="What books am I currently borrowing?">📋 My Loans</button>
    </div>
    <div id="lb-clear">Clear conversation</div>
    <div id="lb-footer">
      <div id="lb-input-wrap">
        <textarea id="lb-input" placeholder="Ask me anything about books…" rows="1"
          aria-label="Type your message"></textarea>
      </div>
      <button id="lb-send" aria-label="Send message">
        <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
      </button>
    </div>`;
  document.body.appendChild(win);

  // ── Element refs ────────────────────────────────────────────────────────────
  const msgEl   = document.getElementById("lb-messages");
  const inputEl = document.getElementById("lb-input");
  const sendBtn = document.getElementById("lb-send");
  const badge   = document.getElementById("lb-badge");
  const sugEl   = document.getElementById("lb-suggestions");

  // ── Markdown-lite renderer for bot replies ──────────────────────────────────
  function renderMarkdown(text) {
    let s = text
      .replace(/&/g,"&amp;")
      .replace(/</g,"&lt;")
      .replace(/>/g,"&gt;");

    s = s.replace(/\*\*(.+?)\*\*/g,"<b>$1</b>");
    s = s.replace(/__(.+?)__/g,"<b>$1</b>");
    s = s.replace(/`([^`]+)`/g,"<code>$1</code>");

    s = s.replace(/(?:^|\n)([ \t]*[-*]\s+.+?)(?=\n|$)/g, (m,item) => {
      return "\n<li>" + item.replace(/^[ \t]*[-*]\s+/,"") + "</li>";
    });
    s = s.replace(/(<li>.*?<\/li>)(\n<li>.*?<\/li>)*/gs, (m) => "<ul>" + m + "</ul>");
    s = s.replace(/\n{2,}/g,"</p><p>");
    s = s.replace(/\n/g,"<br>");

    return "<p>" + s + "</p>";
  }

  function now() {
    return new Date().toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"});
  }

  function addMsg(role, text, isHtml = false) {
    const wrap = document.createElement("div");
    wrap.className = "lb-msg " + role;
    const content = (role === "bot" && !isHtml) ? renderMarkdown(text) : (isHtml ? text : escRaw(text));
    wrap.innerHTML = `<div class="lb-bubble">${content}</div><div class="lb-time">${now()}</div>`;
    msgEl.appendChild(wrap);
    msgEl.scrollTop = msgEl.scrollHeight;
    return wrap;
  }

  function escRaw(t) {
    return t.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\n/g,"<br>");
  }

  function showTyping() {
    const el = document.createElement("div");
    el.className = "lb-msg bot lb-typing"; el.id = "lb-typing";
    el.innerHTML = `<div class="lb-bubble"><span class="lb-dot"></span><span class="lb-dot"></span><span class="lb-dot"></span></div>`;
    msgEl.appendChild(el); msgEl.scrollTop = msgEl.scrollHeight;
  }
  function hideTyping() { const t = document.getElementById("lb-typing"); if(t) t.remove(); }

  // Suggestions are generic — actual names come from the backend (live DB).
  const SUGGESTIONS = [
    "what's new in the library?",
    "show me new arrivals",
    "what books do you have?",
    "show me all categories",
    "which books are available?",
    "recommend me a book",
    "how do I borrow a book?",
  ];

  function showSuggestions() {
    sugEl.innerHTML = "";
    SUGGESTIONS.slice(0,5).forEach(s => {
      const btn = document.createElement("button");
      btn.className = "lb-sug"; btn.textContent = "🔍 " + s;
      btn.onclick = () => { hideSuggestions(); sendMessage(s); };
      sugEl.appendChild(btn);
    });
  }
  function hideSuggestions() { sugEl.innerHTML = ""; }

  let sugTimer = null;
  inputEl.addEventListener("input", () => {
    clearTimeout(sugTimer);
    inputEl.style.height = "auto";
    inputEl.style.height = Math.min(inputEl.scrollHeight, 96) + "px";
    const v = inputEl.value.trim();
    if (v.length > 0 && v.length <= 3) {
      sugTimer = setTimeout(showSuggestions, 600);
    } else {
      hideSuggestions();
    }
  });

  function openChat() {
    isOpen = true;
    win.classList.add("open");
    badge.classList.remove("show");
    inputEl.focus();
    if (msgEl.children.length === 0) {
      const greeting = STUDENT_ID
        ? `Hi! 👋 I'm **${BOT_NAME}**, your Lim Library assistant. I can see you're logged in — ask me about books, check **new arrivals**, or see what you've borrowed!`
        : `Hi! 👋 I'm **${BOT_NAME}**, your Lim Library assistant. You can ask me things like:\n- "what's new in the library?"\n- "show me all categories"\n- "recommend me a book"\n\nHow can I help?`;
      addMsg("bot", greeting);
    }
  }

  function closeChat() {
    isOpen = false;
    win.classList.remove("open");
    hideSuggestions();
  }

  fab.addEventListener("click", () => isOpen ? closeChat() : openChat());
  document.getElementById("lb-close").addEventListener("click", closeChat);

  document.getElementById("lb-clear").addEventListener("click", () => {
    history = [];
    msgEl.innerHTML = "";
    hideSuggestions();
    addMsg("bot", "Conversation cleared! How can I help you?");
  });

  document.querySelectorAll(".lb-qbtn").forEach(btn => {
    btn.addEventListener("click", () => {
      const q = btn.dataset.q;
      if (q) sendMessage(q);
    });
  });

  inputEl.addEventListener("keydown", e => {
    if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); triggerSend(); }
  });
  sendBtn.addEventListener("click", triggerSend);

  function triggerSend() {
    const text = inputEl.value.trim();
    if (!text) return;
    inputEl.value = ""; inputEl.style.height = "auto";
    hideSuggestions();
    sendMessage(text);
  }

  async function sendMessage(text) {
    if (!isOpen) openChat();
    addMsg("user", text);
    sendBtn.disabled = true;
    showTyping();

    history.push({ role: "user", content: text });

    try {
      const res = await fetch(CHATBOT_URL, {
        method:  "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          message:    text,
          history:    history.slice(-10),
          student_id: STUDENT_ID,
        }),
      });

      const data = await res.json();
      hideTyping();

      if (data.error) {
        addMsg("bot", `⚠️ Sorry, something went wrong: ${data.error}`);
      } else {
        addMsg("bot", data.reply);
        history.push({ role: "assistant", content: data.reply });
      }
    } catch (err) {
      hideTyping();
      addMsg("bot", "⚠️ Could not reach the chatbot server.\nMake sure **chatbot.py** is running:\n`python chatbot.py`");
      console.error("LibraBot:", err);
    } finally {
      sendBtn.disabled = false;
      inputEl.focus();
    }
  }

  setTimeout(() => { if (!isOpen) badge.classList.add("show"); }, 3000);

})();