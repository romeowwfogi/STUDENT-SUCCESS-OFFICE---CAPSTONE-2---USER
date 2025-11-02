<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- ================= GOOGLE FONTS ================= -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap"
        rel="stylesheet" />

    <!-- ================= CUSTOM CSS ================= -->
    <link rel="stylesheet" href="pages/src/css/about.css">
    <link rel="stylesheet" href="pages/src/css/global_styling.css">
    <link rel="stylesheet" href="pages/src/css/global_styling2.css">
    <script src="pages/src/js/landingNavbar.js"></script>

    <!-- ================= JAVASCRIPT ================= -->


    <title>Student Service Support</title>
</head>

<body>
    <!-- ================= MAIN CONTAINER ================= -->
    <div class="main_container">

        <section class="landingpage">

            <!-- *************** NAVBAR *************** -->
            <?php include "includes/navbar.php"; ?>

            <!-- *************** END NAVBAR *************** -->


            <!-- *************** BANNER *************** -->
            <div class="banner_container">
                <div class="text_container">

                    <!-- Breadcrumb -->
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
                        <span class="current_page">About</span>
                    </div>

                    <!-- Banner Title -->
                    <div class="title_container">
                        <h1 class="bigtitles">ABOUT STUDENT SUPPORT</h1>
                        <p>
                            This system makes it faster and more convenient to access the
                            services you need.
                        </p>
                    </div>
                </div>
            </div>
            <!-- *************** END BANNER *************** -->


            <!-- *************** ABOUT TEXT *************** -->
            <div class="about_text_section">
                <div class="about_text_Section_container">

                    <!-- About Info -->
                    <div class="text_section">
                        <p>
                            The Student Success Office (SSO) is a dedicated department that
                            ensures the needs and concerns of students are addressed with
                            care and efficiency.
                        </p>

                        <p>
                            We provide essential support in areas such as Admission, ID
                            Replacement, and Lost & Found, making sure that students can
                            focus more on their studies and campus life.
                        </p>

                        <p>
                            This platform was created to streamline student services, reduce
                            waiting time, and make processes more accessible. With just a
                            few clicks, students can conveniently submit requests, track
                            progress, and receive updates anytime, anywhere.
                        </p>
                    </div>

                    <!-- Logos -->
                    <div class="logo_section">
                        <img src="<?php echo $SSO_LOGO_URL; ?>" alt="<?php echo $SSO_LOGO; ?>" />
                        <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" />
                        <img src="<?php echo $SYSTEM_LOGO_URL; ?>" alt="<?php echo $SYSTEM_LOGO; ?>" />
                    </div>
                </div>
            </div>
            <!-- *************** END ABOUT TEXT *************** -->


            <!-- *************** OFFICERS SECTION *************** -->
            <div class="officers_container">

                <!-- Officers Banner -->
                <div class="banner_section">
                    <div class="shapes_container">
                        <div class="circle_container">
                            <div class="circle">
                                <!-- Info Icon -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="40"
                                    height="40"
                                    viewBox="0 0 24 24"
                                    fill="white"
                                    stroke="green"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
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
                    <h3 class="banner_title">MEET STUDENT SUCCESS OFFICE</h3>
                </div>

                <!-- Head Officer -->
                <div class="officers_profile_wall">
                    <div class="main_official_container">
                        <div class="head_officer_container">

                            <div class="picture_container">
                                <img src="./src/images/wall_of_officer.png" alt="Head Officer">
                            </div>

                            <div class="information_container">
                                <div class="official_name">
                                    <p>Arlene Y. Daniel</p>
                                </div>

                                <div class="line_under_official_name">
                                    <div class="line_shape"></div>
                                </div>

                                <div class="official_position_container">
                                    <p>Student Success Office Director</p>
                                </div>

                                <div class="email_icon_container">
                                    <div class="email_container_bg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                            <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                            <rect x="2" y="4" width="20" height="16" rx="2" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Other Officers Grid -->
                <div class="officers__grid">
                    <!-- Officer Card -->
                    <div class="officers__card">
                        <div class="officers__card-picture">
                            <img src="./src/images/wall_of_officer.png" alt="Officer Picture" />
                        </div>
                        <div class="officers__card-info">
                            <p class="officers__name">Arlene Y. Daniel</p>
                            <div class="officers__name-line"></div>
                            <p class="officers__position">Student Success Office Director</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="officers__card">
                        <div class="officers__card-picture">
                            <img src="./src/images/wall_of_officer.png" alt="Officer Picture" />
                        </div>
                        <div class="officers__card-info">
                            <p class="officers__name">Arlene Y. Daniel</p>
                            <div class="officers__name-line"></div>
                            <p class="officers__position">Student Success Office Director</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="officers__card">
                        <div class="officers__card-picture">
                            <img src="./src/images/wall_of_officer.png" alt="Officer Picture" />
                        </div>
                        <div class="officers__card-info">
                            <p class="officers__name">Arlene Y. Daniel</p>
                            <div class="officers__name-line"></div>
                            <p class="officers__position">Student Success Office Director</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="officers__card">
                        <div class="officers__card-picture">
                            <img src="./src/images/wall_of_officer.png" alt="Officer Picture" />
                        </div>
                        <div class="officers__card-info">
                            <p class="officers__name">Arlene Y. Daniel</p>
                            <div class="officers__name-line"></div>
                            <p class="officers__position">Student Success Office Director</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Repeat Officer Cards as needed -->
                </div>

            </div>
            <!-- *************** END OFFICERS SECTION *************** -->


            <!-- *************** DEVELOPERS SECTION *************** -->
            <div class="developers_section">
                <div class="banner_section">
                    <div class="shapes_container">
                        <div class="circle_container">
                            <div class="circle">
                                <!-- Info Icon -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="40"
                                    height="40"
                                    viewBox="0 0 24 24"
                                    fill="white"
                                    stroke="green"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
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
                    <h3 class="banner_title">WHO ARE THE DEVELOPERs</h3>
                </div>

                <!-- Developer Cards -->
                <div class="developer_container">

                    <div class="dev_abo card_container">
                        <div class="dev_image">
                            <img src="./src/images/dev_abo.png" alt="Developer picture">
                        </div>
                        <div class="dev_description">
                            <p class="devname">Abo, Gerrald A.</p>
                            <div class="dev-line">
                                <div class="dev_line_shape"></div>
                            </div>
                            <p class="devscription">QA, Backend Developer</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="dev_ador card_container">
                        <div class="dev_image">
                            <img src="./src/images/dev_ador.png" alt="Developer picture">
                        </div>
                        <div class="dev_description">
                            <p class="devname">Ador, Romeo John</p>
                            <div class="dev-line">
                                <div class="dev_line_shape"></div>
                            </div>
                            <p class="devscription">Fullstack Developer</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="dev_datus card_container">
                        <div class="dev_image">
                            <img src="./src/images/dev_datus.png" alt="Developer picture">
                        </div>
                        <div class="dev_description">
                            <p class="devname">Datus, Mark Andrie D.</p>
                            <div class="dev-line">
                                <div class="dev_line_shape"></div>
                            </div>
                            <p class="devscription">UI&UX, Frontend Developer</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="dev_datus card_container">
                        <div class="dev_image">
                            <img src="./src/images/dev gpt.png" alt="Developer picture">
                        </div>
                        <div class="dev_description">
                            <p class="devname">CHAT, GP T.</p>
                            <div class="dev-line">
                                <div class="dev_line_shape"></div>
                            </div>
                            <p class="devscription">FullStack, Cloud Engineerr</p>
                            <div class="email_icon_container">
                                <div class="email_container_bg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail">
                                        <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                    </svg>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- *************** END DEVELOPERS SECTION *************** -->

        </section>


        <!-- *************** FOOTER IMAGE *************** -->
        <?php include "includes/footer.php"; ?>
    </div>

    <script>
        let BANNER_HEADER_URL = "<?php echo $BANNER_HEADER_URL; ?>";
        const BannerHeaderSection = document.querySelector('.banner_container');
        BannerHeaderSection.style.backgroundImage = `url(${BANNER_HEADER_URL})`;
        BannerHeaderSection.style.backgroundSize = "cover";
        BannerHeaderSection.style.backgroundPosition = "center";
        BannerHeaderSection.style.backgroundRepeat = "no-repeat";
    </script>
</body>

</html>