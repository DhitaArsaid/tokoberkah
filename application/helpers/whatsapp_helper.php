<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('whatsapp_link')) {
    function whatsapp_link($phone, $text)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Membersihkan nomor telepon
        $text = urlencode($text); // Meng-encode text
        return "https://api.whatsapp.com/send?phone={$phone}&text={$text}";
    }
}
