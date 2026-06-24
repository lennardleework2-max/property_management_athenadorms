    <!-- CSRF Token for AJAX -->
    <input type="hidden" id="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="public/assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
