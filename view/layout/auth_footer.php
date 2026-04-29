    <?php if (!empty($js)): ?>
    <script src="public/js/<?= htmlspecialchars(
        $js,
    ) ?>.js?v=<?= time() ?>"></script>
    <?php endif; ?>
  </body>
</html>