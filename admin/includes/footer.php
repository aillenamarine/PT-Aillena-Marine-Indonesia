    </main>
  </div>

  <!-- Admin Interactive Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggle = document.getElementById('mobileNavToggle');
      const sidebar = document.getElementById('adminSidebar');

      if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
          sidebar.classList.toggle('active');
        });

        // Close sidebar if clicking outside on mobile
        document.addEventListener('click', (e) => {
          if (window.innerWidth <= 991 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('active');
          }
        });
      }
    });
  </script>
</body>
</html>
