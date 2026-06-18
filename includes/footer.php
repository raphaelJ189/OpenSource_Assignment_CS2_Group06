<?php
// includes/footer.php - Global footer containing javascript behaviors

?>
            </div> <!-- End of content-wrapper -->
        </main>
    </div> <!-- End of layout-wrapper -->

    <!-- Toast Notifications Container -->
    <div id="toast-container"></div>

    <!-- Unified JavaScript Framework -->
    <script>
        (function() {
            // Namespace initialization
            window.SRMS = window.SRMS || {};

            // 1. Toast Notification System
            window.SRMS.notify = function(message, type = 'info', title = '') {
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;

                let iconClass = 'fa-circle-info';
                if (type === 'success') iconClass = 'fa-circle-check';
                else if (type === 'danger') iconClass = 'fa-circle-xmark';
                else if (type === 'warning') iconClass = 'fa-triangle-exclamation';

                if (!title) {
                    title = type.charAt(0).toUpperCase() + type.slice(1);
                }

                toast.innerHTML = `
                    <div class="toast-icon"><i class="fa-solid ${iconClass}"></i></div>
                    <div class="toast-body">
                        <div class="toast-title">${title}</div>
                        <div class="toast-msg">${message}</div>
                    </div>
                    <button type="button" class="toast-close" aria-label="Close toast">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;

                container.appendChild(toast);

                const dismiss = () => {
                    if (toast.classList.contains('toast-hiding')) return;
                    toast.classList.add('toast-hiding');
                    toast.addEventListener('animationend', () => {
                        toast.remove();
                    });
                };

                const closeBtn = toast.querySelector('.toast-close');
                closeBtn.addEventListener('click', dismiss);

                // Auto-dismiss after 5 seconds
                setTimeout(dismiss, 5000);
            };

            // 2. Custom Confirmation Modal System
            window.SRMS.confirm = function({ title, desc, onConfirm }) {
                // Remove existing modal if any
                const existing = document.getElementById('global-modal-overlay');
                if (existing) existing.remove();

                const overlay = document.createElement('div');
                overlay.id = 'global-modal-overlay';
                overlay.className = 'modal-overlay';
                overlay.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-header">
                            <div class="modal-icon danger">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <h3 class="modal-title">${title}</h3>
                            <p class="modal-desc">${desc}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="modal-cancel-btn">Cancel</button>
                            <button type="button" class="btn btn-primary" id="modal-confirm-btn" style="background: var(--color-danger); border-color: var(--color-danger);">Delete</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);

                const cancelBtn = overlay.querySelector('#modal-cancel-btn');
                const confirmBtn = overlay.querySelector('#modal-confirm-btn');

                const close = () => {
                    overlay.remove();
                };

                cancelBtn.addEventListener('click', close);
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) close();
                });

                confirmBtn.addEventListener('click', () => {
                    onConfirm();
                    close();
                });
            };

            // 3. Client-side validation helper
            window.SRMS.validate = {
                required: (input) => {
                    return input.value.trim() !== '';
                },
                minLength: (input, len) => {
                    return input.value.trim().length >= len;
                },
                pattern: (input, regex) => {
                    return regex.test(input.value.trim());
                }
            };

            // 4. Page Loader control
            window.addEventListener('load', () => {
                const loader = document.getElementById('global-loader');
                if (loader) {
                    loader.classList.add('hidden');
                }
            });

            // 5. Theme Toggle Logic
            const themeBtn = document.getElementById('theme-toggle-btn');
            const sunIcon = document.getElementById('theme-sun-icon');
            const moonIcon = document.getElementById('theme-moon-icon');

            function applyThemeIcons(theme) {
                if (theme === 'dark') {
                    if (sunIcon) sunIcon.style.display = 'inline-block';
                    if (moonIcon) moonIcon.style.display = 'none';
                } else {
                    if (sunIcon) sunIcon.style.display = 'none';
                    if (moonIcon) moonIcon.style.display = 'inline-block';
                }
            }

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            applyThemeIcons(currentTheme);

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const current = document.documentElement.getAttribute('data-theme');
                    const target = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', target);
                    localStorage.setItem('theme', target);
                    applyThemeIcons(target);
                });
            }

            // 6. Responsive Sidebar controls
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('is-open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('active');
                    }
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    if (sidebar) sidebar.classList.remove('is-open');
                    sidebarOverlay.classList.remove('active');
                });
            }

            // 7. Auto-dismiss alerts after 5 seconds
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });

            // 8. Keyboard shortcut (Ctrl+K) to focus search
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const searchInput = document.getElementById('search-input');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
            });

            // 9. Intercept student delete actions to trigger the modal confirmation
            document.addEventListener('click', (e) => {
                const deleteLink = e.target.closest('.js-confirm-delete');
                if (deleteLink) {
                    e.preventDefault();
                    const studentId = deleteLink.dataset.studentId;
                    const studentName = deleteLink.dataset.studentName || 'this student';
                    const deleteUrl = deleteLink.getAttribute('href');

                    window.SRMS.confirm({
                        title: 'Confirm Deletion',
                        desc: `Are you sure you want to permanently delete the student record for <strong>${studentName}</strong>? This action cannot be undone.`,
                        onConfirm: () => {
                            // Convert the action into a secure POST delete
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = deleteUrl;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            });
        })();
    </script>

    <script>
        (function() {
            if (!window.SRMS) {
                return;
            }

            const basePath = window.SRMS.basePath || './';
            const warnAt = 15 * 60 * 1000;
            const timeoutAt = 20 * 60 * 1000;
            const storageKey = 'session-timeout-last-reset';

            let warnTimeoutId = null;
            let redirectTimeoutId = null;
            let dialog = null;

            const clearTimers = () => {
                if (warnTimeoutId) {
                    clearTimeout(warnTimeoutId);
                    warnTimeoutId = null;
                }

                if (redirectTimeoutId) {
                    clearTimeout(redirectTimeoutId);
                    redirectTimeoutId = null;
                }
            };

            const closeDialog = () => {
                if (!dialog) {
                    return;
                }

                if (typeof dialog.close === 'function') {
                    dialog.close();
                }

                if (dialog.parentNode) {
                    dialog.parentNode.removeChild(dialog);
                }

                dialog = null;
            };

            const updateLastActivity = () => {
                try {
                    localStorage.setItem(storageKey, Date.now().toString());
                } catch (error) {
                    // localStorage can be unavailable in restricted browser contexts.
                }
            };

            const keepAlive = () => {
                const timestamp = Math.floor(Date.now() / 1000);
                return fetch(`${basePath}keep-alive.php?time=${timestamp}`, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store'
                }).catch(() => {});
            };

            const redirectToLogout = () => {
                window.location.href = `${basePath}logout.php`;
            };

            const restartTimers = () => {
                clearTimers();

                warnTimeoutId = setTimeout(showDialog, warnAt);
                redirectTimeoutId = setTimeout(() => {
                    closeDialog();
                    redirectToLogout();
                }, timeoutAt);
            };

            const handleContinue = () => {
                keepAlive();
                closeDialog();
                updateLastActivity();
                restartTimers();
            };

            const handleLogout = () => {
                closeDialog();
                redirectToLogout();
            };

            function createDialog() {
                const timeoutDialog = document.createElement('dialog');
                timeoutDialog.className = 'session-timeout-dialog';
                timeoutDialog.setAttribute('role', 'dialog');
                timeoutDialog.setAttribute('aria-modal', 'true');
                timeoutDialog.setAttribute('aria-labelledby', 'session-timeout-title');
                timeoutDialog.innerHTML = `
                    <div class="session-timeout-card">
                        <div class="session-timeout-icon" aria-hidden="true">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="session-timeout-copy">
                            <h2 id="session-timeout-title">Session expiring soon</h2>
                            <p>Your session will end soon because of inactivity. Choose Continue Session to stay signed in or Log Out to finish the session now.</p>
                        </div>
                        <div class="session-timeout-actions">
                            <button type="button" class="btn btn-secondary" data-action="logout">Log Out</button>
                            <button type="button" class="btn btn-danger" data-action="continue">Continue Session</button>
                        </div>
                    </div>
                `;

                const continueBtn = timeoutDialog.querySelector('[data-action="continue"]');
                const logoutBtn = timeoutDialog.querySelector('[data-action="logout"]');

                continueBtn.addEventListener('click', handleContinue);
                logoutBtn.addEventListener('click', handleLogout);

                timeoutDialog.addEventListener('cancel', (event) => {
                    event.preventDefault();
                    handleContinue();
                });

                return timeoutDialog;
            }

            function showDialog() {
                if (!dialog) {
                    dialog = createDialog();
                    document.body.appendChild(dialog);
                }

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', 'open');
                }
            }

            const handleStorage = (event) => {
                if (event.key !== storageKey || !event.newValue) {
                    return;
                }

                const lastReset = parseInt(event.newValue, 10);
                const elapsed = Date.now() - lastReset;

                if (elapsed >= timeoutAt) {
                    closeDialog();
                    redirectToLogout();
                } else if (elapsed >= warnAt) {
                    closeDialog();
                    showDialog();
                } else {
                    closeDialog();
                    restartTimers();
                }
            };

            const readLastActivity = () => {
                try {
                    return localStorage.getItem(storageKey);
                } catch (error) {
                    return null;
                }
            };

            window.addEventListener('storage', handleStorage);

            const storedLastActivity = readLastActivity();
            if (storedLastActivity) {
                const elapsed = Date.now() - parseInt(storedLastActivity, 10);

                if (elapsed >= timeoutAt) {
                    redirectToLogout();
                } else if (elapsed >= warnAt) {
                    showDialog();
                    redirectTimeoutId = setTimeout(() => {
                        closeDialog();
                        redirectToLogout();
                    }, Math.max(0, timeoutAt - elapsed));
                } else {
                    warnTimeoutId = setTimeout(showDialog, Math.max(0, warnAt - elapsed));
                    redirectTimeoutId = setTimeout(() => {
                        closeDialog();
                        redirectToLogout();
                    }, Math.max(0, timeoutAt - elapsed));
                }
            } else {
                restartTimers();
            }

            updateLastActivity();
        })();
    </script>
</body>
</html>
