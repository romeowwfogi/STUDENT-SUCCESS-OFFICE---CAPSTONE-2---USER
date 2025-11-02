<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="pages/src/css/forms.css">
    <link rel="stylesheet" href="pages/src/css/global_styling.css">
    <link rel="stylesheet" href="pages/src/css/global_styling2.css">
    <script src="pages/src/js/landingNavbar.js"></script>

    <title>Student Service Support</title>
</head>

<body>
    <!-- ================= MAIN CONTAINER ================= -->
    <div class="main_container">

        <!-- ================= LANDING PAGE ================= -->
        <section class="landingpage">

            <!-- ================= NAVBAR ================= -->
            <div class="navbar">
                <?php include "includes/navbar.php"; ?>
            </div>

            <!-- ================= BANNER ================= -->
            <div class="banner_container">
                <div class="text_container">
                    <div class="breadcrumb">
                        <span class="previous_page">Home</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="white" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                        <span class="current_page">Forms</span>
                    </div>

                    <div class="title_container">
                        <h1 class="bigtitles">DOWNLOADABLE FORMS</h1>
                        <p>
                            Access and download the official forms you need for admission, ID replacement,
                            and other student services.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ================= FORMS MENU ================= -->
            <div class="forms_menu">
                <div class="forms_menu_container">

                    <div class="top_area_forms_menu">
                        <h3>FORMS</h3>
                        <div class="search_input">
                            <input type="text" placeholder="Search Form" />
                        </div>
                    </div>

                    <div class="bottom_forms_selection">
                        <div class="menu_list_forms">
                            <button class="form_button active">LOREM IPSUM FORM</button>
                            <button class="form_button">LOREM IPSUM FORM</button>
                            <button class="form_button">LOREM IPSUM FORM</button>
                            <button class="form_button">LOREM IPSUM FORM</button>
                            <button class="form_button">LOREM IPSUM FORM</button>
                            <button class="form_button">LOREM IPSUM FORM</button>
                        </div>

                        <div class="forms_image_background">
                            <div class="text_area">
                                <p>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                                    sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                                </p>
                                <div class="line_under_forms"></div>
                                <div class="button_area_forms">
                                    <p>LOREM IPSUM FORM</p>
                                    <button class="small-btn" aria-label="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="white"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 17V3" />
                                            <path d="m6 11 6 6 6-6" />
                                            <path d="M19 21H5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="document_image">
                                <img src="./src/images/Example_document_form.png" alt="Application Form" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= FOOTER ================= -->
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
</script>

</html>