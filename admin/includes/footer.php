<?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
            </main>
            
            <!-- Footer -->
            <footer class="bg-white p-4 text-center text-gray-600 shadow-md">
                <p>&copy; <?php echo date('Y'); ?> Vbind Marketing Agency. All rights reserved.</p>
            </footer>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle for desktop
            const toggleSidebar = document.getElementById('toggle-sidebar');
            const adminSidebar = document.getElementById('admin-sidebar');
            const adminContent = document.getElementById('admin-content');
            
            if (toggleSidebar && adminSidebar && adminContent) {
                toggleSidebar.addEventListener('click', function() {
                    adminSidebar.classList.toggle('collapsed');
                    adminContent.classList.toggle('expanded');
                });
            }
            
            // Mobile menu button
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            
            if (mobileMenuButton && adminSidebar) {
                mobileMenuButton.addEventListener('click', function() {
                    adminSidebar.classList.toggle('active');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!adminSidebar.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                        adminSidebar.classList.remove('active');
                    }
                }
            });
            
            // Highlight current page in navigation
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.nav-item a');
            
            navItems.forEach(item => {
                const href = item.getAttribute('href');
                if (currentPath.includes(href) && href !== 'dashboard.php') {
                    item.classList.add('bg-gray-700');
                } else if (href === 'dashboard.php' && (currentPath.endsWith('dashboard.php') || currentPath.endsWith('/admin/'))) {
                    item.classList.add('bg-gray-700');
                }
            });
        });
    </script>
<?php endif; ?>
</body>
</html>
