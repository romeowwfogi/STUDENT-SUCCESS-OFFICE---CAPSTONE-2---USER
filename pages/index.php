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
    <!-- Geometric Canvas Background (yellow dots) -->
    <script defer src="pages/src/js/geometric _bg.js"></script>
    <!-- Announcements Carousel -->
    <script defer src="pages/src/js/announcements.js"></script>

    <title>Student Service Support</title>
</head>

<body>


    <!-- ================= MAIN CONTAINER ================= -->
    <div class="main_container">

        <canvas id="canvas"></canvas>


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

                

                <!-- Waves 
                <div class="wave_container">
                    Green Wave 
                    <div class="green">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-green-base)" d="M0,50 C150,150 350,-50 500,50 L500,150 L0,150 Z" />
                        </svg>
                    </div>

                    Yellow Wave 
                    <div class="yellow">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path fill="var(--color-yellow-base)" fill-opacity="0.85"
                                d="M0,80 C120,160 380,0 500,80 L500,150 L0,150 Z" />
                        </svg>
                    </div>
                </div>-->
            </div>
        </section>

        <!-- ================= Announcements Section ================= -->
        <section id="announcements" class="announcements_section" aria-label="Announcements">

        
            <div class="announcements_header">
                <h2 class="announcements_title">Announcements</h2>
                <p class="announcements_subtitle">Latest updates and important notices</p>
            </div>

            <div class="announcements_wrapper">

                <div id="announcements-carousel" class="announcements_carousel" tabindex="0" aria-live="polite">
                    <!-- Card 1 -->
                    <article class="announcement_card" aria-label="Entrance Exam Schedule">
                        <div class="announcement_meta">
                            <span class="announcement_date">Nov 10, 2025</span>
                            <span class="announcement_tag">Admissions</span>
                        </div>
                        <h3 class="announcement_title">Entrance Exam Schedule</h3>
                        <p class="announcement_desc">The entrance exam for incoming freshmen is open. Register online and pick your preferred date.</p>
                        <div class="announcement_actions">
                            <a href="#" class="btn btn--ghost">Learn More</a>
                        </div>
                    </article>

                    <!-- Card 2 -->
                    <article class="announcement_card" aria-label="ID Replacement Drive">
                        <div class="announcement_meta">
                            <span class="announcement_date">Nov 7, 2025</span>
                            <span class="announcement_tag">Student Services</span>
                        </div>
                        <h3 class="announcement_title">ID Replacement Drive</h3>
                        <p class="announcement_desc">Lost or damaged ID? Visit the booth at the lobby this week for expedited processing.</p>
                        <div class="announcement_actions">
                            <a href="#services" class="btn btn--ghost">View Service</a>
                        </div>
                    </article>

                    <!-- Card 3 -->
                    <article class="announcement_card" aria-label="Good Moral Request Update">
                        <div class="announcement_meta">
                            <span class="announcement_date">Nov 4, 2025</span>
                            <span class="announcement_tag">Registrar</span>
                        </div>
                        <h3 class="announcement_title">Good Moral Request Update</h3>
                        <p class="announcement_desc">Processing time for Good Moral certificates has been reduced to 2–3 business days.</p>
                        <div class="announcement_actions">
                            <a href="#services" class="btn btn--ghost">See Details</a>
                        </div>
                    </article>

                    <!-- Card 4 -->
                    <article class="announcement_card" aria-label="System Maintenance">
                        <div class="announcement_meta">
                            <span class="announcement_date">Nov 1, 2025</span>
                            <span class="announcement_tag">IT Notice</span>
                        </div>
                        <h3 class="announcement_title">System Maintenance</h3>
                        <p class="announcement_desc">Scheduled maintenance on Saturday 10 PM–12 AM. Some services may be temporarily unavailable.</p>
                        <div class="announcement_actions">
                            <a href="#" class="btn btn--ghost">Read Notice</a>
                        </div>
                    </article>
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

    <script>
          function togglePassword() {
          const passwordInput = document.getElementById('password');
          const eyeIcon = document.getElementById('eye-icon');
          const eyeOffIcon = document.getElementById('eye-off-icon');

          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.style.display = 'none';
            eyeOffIcon.style.display = 'block';
          } else {
            passwordInput.type = 'password';
            eyeIcon.style.display = 'block';
            eyeOffIcon.style.display = 'none';
          }
        }

        // ================== PARTICLE EFFECT ==================
        // Use full viewport for canvas background on landing page
        const rightEl = null;
        const canvas = document.getElementById("canvas");
        const ctx = canvas.getContext("2d");

        const particles = [];
        const fireworkParticles = [];
        const dustParticles = [];
        const ripples = [];
        const techRipples = [];

        const mouse = (() => {
          let state = { x: null, y: null };
          return {
            get x() { return state.x; },
            get y() { return state.y; },
            set({ x, y }) { state = { x, y }; },
            reset() { state = { x: null, y: null }; }
          };
        })();

        let frameCount = 0;
        let autoDrift = true;

        function adjustParticleCount() {
          const particleConfig = {
            heightConditions: [200, 300, 400, 500, 600],
            widthConditions: [450, 600, 900, 1200, 1600],
            particlesForHeight: [20, 30, 40, 50, 60],
            particlesForWidth: [20, 30, 40, 50, 60]
          };

          let numParticles = 60;
          for (let i = 0; i < particleConfig.heightConditions.length; i++) {
            if (canvas.height < particleConfig.heightConditions[i]) {
              numParticles = particleConfig.particlesForHeight[i];
              break;
            }
          }

          for (let i = 0; i < particleConfig.widthConditions.length; i++) {
            if (canvas.width < particleConfig.widthConditions[i]) {
              numParticles = Math.min(numParticles, particleConfig.particlesForWidth[i]);
              break;
            }
          }

          return numParticles;
        }

        class Particle {
          constructor(x, y, isFirework = false) {
            const baseSpeed = isFirework ? Math.random() * 2 + 1 : Math.random() * 0.5 + 0.3;

            Object.assign(this, {
              isFirework,
              x,
              y,
              vx: Math.cos(Math.random() * Math.PI * 2) * baseSpeed,
              vy: Math.sin(Math.random() * Math.PI * 2) * baseSpeed,
              size: isFirework ? Math.random() * 1 + 1 : Math.random() * 1.5 + 0.5,
              hue: Math.random() * 60 + 90, // soft lime green range
              alpha: 1,
              sizeDirection: Math.random() < 0.5 ? -1 : 1,
              trail: []
            });
          }

          update(mouse) {
            const dist = mouse.x !== null ? (mouse.x - this.x) ** 2 + (mouse.y - this.y) ** 2 : 0;
            if (!this.isFirework) {
              const force = dist && dist < 22500 ? (22500 - dist) / 22500 : 0;

              if (mouse.x === null && autoDrift) {
                this.vx += (Math.random() - 0.5) * 0.03;
                this.vy += (Math.random() - 0.5) * 0.03;
              }

              if (dist) {
                const sqrtDist = Math.sqrt(dist);
                this.vx += ((mouse.x - this.x) / sqrtDist) * force * 0.1;
                this.vy += ((mouse.y - this.y) / sqrtDist) * force * 0.1;
              }

              this.vx *= mouse.x !== null ? 0.99 : 0.998;
              this.vy *= mouse.y !== null ? 0.99 : 0.998;
            } else {
              this.alpha -= 0.02;
            }

            this.x += this.vx;
            this.y += this.vy;

            if (this.x <= 0 || this.x >= canvas.width - 1) this.vx *= -0.9;
            if (this.y < 0 || this.y > canvas.height) this.vy *= -0.9;

            this.size += this.sizeDirection * 0.1;
            if (this.size > 3 || this.size < 0.5) this.sizeDirection *= -1;

            this.hue = (this.hue + 0.3) % 360;

            if (frameCount % 2 === 0 && (Math.abs(this.vx) > 0.1 || Math.abs(this.vy) > 0.1)) {
              this.trail.push({ x: this.x, y: this.y, hue: this.hue, alpha: this.alpha });
              if (this.trail.length > 15) this.trail.shift();
            }
          }

          draw(ctx) {
            const glowColor = `hsl(${this.hue}, 90%, 60%)`;
            const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size);
            gradient.addColorStop(0, `hsla(${this.hue}, 100%, 70%, ${Math.max(this.alpha, 0)})`);
            gradient.addColorStop(1, `hsla(${this.hue}, 80%, 40%, 0)`);

            ctx.fillStyle = gradient;
            ctx.shadowBlur = 20;
            ctx.shadowColor = glowColor;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;

            if (this.trail.length > 1) {
              ctx.beginPath();
              ctx.lineWidth = 1.5;
              for (let i = 0; i < this.trail.length - 1; i++) {
                const { x: x1, y: y1, hue: h1, alpha: a1 } = this.trail[i];
                const { x: x2, y: y2 } = this.trail[i + 1];
                ctx.strokeStyle = `hsla(${h1}, 100%, 60%, ${Math.max(a1, 0)})`;
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
              }
              ctx.stroke();
            }
          }

          isDead() {
            return this.isFirework && this.alpha <= 0;
          }
        }

        class DustParticle {
          constructor() {
            Object.assign(this, {
              x: Math.random() * canvas.width,
              y: Math.random() * canvas.height,
              size: Math.random() * 1.2 + 0.3,
              hue: Math.random() * 60 + 40, // yellow-green tint
              vx: (Math.random() - 0.5) * 0.05,
              vy: (Math.random() - 0.5) * 0.05
            });
          }

          update() {
            this.x = (this.x + this.vx + canvas.width) % canvas.width;
            this.y = (this.y + this.vy + canvas.height) % canvas.height;
            this.hue = (this.hue + 0.1) % 360;
          }

          draw(ctx) {
            ctx.fillStyle = `hsla(${this.hue}, 50%, 70%, 0.25)`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
          }
        }

        class Ripple {
          constructor(x, y, hue = 90, maxRadius = 30) {
            Object.assign(this, { x, y, radius: 0, maxRadius, alpha: 0.5, hue });
          }

          update() {
            this.radius += 1.5;
            this.alpha -= 0.01;
            this.hue = (this.hue + 5) % 360;
          }

          draw(ctx) {
            ctx.strokeStyle = `hsla(${this.hue}, 90%, 60%, ${this.alpha})`;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.stroke();
          }

          isDone() {
            return this.alpha <= 0;
          }
        }

        function createParticles() {
          particles.length = 0;
          dustParticles.length = 0;

          const numParticles = adjustParticleCount();
          for (let i = 0; i < numParticles; i++) {
            particles.push(new Particle(Math.random() * canvas.width, Math.random() * canvas.height));
          }
          for (let i = 0; i < 80; i++) {
            dustParticles.push(new DustParticle());
          }
        }

        function resizeCanvas() {
          // Size canvas to full viewport for background effect
          canvas.width = Math.max(0, Math.floor(window.innerWidth));
          canvas.height = Math.max(0, Math.floor(window.innerHeight));
          createParticles();
        }

        function drawBackground() {
          const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
          gradient.addColorStop(0, "#0A1B0A");
          gradient.addColorStop(1, "#133D1C");
          ctx.fillStyle = gradient;
          ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        function connectParticles() {
          const gridSize = 120;
          const grid = new Map();

          particles.forEach((p) => {
            const key = `${Math.floor(p.x / gridSize)},${Math.floor(p.y / gridSize)}`;
            if (!grid.has(key)) grid.set(key, []);
            grid.get(key).push(p);
          });

          ctx.lineWidth = 1.2;
          particles.forEach((p) => {
            const gridX = Math.floor(p.x / gridSize);
            const gridY = Math.floor(p.y / gridSize);

            for (let dx = -1; dx <= 1; dx++) {
              for (let dy = -1; dy <= 1; dy++) {
                const key = `${gridX + dx},${gridY + dy}`;
                if (grid.has(key)) {
                  grid.get(key).forEach((neighbor) => {
                    if (neighbor !== p) {
                      const diffX = neighbor.x - p.x;
                      const diffY = neighbor.y - p.y;
                      const dist = diffX * diffX + diffY * diffY;
                      if (dist < 10000) {
                        ctx.strokeStyle = `rgba(0, 255, 100, ${1 - Math.sqrt(dist) / 100})`;
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(neighbor.x, neighbor.y);
                        ctx.stroke();
                      }
                    }
                  });
                }
              }
            }
          });
        }

        function animate() {
          drawBackground();

          [dustParticles, particles, ripples, techRipples, fireworkParticles].forEach((arr) => {
            for (let i = arr.length - 1; i >= 0; i--) {
              const obj = arr[i];
              obj.update(mouse);
              obj.draw(ctx);
              if (obj.isDone?.() || obj.isDead?.()) arr.splice(i, 1);
            }
          });

          connectParticles();
          frameCount++;
          requestAnimationFrame(animate);
        }

        window.addEventListener("mousemove", (e) => {
          mouse.set({ x: e.clientX, y: e.clientY });
          techRipples.push(new Ripple(mouse.x, mouse.y));
          autoDrift = false;
        });

        document.addEventListener("mouseleave", () => {
          mouse.reset();
          autoDrift = true;
        });

        window.addEventListener("click", (e) => {
          const clickX = e.clientX;
          const clickY = e.clientY;

          ripples.push(new Ripple(clickX, clickY, 100, 60));

          for (let i = 0; i < 10; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = Math.random() * 2 + 1;
            const particle = new Particle(clickX, clickY, true);
            particle.vx = Math.cos(angle) * speed;
            particle.vy = Math.sin(angle) * speed;
            fireworkParticles.push(particle);
          }
        });

        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();
        animate();

      </script>

    
</body>

</html>