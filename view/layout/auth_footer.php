    <script src="public/js/navbar.js?v=<?= time() ?>"></script>
    <?php if (!empty($authCss)): ?>
    <script src="public/js/auth.js?v=<?= time() ?>"></script>
    <?php endif; ?>
    <script src="public/js/logout-confirm.js?v=<?= time() ?>"></script>
    <?php if (!empty($js)): ?>
    <script src="public/js/<?= htmlspecialchars(
        $js,
    ) ?>.js?v=<?= time() ?>"></script>
    <?php endif; ?>
    <script src="public/js/chatbot.js?v=<?= time() ?>"></script>
  </body>
</html>
