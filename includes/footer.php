<?php
// includes/footer.php - Global footer containing javascript behavior for theme toggling

?>
            </div> <!-- End of fade-in animations wrapper -->
        </main>
    </div> <!-- End of main layout wrapper -->

    <!-- Theme management script -->
    <script>
        (function() {
            const themeBtn = document.getElementById('theme-toggle-btn');
            const sunIcon = document.getElementById('theme-sun-icon');
            const moonIcon = document.getElementById('theme-moon-icon');

            function applyThemeIcons(theme) {
                if (theme === 'dark') {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                } else {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                }
            }

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            applyThemeIcons(currentTheme);

            themeBtn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme');
                const target = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', target);
                localStorage.setItem('theme', target);
                applyThemeIcons(target);
            });

            // Handle Global Loader
            window.addEventListener('load', () => {
                const loader = document.getElementById('global-loader');
                if (loader) {
                    loader.classList.add('hidden');
                }
            });

            // Optional: Show loader on form submit or page unload
            window.addEventListener('beforeunload', () => {
                const loader = document.getElementById('global-loader');
                if (loader) {
                    loader.classList.remove('hidden');
                }
            });
        })();
    </script>
</body>
</html>
