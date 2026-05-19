    <footer>
      <div class="container footer-content">
        <div class="footer-left">
          <div class="footer-logo">DRIVEEASE</div>
          <div class="copyright">(c) 2026 DRIVEEASE NEPAL. ALL RIGHTS RESERVED.</div>
        </div>
        <div class="footer-links">
          <a href="support.php">SUPPORT</a>
          <a href="privacy.php">PRIVACY POLICY</a>
          <a href="terms.php">TERMS</a>
          <a href="fleet.php">FLEET</a>
          <a href="contact.php">CONTACT</a>
        </div>
      </div>
    </footer>

    <!-- Chatbot widget -->
    <!-- Floating toggle button (bottom-right) -->
    <button id="chatbot-toggle" aria-label="Open chat assistant" title="Chat with DriveEase AI">
      <!-- Chat icon -->
      <svg class="cb-icon cb-icon-chat" viewBox="0 0 24 24" fill="none"
           stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
      <!-- Close icon -->
      <svg class="cb-icon cb-icon-close" viewBox="0 0 24 24" fill="none"
           stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
      <!-- Notification dot -->
      <span class="cb-dot"></span>
    </button>

    <!-- Chat panel -->
    <div id="chatbot-panel" role="dialog" aria-label="DriveEase AI Assistant">
      <!-- Header -->
      <div class="cb-header">
        <div class="cb-header-avatar">
          <svg viewBox="0 0 24 24">
            <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2zM9 14a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
          </svg>
        </div>
        <div class="cb-header-info">
          <div class="cb-header-name">DriveEase Assistant</div>
          <div class="cb-header-status">
            <span class="cb-status-dot"></span>
            Online - Powered by Groq AI
          </div>
        </div>
        <button class="cb-header-close" id="cb-close" aria-label="Close chat">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Messages -->
      <div class="cb-messages" id="cb-messages">
        <!-- Welcome card -->
        <div class="cb-welcome" id="cb-welcome">
          <p>Hi there! I'm your <strong>DriveEase AI assistant</strong>.<br>
          Ask me anything about vehicle rentals, pricing, or travel in Nepal!</p>
          <div class="cb-chips">
            <button class="cb-chip">View available vehicles</button>
            <button class="cb-chip">Pricing info</button>
            <button class="cb-chip">How do I book?</button>
            <button class="cb-chip">Contact support</button>
          </div>
        </div>
      </div>

      <!-- Input area -->
      <div class="cb-input-wrap">
        <textarea
          id="chatbot-input"
          placeholder="Type your message..."
          rows="1"
          aria-label="Chat message input"
        ></textarea>
        <button id="chatbot-send" aria-label="Send message">
          <svg viewBox="0 0 24 24">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
          </svg>
        </button>
      </div>
    </div>
    <!-- /Chatbot widget -->

<?php include __DIR__ . "/auth_footer.php"; ?>
