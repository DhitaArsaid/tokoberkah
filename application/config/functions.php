<?php

function ambilkecamatan() {
    // URL API Rajaongkir untuk kecamatan
    $url = "https://api.rajaongkir.com/starter/subdistrict";

    // Header dengan API Key
    $header = array(
        "key: b35d8deb998b40d48de4fa710501b030"
    );

    // Membuat request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    curl_close($ch);

    // Mengubah data dari format JSON ke array PHP
    $result = json_decode($result, true);

    // Mengambil data kecamatan
    $kecamatan = $result["rajaongkir"]["results"];

    return $kecamatan;
}



// fungsi untuk mengambil data kota dari API Rajaongkir
function ambilkota() {
    // URL API Rajaongkir
    $url = "https://api.rajaongkir.com/starter/city";

    // header dengan API Key
    $header = array(
        "key: b35d8deb998b40d48de4fa710501b030"
    );

    // membuat request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    curl_close($ch);

    // mengubah data dari format JSON ke array PHP
    $result = json_decode($result, true);

    // mengambil data kota
    $kota = $result["rajaongkir"]["results"];

    return $kota;
}



// Fungsi untuk mengambil data provinsi dari API Rajaongkir
function ambilprovinsi() {
    // URL API Rajaongkir
    $url = "https://api.rajaongkir.com/starter/province";

    // Header dengan API Key
    $header = array(
        "key: b35d8deb998b40d48de4fa710501b030"
    );

    // Membuat request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    curl_close($ch);

    // Mengubah data dari format JSON ke array PHP
    $result = json_decode($result, true);

    // Mengambil data provinsi
    $provinsi = $result["rajaongkir"]["results"];

    return $provinsi;
}



function ongkir($id_kota_asal, $id_kota_tujuan, $berat, $kurir) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "origin=$id_kota_asal&destination=$id_kota_tujuan&weight=$berat&courier=$kurir",
      CURLOPT_HTTPHEADER => array(
        "content-type: application/x-www-form-urlencoded",
        "key: b35d8deb998b40d48de4fa710501b030"
      ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return $err;
    } else {
      return json_decode($response, true);
    }
}



?>