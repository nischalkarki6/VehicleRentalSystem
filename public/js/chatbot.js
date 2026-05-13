(() => {
  /* ── Constants ── */
  const ENDPOINT = 'chatbot.php';
  const STORAGE_KEY = 'driveease_chat_history';

  /* ── State ── */
  let history = [];
  let isOpen = false;
  let isTyping = false;
  let hasGreeted = false;

  /* ── DOM Refs ── */
  const toggle  = document.getElementById('chatbot-toggle');
  const panel   = document.getElementById('chatbot-panel');
  const closeBtn = document.getElementById('cb-close');
  const messages = document.getElementById('cb-messages');
  const input   = document.getElementById('chatbot-input');
  const sendBtn = document.getElementById('chatbot-send');

  if (!toggle || !panel) return; // guard if not rendered

  /* ── Toggle panel open/close ── */
  function openPanel() {
    isOpen = true;
    panel.classList.add('open');
    toggle.classList.add('open');
    input.focus();
    scrollBottom();

    if (!hasGreeted) {
      hasGreeted = true;
      // remove the notification dot permanently once opened
      const dot = toggle.querySelector('.cb-dot');
      if (dot) dot.style.display = 'none';
    }
  }

  function closePanel() {
    isOpen = false;
    panel.classList.remove('open');
    toggle.classList.remove('open');
  }

  toggle.addEventListener('click', () => isOpen ? closePanel() : openPanel());
  if (closeBtn) closeBtn.addEventListener('click', closePanel);

  /* ── Close on outside click ── */
  document.addEventListener('click', (e) => {
    if (isOpen && !panel.contains(e.target) && !toggle.contains(e.target)) {
      closePanel();
    }
  });

  /* ── Send on Enter (Shift+Enter = newline) ── */
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  sendBtn.addEventListener('click', sendMessage);

  /* ── Auto-grow textarea ── */
  input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 110) + 'px';
  });

  /* ── Quick suggestion chips ── */
  document.querySelectorAll('.cb-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      input.value = chip.textContent.trim();
      input.dispatchEvent(new Event('input'));
      sendMessage();
    });
  });

  /* ── Send message ── */
  async function sendMessage() {
    const text = input.value.trim();
    if (!text || isTyping) return;

    // Remove welcome card on first message
    const welcome = document.getElementById('cb-welcome');
    if (welcome) welcome.remove();

    appendMessage('user', text);
    history.push({ role: 'user', text });
    input.value = '';
    input.style.height = 'auto';
    input.focus();

    showTyping();
    sendBtn.disabled = true;

    try {
      const res = await fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, history: history.slice(-12) })
      });

      const data = await res.json();

      if (data.error) {
        removeTyping();
        appendError(data.error);
        return;
      }

      removeTyping();
      const reply = data.reply || 'Sorry, I couldn\'t get a response. Please try again.';
      appendMessage('bot', reply);
      history.push({ role: 'model', text: reply });

    } catch (err) {
      removeTyping();
      appendError('Network error. Please check your connection.');
    } finally {
      sendBtn.disabled = false;
    }
  }

  /* ── Append message bubble ── */
  function appendMessage(role, text) {
    const wrap = document.createElement('div');
    wrap.className = `cb-msg ${role}`;

    if (role === 'bot') {
      wrap.innerHTML = `
        <div class="cb-avatar-sm">
          <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2zM9 14a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
        </div>
        <div class="cb-bubble">${escapeHtml(text)}</div>`;
    } else {
      wrap.innerHTML = `<div class="cb-bubble">${escapeHtml(text)}</div>`;
    }

    messages.appendChild(wrap);
    scrollBottom();
  }

  /* ── Typing indicator ── */
  function showTyping() {
    isTyping = true;
    const el = document.createElement('div');
    el.className = 'cb-typing';
    el.id = 'cb-typing';
    el.innerHTML = `
      <div class="cb-avatar-sm">
        <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2zM9 14a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
      </div>
      <div class="cb-typing-dots">
        <span></span><span></span><span></span>
      </div>`;
    messages.appendChild(el);
    scrollBottom();
  }

  function removeTyping() {
    isTyping = false;
    const el = document.getElementById('cb-typing');
    if (el) el.remove();
  }

  /* ── Error bubble ── */
  function appendError(msg) {
    const el = document.createElement('div');
    el.className = 'cb-msg bot';
    el.innerHTML = `<div class="cb-error">⚠ ${escapeHtml(msg)}</div>`;
    messages.appendChild(el);
    scrollBottom();
  }

  /* ── Helpers ── */
  function scrollBottom() {
    requestAnimationFrame(() => {
      messages.scrollTop = messages.scrollHeight;
    });
  }

  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/\n/g, '<br>');
  }
})();
