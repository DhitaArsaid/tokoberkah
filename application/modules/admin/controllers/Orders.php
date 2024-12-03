<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->library('Fonnte');
        verify_session('admin');

        $this->load->model(array(
            'order_model' => 'order'
        ));
    }

    public function index()
    {
        $search = $this->input->get('search_query');

        if ($search) {
            $params['title'] = 'Cari "' . $search . '"';
        } else {
            $params['title'] = 'Kelola Order';
        }

        $config['base_url'] = site_url('admin/orders/index');
        $config['total_rows'] = $this->order->count_all_orders();
        $config['per_page'] = 10;
        $config['uri_segment'] = 4;
        $choice = $config['total_rows'] / $config['per_page'];
        $config['num_links'] = floor($choice);

        $config['first_link']       = '«';
        $config['last_link']        = '»';
        $config['next_link']        = '›';
        $config['prev_link']        = '‹';
        $config['full_tag_open']    = '<div class="pagging text-center"><nav><ul class="pagination justify-content-center">';
        $config['full_tag_close']   = '</ul></nav></div>';
        $config['num_tag_open']     = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close']    = '</span></li>';
        $config['cur_tag_open']     = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']    = '<span class="sr-only">(current)</span></span></li>';
        $config['next_tag_open']    = '<li class="page-item"><span class="page-link">';
        $config['next_tagl_close']  = '<span aria-hidden="true">&raquo;</span></span></li>';
        $config['prev_tag_open']    = '<li class="page-item"><span class="page-link">';
        $config['prev_tagl_close']  = '</span>Next</li>';
        $config['first_tag_open']   = '<li class="page-item"><span class="page-link">';
        $config['first_tagl_close'] = '</span></li>';
        $config['last_tag_open']    = '<li class="page-item"><span class="page-link">';
        $config['last_tagl_close']  = '</span></li>';

        $this->load->library('pagination', $config);
        $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;

        $orders['orders'] = $this->order->get_all_orders($config['per_page'], $page, $search);
        $orders['pagination'] = $this->pagination->create_links();

        $this->load->view('header', $params);
        $this->load->view('orders/orders', $orders);
        $this->load->view('footer');
    }

    public function view($id = 0)
    {
        if ($this->order->is_order_exist($id)) {
            $data = $this->order->order_data($id);
            $items = $this->order->order_items($id);
            $banks = json_decode(get_settings('payment_banks'));
            $banks = (array) $banks;

            $params['title'] = 'Order #' . $data->order_number;

            $order['data'] = $data;
            $order['items'] = $items;
            $order['delivery_data'] = json_decode($data->delivery_data);
            $order['banks'] = $banks;
            $order['order_flash'] = $this->session->flashdata('order_flash');
            $order['payment_flash'] = $this->session->flashdata('payment_flash');

            $this->load->view('header', $params);
            $this->load->view('orders/view', $order);
            $this->load->view('footer');
        } else {
            show_404();
        }
    }

    public function status()
    {
        $status = $this->input->post('status');
        $order_id = $this->input->post('order');

        // Mendapatkan detail pesanan untuk mengidentifikasi metode pembayaran dan detail produk
        $order = $this->db->where('id', $order_id)->get('orders')->row();
        $payment_method = $order->payment_method;

        $this->order->set_status($status, $order_id);

        // Pengurangan stok produk hanya jika status pesanan berubah menjadi "selesai" (status 4)
        if ($status == "4") {
            // Mengambil detail produk yang dipesan
            $order_items = $this->db->where('order_id', $order_id)->get('order_items')->result();

            foreach ($order_items as $item) {
                $product_id = $item->product_id;
                $order_qty = $item->order_qty;

                // Mengurangi stok produk berdasarkan jumlah yang dipesan
                $this->db->set('stock', 'stock - ' . $order_qty, FALSE)
                    ->where('id', $product_id)
                    ->update('products');
            }
        }

        // Logika untuk mengurangi pendapatan jika status diubah menjadi dibatalkan
        if (($payment_method == 1 && $status == "5") || ($payment_method == 2 && $status == "4")) {
            $this->db->set('total_price', 'total_price - ' . $order->total_price, FALSE)
                ->where('id', $order_id)
                ->update('orders');
        }

        $this->session->set_flashdata('order_flash', 'Status berhasil diperbarui');

        redirect('admin/orders/view/' . $order_id);
    }




    public function pdf($id)
    {
        if ($this->order->is_order_exist($id)) {
            $this->load->library('pdf');
            $data = $this->order->order_data($id);

            $items = $this->order->order_items($id);
            $banks = json_decode(get_settings('payment_banks'));
            $banks = (array) $banks;

            $params['data'] = $data;
            $params['items'] = $items;
            $params['delivery_data'] = json_decode($data->delivery_data);
            $params['banks'] = $banks;

            // Path to your watermark image
            $watermarkImagePath = FCPATH . 'assets/watermark/paid.png'; // Adjust the path and filename

            // Check if the file exists and log if not
            if (!file_exists($watermarkImagePath)) {
                log_message('error', 'Watermark image does not exist: ' . $watermarkImagePath);
            }

            // Load HTML content from view file
            $html = $this->load->view('orders/pdf', $params, true);

            // Define watermark size
            $watermarkWidth = 500; // Adjust the width as needed
            $watermarkHeight = 500; // Adjust the height as needed

            // Generate PDF with watermark
            $this->pdf->createPDF($html, 'order_' . $data->order_number, false, 'A3', 'portrait', $watermarkImagePath, $watermarkWidth, $watermarkHeight);
        } else {
            show_404();
        }
    }


    public function send_message($id)
    {
        $order = $this->Order_model->get_order_with_customer($id);

        if (empty($order)) {
            echo "Order not found";
            return;
        }

        $phone = $order['customer_phone'];
        $message = "Halo " . $order['customer_name'] . ",\nPesanan Anda dengan nomor " . $order['order_number'] . " sedang diproses.";

        $response = $this->fonnte->send_message($phone, $message);
        echo $response;
    }


    // public function send_pending_messages()
    // {
    //     $orders = $this->Order_model->get_pending_orders_with_phone();

    //     if (empty($orders)) {
    //         echo "No pending orders found";
    //         return;
    //     }

    //     foreach ($orders as $order) {
    //         $phone = $order['customer_phone'];
    //         $message = "Hello " . $order['user_id'] . ", your order #" . $order['order_number'] . " is pending payment. Please complete your payment.";
    //         $response = $this->fonnte->send_message($phone, $message);
    //         // Optional: handle the response (e.g., log it or display it)
    //         echo $response;
    //     }
    // }

    public function send_pending_messages()
    {
        $orders = $this->Order_model->get_pending_orders_with_phone();

        if (empty($orders)) {
            echo "No pending orders found";
            return;
        }

        foreach ($orders as $order) {
            $phone = $order['customer_phone'];
            $name = $order['customer_name'];  // Menggunakan customer_name
            $message = "Hello " . $name . ", your order #" . $order['order_number'] . " is pending payment. Please complete your payment.";

            // Format nomor telepon
            $formatted_phone = $this->format_phone_number($phone);
            if ($formatted_phone === false) {
                echo "Invalid phone number: $phone\n";
                continue;
            }

            // Debugging: Cetak nomor telepon dan pesan
            echo "Sending to: $formatted_phone\n";
            echo "Message: $message\n";

            // Kirim pesan menggunakan metode wa yang sudah berfungsi
            $response = $this->wa($formatted_phone, $message);

            // Optional: handle the response (e.g., log it or display it)
            echo "Response: $response\n";
        }
    }

    private function format_phone_number($phone)
    {
        // Pastikan nomor telepon dimulai dengan '0' atau '62'
        if (substr($phone, 0, 1) == '0') {
            return '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) == '62') {
            return $phone;
        } else {
            return false;
        }
    }


    // Fungsi validasi nomor telepon
    private function is_valid_phone_number($phone)
    {
        // Contoh validasi sederhana
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phone);
    }

    // Metode wa yang sudah berfungsi
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
                'Authorization:YtaKJ#J8XDz+RMb4ubVP' //change TOKEN to your actual token
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        curl_close($curl);

        if (isset($error_msg)) {
            return $error_msg;
        }
        return $response;
    }
}
