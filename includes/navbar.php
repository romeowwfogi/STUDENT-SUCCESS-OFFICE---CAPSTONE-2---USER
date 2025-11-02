<div class="nav">
    <!-- Logo -->
    <div class="logo">
        <div class="plp_logo">
            <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" />
        </div>
    </div>

    <!-- Menu List -->
    <div class="menu_list" role="navigation" aria-label="Primary">
        <a href="index" class="active">HOME</a>
        <a href="login">ADMISSION</a>
        <a href="forms">FORMS</a>
        <a href="contact-us">CONTACT US</a>
        <a href="about">ABOUT</a>
    </div>

    <!-- Right Side -->
    <div class="left_area">
        <span>Register</span>
        <button>
            Login
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-log-in-icon lucide-log-in">
                <path d="m10 17 5-5-5-5" />
                <path d="M15 12H3" />
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            </svg>
        </button>
    </div>

    <!-- Hamburger Menu -->
    <div class="hamburger_area">
        <div class="hamburger_icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-menu-icon lucide-menu">
                <path d="M4 5h16" />
                <path d="M4 12h16" />
                <path d="M4 19h16" />
            </svg>
        </div>

        <div class="hamburger_nav" role="navigation" aria-label="Mobile">
            <span><a href="index">HOME</a></span>
            <span><a href="login">ADMISSION</a></span>
            <span><a href="forms">FORMS</a></span>
            <span><a href="contact-us">CONTACT US</a></span>
            <a href="about">ABOUT</a>
        </div>
    </div>
</div>