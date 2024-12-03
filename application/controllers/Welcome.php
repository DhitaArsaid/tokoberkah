<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

  /**
   * Index Page for this controller.
   *
   * Maps to the following URL
   * 		http://example.com/index.php/welcome
   *	- or -
   * 		http://example.com/index.php/welcome/index
   *	- or -
   * Since this controller is set as the default controller in
   * config/routes.php, it's displayed at http://example.com/
   *
   * So any other public methods not prefixed with an underscore will
   * map to /index.php/welcome/<method_name>
   * @see https://codeigniter.com/user_guide/general/urls.html
   */
  public function index()
  {
    $this->load->view('welcome_message');
  }

  public function callback()
  {
    $notif = json_decode(file_get_contents('php://input'), true);
    $transaction = $notif['transaction_status'];
    $type = $notif['payment_type'];
    $order_id = $notif['order_id'];
    $arr = explode("-", $order_id);
    $order_id = $arr[0];

    if ($transaction == 'settlement') {
      // TODO set payment status in merchant's database to 'Settlement'

      $get = $this->db->get_where('orders', ['order_number' => $order_id])->row();
      $delivery_data = json_decode($get->delivery_data);

      $this->db->set('order_status', '2');
      $this->db->where('order_number', $order_id);
      $this->db->update('orders');

      $this->wa($delivery_data->customer->phone_number, "Pembayaran Berhasil. Pesanan akan kami proses");

      echo 'berhasil';
      //   echo "Transaction order_id: " . $order_id ." successfully transfered using " . $type;
    } else if ($transaction == 'pending') {
      // TODO set payment status in merchant's database to 'Pending'
      echo "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
    } else if ($transaction == 'deny') {
      // TODO set payment status in merchant's database to 'Denied'
      echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.";
    } else if ($transaction == 'expire') {
      // TODO set payment status in merchant's database to 'expire'
      echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
    } else if ($transaction == 'cancel') {
      // TODO set payment status in merchant's database to 'Denied'
      echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.";
    }
  }


  public function wa($no, $message)
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $no,
        'message' => $message,
        'countryCode' => '62', //optional
      ),
      CURLOPT_HTTPHEADER => array(
        'Authorization: bWnQ3pyyN+sugkV@Qd8b' //change TOKEN to your actual token
      ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
      $error_msg = curl_error($curl);
    }
    curl_close($curl);

    if (isset($error_msg)) {
      echo $error_msg;
    }
    echo $response;
  }
}
