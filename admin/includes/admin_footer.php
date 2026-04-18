    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->

<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
<script>
    const SITE_URL = '<?= SITE_URL ?>';
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
</script>
</body>
</html>
