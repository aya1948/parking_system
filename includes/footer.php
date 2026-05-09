</div><!-- /.container-fluid or main content end -->
  </div><!-- /.col main -->
</div><!-- /.row -->

<footer class="text-white text-center py-3" style="background-color: #480959;">
  <small> Rakna Parking Management System &copy; <?= date('Y') ?></small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss flash alerts after 4 seconds
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        let bsAlert = bootstrap.Alert.getOrCreateInstance(a);
        bsAlert.close();
    });
}, 4000);
</script>
</body>
</html>