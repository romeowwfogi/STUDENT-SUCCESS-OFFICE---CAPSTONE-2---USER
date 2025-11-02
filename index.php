<?php
$route = $_GET['route'] ?? '';

$route = trim($route, '/');

$routes = [
    '' => 'pages/index.php',
    'index' => 'pages/index.php',
    'forms' => 'pages/downloadable-forms.php',
    'contact-us' => 'pages/contact-us.php',
    'about' => 'pages/about.php',
    'login' => 'pages/signin.php',
    'login/email-otp' => 'pages/signin_otp.php',
    'login/verify-login' => 'pages/signin_verify_otp.php',
    'register' => 'pages/signup.php',
    'forgot-password' => 'pages/forgot_pass.php',
    'change-password' => 'pages/change_pass.php',
    'verify-account' => 'pages/verify_account.php',
    'activate-account' => 'pages/activate_account.php',
    'admission/home' => 'pages/admission_home.php',
    'admission/my-application' => 'pages/my_application.php',
    'admission/application' => 'pages/admission_application.php',
    'admission/submit-application' => 'pages/submit_application.php',
    'admission/received-application' => 'pages/received_application.php',
    'api/register' => 'api/register.php',
    'api/login' => 'api/login.php',
    'api/login-send-otp' => 'api/send_otp.php',
    'api/login-verify-otp' => 'api/verify_login_otp.php',
    'api/resend-login-verification' => 'api/resend_token.php',
    'api/activate-account' => 'api/activate_account.php',
    'api/reset-password' => 'api/forgot_pass.php',
    'api/change-password' => 'api/change_password.php',
    'api/resend-verify-link' => 'api/send_verify_link.php',
    'api/set-profile' => 'api/set_profile.php',
    'api/fetch-programs' => 'api/get_programs_by_strand.php',
    'api/fetch-all-programs' => 'api/get_all_programs.php',
    'api/submit-application' => 'api/submit_application_admission.php',
];

if (array_key_exists($route, $routes)) {
    include $routes[$route];
} else {
    header("location: error");
}
