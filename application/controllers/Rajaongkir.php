<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rajaongkir extends CI_Controller {

	public function getCity($province) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.rajaongkir.com/starter/city?&province=$province",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "key: b35d8deb998b40d48de4fa710501b030"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	}

	public function ongkir($city,$expedisi) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "origin=501&destination=$city&weight=1700&courier=$expedisi",
		  CURLOPT_HTTPHEADER => array(
		    "content-type: application/x-www-form-urlencoded",
		    "key: b35d8deb998b40d48de4fa710501b030"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	}

  	// http://example.com/rajaongkir/province
	public function province(){
		$provinces = $this->rajaongkir->province(); // output json
		print_r($provinces);
	}
  
  	// http://example.com/rajaongkir/city
  	public function city(){
		$cities = $this->rajaongkir->city(); // output json
		print_r($cities);
	}
  
  	// http://example.com/rajaongkir/subdistrict
  	public function subdistrict(){
		$subdistrict = $this->rajaongkir->subdistrict(151); // output json
		print_r($subdistrict);
	}
  
  	// http://example.com/rajaongkir/cost
  	public function cost(){
		$cost = $this->rajaongkir->cost(501, 114, 1000, "jne"); // output json
		print_r($cost);
	}
  
}
