    </div><!-- /.container-fluid or main content end -->
  </div><!-- /.col main -->
</div><!-- /.row -->

<footer class="bg-dark text-white text-center py-3 mt-4">
  <small>🅿️ CitySlot Parking Management System &copy; <?= date('Y') ?> | CS251 Software Engineering 1</small>
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
