<style>
    /* =========================================================
   🌿 FOOTER SECTION
========================================================= */

    .site_footer {
        background: #0f3e10;
        color: var(--color-white);
        padding: var(--space-xl) var(--space-lg) var(--space-lg);
    }

    .footer_inner {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: var(--space-xxl);
    }

    .footer_intro {
        display: flex;
        flex-direction: column;
        gap: var(--space-md);
    }

    .footer_intro p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.9rem;
    }

    .footer_logos {
        display: flex;
        align-items: center;
        gap: var(--space-md);
        flex-wrap: wrap;
    }

    .footer_logos img {
        width: 70px;
        height: 70px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .footer_logos img:hover {
        transform: scale(1.1);
    }

    .footer_cols {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-xl);
        font-size: 0.9rem;
    }

    .footer_col h4 {
        color: var(--color-yellow-base);
        margin-bottom: var(--space-sm);
    }

    .footer_col ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer_col li {
        margin: 6px 0;
    }

    .footer_col a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer_col a:hover {
        color: var(--color-yellow-base);
    }

    .footer_divider {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        margin: var(--space-lg) 0;
    }

    .footer_bottom {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-lg);
        flex-wrap: wrap;
    }

    .footer_copy p {
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.9rem;
    }

    .footer_socials {
        display: flex;
        gap: var(--space-sm);
    }

    .social_btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.1);
        color: var(--color-white);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social_btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .footer_image {
        grid-area: footer_image;
        display: flex;
        background: var(--color-darkergreen-base);
        justify-content: flex-end;
    }

    .footer_image img {
        margin-top: var(--space-xxl);
        width: 100%;
        max-width: 100%;
    }

    /* ======================= FOOTER RESPONSIVE DESIGN ======================= */

    /* Mobile */
    @media (max-width: 640px) {
        .footer_inner {
            grid-template-columns: 1fr;
        }

        .footer_bottom {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--space-md);
        }

        .footer_cols {
            grid-template-columns: 1fr;
            gap: var(--space-lg);
        }
    }

    /* Tablet */
    @media (min-width: 641px) and (max-width: 1024px) {
        .footer_cols {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
<!-- *************** FOOTER IMAGE *************** -->
<div class="footer_image">
    <img src="<?php echo $FOOTER_IMAGE_URL; ?>" alt="<?php echo $FOOTER_IMAGE; ?>" />
</div>

<!-- *************** FOOTER *************** -->
<footer class="site_footer" role="contentinfo">
    <div class="footer_inner">

        <!-- Footer Intro -->
        <div class="footer_intro">
            <p>
                We keep things simple, approachable, and are committed to helping
                you start and stay on track with confidence.
            </p>
            <div class="footer_logos" aria-hidden="true">
                <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" />
                <img src="<?php echo $SYSTEM_LOGO_URL; ?>" alt="<?php echo $SYSTEM_LOGO; ?>" />
            </div>
        </div>

        <!-- Footer Columns -->
        <div class="footer_cols" aria-label="Footer links">
            <div class="footer_col">
                <h4>Navigation Pages</h4>
                <ul>
                    <li><a href="index">Home</a></li>
                    <li><a href="login">Admission</a></li>
                    <li><a href="forms">Forms</a></li>
                    <li><a href="about">About</a></li>
                    <li><a href="contact-us">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer_col">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms and Conditions</a></li>
                </ul>
            </div>
            <div class="footer_col">
                <h4>About PLP</h4>
                <ul>
                    <li><a href="#">Mission and Vision</a></li>
                    <li><a href="#">News and Events</a></li>
                </ul>
            </div>
        </div>
    </div>

    <hr class="footer_divider" />

    <!-- Footer Bottom -->
    <div class="footer_bottom">
        <div class="footer_copy">
            <p>Copyright © 2025 Pamantasan ng Lungsod ng Pasig. All Rights Reserved</p>
            <p>Maintained by Student Success Office</p>
            <p>Version 1.0</p>
            <p>Developed By ABO | ADOR | DATUS</p>
        </div>

        <!-- Social Links -->
        <div class="footer_socials">
            <a href="#" aria-label="Facebook" class="social_btn">f</a>
            <a href="#" aria-label="LinkedIn" class="social_btn">in</a>
            <a href="#" aria-label="Twitter" class="social_btn">tw</a>
        </div>
    </div>
</footer>