<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="pages/src/css/global_styling.css">
    <link rel="stylesheet" href="pages/src/css/landingpage.css" />
    <link rel="stylesheet" href="pages/src/css/global_styling2.css">

    <!-- JS -->
    <script src="pages/src/js/landingNavbar.js"></script>

    <title>Student Service Support</title>
</head>

<body>
    <!-- ================= MAIN CONTAINER ================= -->
    <div class="main_container">


        <section class="landingpage">

            <!-- ================= NAVBAR ================= -->
            <div class="navbar">
                <?php include "includes/navbar.php"; ?>

                <!-- Hero -->
                <div class="hero">
                    <div class="hero_subcontainer">

                        <div class="hero_text_container">
                            <div class="hero-text">
                                <span class="title">Student Support Services</span>
                                <span class="subheading">
                                    We keep things simple, approachable, and are committed to
                                    helping you start and stay on track with confidence.
                                </span>
                            </div>

                            <div>
                                <span class="hero_buttons">
                                    <!-- Add reusable button classes, non-breaking -->
                                    <button class="getstrtd btn btn--primary" onclick="window.location.href='./forms.html'">
                                        Get Started
                                    </button>
                                    <button class="watchbttn btn btn--ghost" aria-label="Watch Demo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-play-icon lucide-play">
                                            <path d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z" />
                                        </svg>
                                        Watch Demo
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Waves -->
                <div class="wave_container">
                    <!-- Green Wave -->
                    <div class="green">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-green-base)" d="M0,50 C150,150 350,-50 500,50 L500,150 L0,150 Z" />
                        </svg>
                    </div>

                    <!-- Yellow Wave -->
                    <div class="yellow">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-yellow-base)" fill-opacity="0.85"
                                d="M0,80 C120,160 380,0 500,80 L500,150 L0,150 Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= SERVICES SECTION ================= -->
        <section id="services" class="services" aria-label="Services">
            <!-- HERO AREA -->
            <div class="header_services">
                <div class="banner_section">
                    <div class="shapes_container">
                        <div class="circle_container">
                            <div class="circle">
                                <!-- Info Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="white"
                                    stroke="green" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-info">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 16v-4" />
                                    <path d="M12 8h.01" />
                                </svg>
                            </div>
                        </div>
                        <div class="line_container">
                            <div class="line"></div>
                        </div>
                    </div>
                    <h3 class="banner_title">WHAT SERVICES WE OFFER</h3>
                </div>
            </div>

            <!-- BODY CARDS -->
            <div class="body_services">
                <div class="cards_container">
                    <!-- Card 1 -->
                    <div class="card">
                        <div class="imagesholder">
                            <img src="<?php echo $ADMISSION_BANNER_URL; ?>" alt="<?php echo $ADMISSION_BANNER; ?>" />
                        </div>
                        <div class="text_holder">
                            <div class="placeholder_desc">
                                <p>Request to replace lost or damaged ID</p>
                            </div>
                            <div class="lineforcards"></div>
                            <div class="service_card_title">
                                <p>Admission</p>
                                <button class="small-btn" aria-label="next">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="card">
                        <div class="imagesholder">
                            <img src="<?php echo $ID_REPLACEMENT_BANNER_URL; ?>" alt="<?php echo $ID_REPLACEMENT_BANNER; ?>" />
                        </div>
                        <div class="text_holder">
                            <div class="placeholder_desc">
                                <p>Request to replace lost or damaged ID</p>
                            </div>
                            <div class="lineforcards"></div>
                            <div class="service_card_title">
                                <p>ID Replacement</p>
                                <button class="small-btn" aria-label="next">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="card">
                        <div class="imagesholder">
                            <img src="<?php echo $GOOD_MORAL_REQUEST_BANNER_URL; ?>" alt="<?php echo $GOOD_MORAL_REQUEST_BANNER; ?>" />
                        </div>
                        <div class="text_holder">
                            <div class="placeholder_desc">
                                <p>Request to replace lost or damaged ID</p>
                            </div>
                            <div class="lineforcards"></div>
                            <div class="service_card_title">
                                <p>Good Moral Requisition</p>
                                <button class="small-btn" aria-label="next">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wave -->
            <div class="wave">
                <div class="wave_container">
                    <!-- Green Wave -->
                    <div class="green">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-green-base)" d="M0,50 C150,150 350,-50 500,50 L500,150 L0,150 Z" />
                        </svg>
                    </div>

                    <!-- Yellow Wave -->
                    <div class="yellow">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-yellow-base)" fill-opacity="0.85"
                                d="M0,80 C120,160 380,0 500,80 L500,150 L0,150 Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= About Section ================= -->
        <section id="about" class="about_section" aria-labelledby="about-heading">
            <!-- Banner -->


            <div class="banner_section">
                <div class="shapes_container">
                    <div class="circle_container">
                        <div class="circle">
                            <!-- Info Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="white"
                                stroke="green" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-info">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 16v-4" />
                                <path d="M12 8h.01" />
                            </svg>
                        </div>
                    </div>
                    <div class="line_container">
                        <div class="line"></div>
                    </div>
                </div>
                <h3 class="banner_title">About Student services</h3>
            </div>


            <!-- Body -->
            <div class="about_body">
                <!-- Left Side (Text Items) -->
                <div class="about_text">
                    <div class="about_item">
                        <div class="about_svg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-newspaper">
                                <path d="M15 18h-5" />
                                <path d="M18 14h-8" />
                                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0v-9a2 2 0 0 1 2-2h2" />
                                <rect width="8" height="4" x="10" y="6" rx="1" />
                            </svg>
                        </div>
                        <div class="about_textcontent">
                            <span class="about_title">TRANSPARENCY</span>
                            <p>
                                Cras et lorem gravida, pharetra felis vitae, condimentum
                                turpis.
                            </p>
                        </div>
                    </div>

                    <div class="about_item">
                        <div class="about_svg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-clock-8">
                                <path d="M12 6v6l-4 2" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </div>
                        <div class="about_textcontent">
                            <span class="about_title">FASTER APPLICATION</span>
                            <p>
                                In in felis et risus suscipit tincidunt in eu ipsum. Ut nec
                                purus sed magna volutpat rhoncus.
                            </p>
                        </div>
                    </div>

                    <div class="about_item">
                        <div class="about_svg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-lightbulb">
                                <path
                                    d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5" />
                                <path d="M9 18h6" />
                                <path d="M10 22h4" />
                            </svg>
                        </div>
                        <div class="about_textcontent">
                            <span class="about_title">SMART ASSISTANCE</span>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                Aliquam dictum consequat mollis.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="about_right">
                    <span><strong>Donec aliquam</strong> faucibus nunc, ac porta magna
                        consequat pellentesque. Etiam a faucibus metus. Nullam mattis
                        mauris commodo mauris vestibulum, vel ultricies augue condimentum.
                        Donec vel neque est.</span>

                    <span><strong>Praesent interdum</strong> tortor quis sodales
                        ullamcorper. Phasellus lobortis sodales augue quis sollicitudin.
                        Etiam gravida lectus et nisi tincidunt bibendum. Fusce ac sodales
                        libero.</span>
                </div>
            </div>

            <!-- Wave -->
            <div class="about_wave">
                <div class="wave_container_about">
                    <!-- Green Wave -->
                    <div class="green">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-green-base)" d="M0,50 C150,150 350,-50 500,50 L500,150 L0,150 Z" />
                        </svg>
                    </div>

                    <!-- Yellow Wave -->
                    <div class="yellow">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-yellow-base)" fill-opacity="0.85"
                                d="M0,80 C120,160 380,0 500,80 L500,150 L0,150 Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= Contact Section ================= -->

        <div class="getintouch_container">

            <div class="twoboxes">

                <div class="left_area_contact">

                    <div class="address_container">
                        <div class="addressbox">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-map-pin-icon lucide-map-pin">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </span>

                            <p>Address</p>
                        </div>

                        <p class="subdescription">12-B Alcalde Jose, Pasig, 1600 Metro Manila</p>
                    </div>

                    <div class="landline_container">
                        <div class="landlinebox">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-phone-call-icon lucide-phone-call">
                                    <path d="M13 2a9 9 0 0 1 9 9" />
                                    <path d="M13 6a5 5 0 0 1 5 5" />
                                    <path
                                        d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384" />
                                </svg>
                            </span>

                            <p>Landline</p>
                        </div>
                        <p class="subdescription">2-8643-1014</p>
                    </div>

                    <div class="email_add_container">
                        <div class="emailbox">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-mail-icon lucide-mail">
                                    <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                </svg>
                            </span>

                            <p>Email</p>
                        </div>
                        <p class="subdescription">inquiry@plpasig.edu.ph</p>
                    </div>
                </div>

                <div class="right_area_contact">

                    <div class="header_container">
                        <h2>GET IN TOUCH</h2>
                        <p>Have questions, concerns, or requests? We’re here to help.</p>
                    </div>

                    <form action="">

                        <div class="email">
                            <label for="Email">Email Address</label>
                            <input type="text" aria-label="email">
                        </div>

                        <div class="subject">
                            <label for="Subject">Subject</label>
                            <input type="text" aria-label="subject">
                        </div>

                        <div class="message">
                            <label for="Message">Message</label>
                            <textarea name="" id="" placeholder="Write your message.."></textarea>
                        </div>
                    </form>

                    <div class="button_area">
                        <button class="button_send">Send Message</button>

                    </div>

                </div>
            </div>
        </div>
        </section>

        <!-- ================= Footer ================= -->

        <?php include "includes/footer.php"; ?>



        <!-- ================= END OF THE MAIN CONTAINER ================= -->
    </div>
    <script>
        let CONTACT_US_BANNER_URL = "<?php echo $CONTACT_US_BANNER_URL; ?>";
        const contactSection = document.querySelector('.left_area_contact');
        contactSection.style.backgroundImage = `url(${CONTACT_US_BANNER_URL})`;
        contactSection.style.backgroundSize = "cover";
        contactSection.style.backgroundPosition = "center";
        contactSection.style.backgroundRepeat = "no-repeat";
    </script>
</body>

</html>