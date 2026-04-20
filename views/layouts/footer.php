</main> <!-- End Main Content -->

<footer class="bg-white shadow-inner mt-auto">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
        © <?= date('Y') ?> Web Kasir Toko Buku Berjalan - Dibuat dengan PHP Native & Tailwind CSS
    </div>
</footer>

<!-- Include JavaScript Kustom -->
<script src="assets/js/script.js"></script>
<!-- Script untuk toggle menu mobile (contoh sederhana) -->
<script>
     document.addEventListener('DOMContentLoaded', function() {
         const mobileMenuButton = document.querySelector('button[aria-controls="mobile-menu"]');
         const mobileMenu = document.getElementById('mobile-menu');
         const menuIconOpen = mobileMenuButton.querySelector('svg.block');
         const menuIconClose = mobileMenuButton.querySelector('svg.hidden');

         if (mobileMenuButton && mobileMenu && menuIconOpen && menuIconClose) {
            mobileMenuButton.addEventListener('click', function() {
                const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
                mobileMenu.style.display = isExpanded ? 'none' : 'block';
                menuIconOpen.style.display = isExpanded ? 'block' : 'none';
                menuIconClose.style.display = isExpanded ? 'none' : 'block';
             });
         }
     });
</script>

</body>
</html>
<?php
// Hapus input lama jika ada setelah footer dimuat (agar tidak persisten antar request)
if (isset($_SESSION['old_input'])) {
unset($_SESSION['old_input']);
}
?>
