<style>
    /* =========================================================
   🌿 NAVBAR STYLES
========================================================= */
    .navbar_section {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: flex-start;
        padding: var(--space-sm);
        gap: var(--space-sm);
        border-radius: 12px;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -2px, rgba(0, 0, 0, 0.3) 0px 3px 7px -3px;
        position: relative;
    }

    .logo_container {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: var(--space-xs);
        flex-grow: 1;
    }

    .logo_container img {
        max-width: 80px;
        height: auto;
    }

    .title {
        display: flex;
        align-items: center;
        gap: var(--space-xs);
    }

    .hamburger_container {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: var(--space-xs);
    }

    .hambuger_icon {
        cursor: pointer;
    }

    .leftside {
        display: none;
        gap: 30px;
        align-items: center;
        justify-content: center;
    }

    /* ---------- User Circle ---------- */

    .circlecontainer {
        width: clamp(36px, 5vw, 60px);
        height: clamp(36px, 5vw, 60px);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .circle {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: var(--color-darkergreen-base);
        color: white;
        font-size: clamp(0.7rem, 1.5vw, 1rem);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    @media (max-width: 640px) {
        .circlecontainer {
            width: 40px;
            height: 40px;
        }
    }

    @media (min-width: 1024px) {
        .circlecontainer {
            width: 55px;
            height: 55px;
        }
    }

    .text_container {
        display: flex;
        align-items: center;
        gap: var(--space-sm);
        font-size: var(--space-sm);
    }

    .text_container p {
        cursor: pointer;
        transition: color 0.2s;
    }

    .text_container p:hover {
        color: var(--color-darkergreen-base);
    }

    .text_container p.active {
        font-weight: 800;
        color: var(--color-darkergreen-base);
    }

    .namecontainer {
        font-size: var(--font-size-xs);
    }

    .minibreadcumb {
        color: #666;
        font-size: 12px;
    }

    .dropdown_icon {
        display: flex;
        cursor: pointer;
    }

    /* ---------- Dropdown Menu ---------- */

    .dropdown_menu {
        position: absolute;
        top: 136px;
        right: 13px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .dropdown_menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown_menu ul {
        list-style: none;
        padding: var(--space-xs);
        margin: 0;
    }

    .dropdown_menu li {
        padding: var(--space-xs) var(--space-sm);
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 0.2s;
    }

    .dropdown_menu li:hover {
        background-color: rgba(34, 139, 34, 0.1);
    }

    .dropdown_menu li a {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .dropdown_divider {
        height: 1px;
        background-color: #e5e7eb;
        margin: var(--space-xs) 0;
    }

    /* =========================================================
   🌿 NAVBAR RESPONSIVE DESIGN
========================================================= */

    /* Tablet (641px–1075px) */
    @media (min-width: 641px) and (max-width: 1075px) {
        .navbar_section {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .hamburger_container {
            display: none;
        }

        .leftside {
            display: flex;
        }
    }

    /* Small desktops (1076px–1280px) */
    @media (min-width: 1076px) and (max-width: 1280px) {
        .navbar_section {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .hamburger_container {
            display: none;
        }

        .leftside {
            display: flex;
        }
    }

    /* Large desktops (1281px and up) */
    @media (min-width: 1281px) {
        .navbar_section {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .hamburger_container {
            display: none;
        }

        .leftside {
            display: flex;
        }
    }

    /* Mobile dropdown positioning */
    @media (max-width: 640px) {
        .dropdown_menu {
            right: 10px;
            left: 10px;
            min-width: auto;
        }
    }
</style>

<!-- ================= NAVBAR SECTION ================= -->
<div class="navbar_section">
    <div class="logo_container">
        <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" />
        <div class="title">
            <p><b>Admission Portal</b></p>
        </div>
    </div>

    <div class="hamburger_container">
        <div class="hambuger_icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-menu-icon lucide-menu">
                <path d="M4 5h16" />
                <path d="M4 12h16" />
                <path d="M4 19h16" />
            </svg>
        </div>
    </div>

    <div class="leftside">
        <div class="text_container">
            <?php
            // Get current page name
            function getCurrentUrl()
            {
                // Check if the protocol is HTTPS or HTTP
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";

                // Append the standard separator
                $url = $protocol . "://";

                // Append the host name
                $url .= $_SERVER['HTTP_HOST'];

                // Append the request URI, including the path and query string
                $url .= $_SERVER['REQUEST_URI'];

                return $url;
            }

            $page_mappings = [
                'admission_home' => 'home',
                'my_application' => 'my-application'
            ];

            $current_page = getCurrentUrl();

            ?>
            <p class="<?php echo str_contains($current_page, 'home') ? 'active' : ''; ?>" onclick="window.location.href = 'home';">HOME</p>
            <p class="<?php echo str_contains($current_page, 'my-application') ? 'active' : ''; ?>" onclick="window.location.href = 'my-application';">MY APPLICATION</p>
        </div>

        <!-- <div class="circlecontainer">
            <div class="circle" id="profile-ic">-</div>
        </div> -->

        <div class="namecontainer">
            <div class="name" id="fullname">Loading...</div>
            <div class="minibreadcumb">Applicant</div>
        </div>

        <div class="dropdown_icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-chevron-down">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </div>
    </div>
</div>

<!-- Dropdown Menu -->
<div class="dropdown_menu">
    <ul>
        <li><a href="#" id="open-settings-modal">Settings</a></li>
        <li><a href="#logout" id="logout-link">Logout</a></li>
    </ul>
</div>

<script>
    // ======================= NAVBAR FUNCTIONALITY =======================

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const hamburger = document.querySelector('.hambuger_icon');
        const dropdownIcon = document.querySelector('.dropdown_icon');
        const dropdownMenu = document.querySelector('.dropdown_menu');

        // Hamburger menu functionality (mobile)
        if (hamburger && dropdownMenu) {
            hamburger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdownMenu.classList.toggle('active');
            });
        }

        // Dropdown icon functionality (desktop)
        if (dropdownIcon && dropdownMenu) {
            dropdownIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdownMenu.classList.toggle('active');
            });
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (dropdownMenu &&
                !hamburger?.contains(e.target) &&
                !dropdownIcon?.contains(e.target) &&
                !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('active');
            }
        });

        // Close dropdown when clicking a menu item
        const menuItems = document.querySelectorAll('.dropdown_menu li');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('active');
                }
            });
        });

        // Smooth scrolling for navigation links
        const navLinks = document.querySelectorAll('a[href^="#"]');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navigation click handlers (active state is now handled server-side)
        const navItems = document.querySelectorAll('.text_container p[onclick]');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                // Active state will be set automatically when page loads
                // No need to manage it client-side anymore
            });
        });

        // No JS-driven hover or page-load animations to keep UI stable

        // Settings modal trigger
        const settingsLink = document.getElementById('open-settings-modal');
        if (settingsLink) {
            settingsLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof openSettingsModal === 'function') {
                    openSettingsModal();
                }
            });
        }

        // Logout with confirmation modal
        const logoutLink = document.getElementById('logout-link');
        if (logoutLink) {
            logoutLink.addEventListener('click', async function(e) {
                e.preventDefault();
                const icon = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='28' height='28' fill='none' stroke='currentColor' stroke-width='1.5'><path d='M10 17l5-5-5-5'/><path d='M15 12H3'/><path d='M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4'/></svg>`;
                if (typeof messageModalV1Show === 'function') {
                    messageModalV1Show({
                        icon,
                        iconBg: '#eef2ff',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: true,
                        title: 'Sign out?',
                        message: 'Are you sure you want to logout?',
                        cancelText: 'Cancel',
                        actionText: 'Logout',
                        onConfirm: async () => {
                            try {
                                const { ok, data } = await (window.postJSON ? window.postJSON('/api/logout.php', {}) : Promise.resolve({ ok: false, data: { success: false } }));
                                if (ok && data && data.success) {
                                    window.location.href = '../login';
                                } else {
                                    // Fallback: still redirect to login
                                    window.location.href = '../login';
                                }
                            } catch (_) {
                                window.location.href = '../login';
                            }
                        }
                    });
                } else {
                    // If modal not available, proceed directly
                    try {
                        await fetch('/api/logout.php', { method: 'POST' });
                    } catch (_) {}
                    window.location.href = 'login';
                }
            });
        }
    });
</script>