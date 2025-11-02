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
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap"
        rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="pages/src/css/contact-us.css">
    <link rel="stylesheet" href="pages/src/css/global_styling.css">
    <link rel="stylesheet" href="pages/src/css/global_styling2.css">
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

                <!-- ================= BANNER ================= -->
                <div class="banner_container">
                    <div class="text_container">
                        <div class="breadcrumb">
                            <span class="previous_page">Home</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                fill="none"
                                stroke="white"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                            <span class="current_page">Contact Us</span>
                        </div>

                        <div class="title_container">
                            <h1 class="bigtitles">CONTACT SUPPORT</h1>
                            <p>
                                Have questions or need assistance? Our Student Services Office is ready to help.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="getintouch_container">

                    <div class="twoboxes">

                        <div class="left_area_contact">

                            <div class="address_container">
                                <div class="addressbox">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin-icon lucide-map-pin">
                                            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone-call-icon lucide-phone-call">
                                            <path d="M13 2a9 9 0 0 1 9 9" />
                                            <path d="M13 6a5 5 0 0 1 5 5" />
                                            <path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384" />
                                        </svg>
                                    </span>

                                    <p>Landline</p>
                                </div>
                                <p class="subdescription">2-8643-1014</p>
                            </div>

                            <div class="email_add_container">
                                <div class="emailbox">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
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
                                <button class="send_button">Send Message</button>

                            </div>

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
        
        let BANNER_HEADER_URL = "<?php echo $BANNER_HEADER_URL; ?>";
        const BannerHeaderSection = document.querySelector('.banner_container');
        BannerHeaderSection.style.backgroundImage = `url(${BANNER_HEADER_URL})`;
        BannerHeaderSection.style.backgroundSize = "cover";
        BannerHeaderSection.style.backgroundPosition = "center";
        BannerHeaderSection.style.backgroundRepeat = "no-repeat";
    </script>
</body>

</html>