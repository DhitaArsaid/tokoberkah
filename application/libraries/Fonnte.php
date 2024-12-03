<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fonnte
{
    private $api_key = 'YtaKJ#J8XDz+RMb4ubVP';

    public function send_message($phone, $message)
    {
        $data = [
            'phone' => $phone,
            'message' => $message,
            'type' => 'text'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.fonnte.com/send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->api_key,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        // Debug: Cetak data yang dikirim dan respon API
        echo "Data Sent: " . json_encode($data) . "\n";
        echo "API Response: " . $response . "\n";

        return $response;
    }
}
