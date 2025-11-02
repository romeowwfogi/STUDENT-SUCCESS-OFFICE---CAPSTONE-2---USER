<?php
function getGreetingMessage()
{
    date_default_timezone_set('Asia/Manila');

    $hour = (int) date('H');

    if ($hour >= 5 && $hour < 12) {
        return "Good Morning";
    } elseif ($hour >= 12 && $hour < 18) {
        return "Good Afternoon";
    } else {
        return "Good Evening";
    }
}
