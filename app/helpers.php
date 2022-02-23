<?php

if (!function_exists("formatPhoneNumber")) {

    function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = str_pad($phone, 10, "0", STR_PAD_LEFT);

        return '90' . substr($phone, -10);
    }
}
