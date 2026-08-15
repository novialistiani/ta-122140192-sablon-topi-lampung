/**
 * Admin Dashboard - JavaScript
 * Handles sidebar toggle, interactions, and responsive behavior
 */

document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarLinks = document.querySelectorAll('.sidebar__link');

    // Initialize
    initSidebar();
    initResponsive();

    /**
     * Initialize sidebar functionality
     */
    function initSidebar() {
        // Sidebar toggle button
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        // Sidebar links
        sidebarLinks.forEach((link) => {
            link.addEventListener('click', function (e) {
                // Update active state
                sidebarLinks.forEach((l) => l.classList.remove('active'));
                this.classList.add('active');

                // Close sidebar on mobile after click
                if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                    closeSidebar();
                }
            });
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function (e) {
            if (
                window.innerWidth <= 768 &&
                sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                !sidebarToggle.contains(e.target)
            ) {
                closeSidebar();
            }
        });
    }

    /**
     * Toggle sidebar visibility
     */
    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarToggle.setAttribute(
            'aria-expanded',
            sidebar.classList.contains('open')
        );
    }

    /**
     * Close sidebar
     */
    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarToggle.setAttribute('aria-expanded', 'false');
    }

    /**
     * Initialize responsive behavior
     */
    function initResponsive() {
        // Close sidebar on resize if moving to desktop view
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('open');
                sidebar.classList.remove('collapsed');
                sidebarToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /**
     * Set active menu item based on current route
     */
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        sidebarLinks.forEach((link) => {
            const href = link.getAttribute('href');
            if (href === currentPath || href === window.location.href) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    // Set active menu item on page load
    setActiveMenuItem();

    /**
     * Optional: Add smooth scroll behavior
     */
    document.querySelectorAll('.content-body').forEach((container) => {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.style.animation =
                                'fadeIn 0.5s ease forwards';
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.1 }
            );

            document
                .querySelectorAll('.card, .stat-card')
                .forEach((card) => observer.observe(card));
        }
    });

    /**
     * Optional: Add theme toggle functionality (if needed in future)
     */
    window.toggleDarkMode = function () {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem(
            'darkMode',
            document.body.classList.contains('dark-mode')
        );
    };

    /**
     * Restore dark mode preference
     */
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
});

/**
 * Add animation styles dynamically
 */
if (!document.querySelector('style[data-admin-dashboard]')) {
    const style = document.createElement('style');
    style.setAttribute('data-admin-dashboard', 'true');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card,
        .stat-card {
            animation: fadeIn 0.5s ease forwards;
        }
    `;
    document.head.appendChild(style);
}