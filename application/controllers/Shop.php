<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shop extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('cart');
        $this->load->model(array(
            'product_model' => 'product',
            'customer_model' => 'customer'
        ));
    }

    public function product($id = 0, $sku = '')
    {
        if ($id == 0 || empty($sku)) {
            show_error('Akses tidak sah!');
        } else {
            if ($this->product->is_product_exist($id, $sku)) {
                $data = $this->product->product_data($id);

                $product['product'] = $data;
                $product['related_products'] = $this->product->related_products($data->id, $data->category_id);

                get_header($data->name . ' | ' . get_settings('store_tagline'));
                get_template_part('shop/view_single_product', $product);
                get_footer();
            } else {
                show_404();
            }
        }
    }

    public function cart()
    {
        $cart['carts'] = $this->cart->contents();
        $cart['total_cart'] = $this->cart->total();

        $insufficient_stock = false;
        $error_messages = [];

        foreach ($cart['carts'] as &$item) {
            $product_stock = $this->product->get_product_stock($item['id']); // Assume this method returns the stock of the product
            if ($item['qty'] > $product_stock) {
                $insufficient_stock = true;
                $item['insufficient'] = true; // Mark this item as having insufficient stock
                $error_messages[] = "Produk {$item['name']} hanya tersedia sebanyak {$product_stock} unit.";
            }
        }

        if ($insufficient_stock) {
            $this->session->set_flashdata('error_messages', $error_messages);
        }

        $ongkir = ($cart['total_cart'] >= get_settings('min_shop_to_free_shipping_cost')) ? 0 : get_settings('shipping_cost');
        $cart['total_price'] = $cart['total_cart'] + $ongkir;

        get_header('Keranjang Belanja');
        get_template_part('shop/cart', $cart);
        get_footer();
    }
    public function checkout($action = '')
    {
        if (!is_login()) {
            $coupon = $this->input->post('coupon_code');
            $quantity = $this->input->post('quantity');

            $this->session->set_userdata('_temp_coupon', $coupon);
            $this->session->set_userdata('_temp_quantity', $quantity);

            verify_session('customer');
        }

        switch ($action) {
            default:
                $coupon = $this->input->post('coupon_code') ? $this->input->post('coupon_code') : $this->session->userdata('_temp_coupon');
                $quantity = $this->input->post('quantity') ? $this->input->post('quantity') : $this->session->userdata('_temp_quantity');

                if ($this->session->userdata('_temp_quantity') || $this->session->userdata('_temp_coupon')) {
                    $this->session->unset_userdata('_temp_coupon');
                    $this->session->unset_userdata('_temp_quantity');
                }

                $items = [];
                $insufficient_stock = false;
                $error_messages = [];

                foreach ($quantity as $rowid => $qty) {
                    $cart_item = $this->cart->get_item($rowid);
                    $product_stock = $this->product->get_product_stock($cart_item['id']);

                    if ($qty > $product_stock) {
                        $insufficient_stock = true;
                        $error_messages[] = "Produk {$cart_item['name']} hanya tersedia sebanyak {$product_stock} unit.";
                    } else {
                        $items['rowid'] = $rowid;
                        $items['qty'] = $qty;
                    }
                }

                if ($insufficient_stock) {
                    $this->session->set_flashdata('error_messages', $error_messages);
                    redirect('shop/cart');
                    return;
                }

                $this->cart->update($items);

                if (empty($coupon)) {
                    $discount = 0;
                    $disc = 'Tidak menggunakan kupon';
                } else {
                    if ($this->customer->is_coupon_exist($coupon)) {
                        if ($this->customer->is_coupon_active($coupon)) {
                            if ($this->customer->is_coupon_expired($coupon)) {
                                $discount = 0;
                                $disc = 'Kupon kadaluarsa';
                            } else {
                                $coupon_id = $this->customer->get_coupon_id($coupon);
                                $this->session->set_userdata('coupon_id', $coupon_id);

                                $credit = $this->customer->get_coupon_credit($coupon);
                                $discount = $credit;
                                $disc = '<span class="badge badge-success">' . $coupon . '</span> Rp ' . format_rupiah($credit);
                            }
                        } else {
                            $discount = 0;
                            $disc = 'Kupon sudah tidak aktif';
                        }
                    } else {
                        $discount = 0;
                        $disc = 'Kupon tidak terdaftar';
                    }
                }

                $items = [];

                foreach ($this->cart->contents() as $item) {
                    $items[$item['id']]['qty'] = $item['qty'];
                    $items[$item['id']]['price'] = $item['price'];
                }

                $subtotal = $this->cart->total();
                $ongkir = (int) ($subtotal >= get_settings('min_shop_to_free_shipping_cost')) ? 0 : get_settings('shipping_cost');

                $params['customer'] = $this->customer->data();
                $params['subtotal'] = $subtotal;
                $params['ongkir'] = ($ongkir > 0) ? 'Rp' . format_rupiah($ongkir) : 'Gratis';
                $params['total'] = $subtotal + $ongkir - $discount;
                $params['discount'] = $disc;
                $params['provinsi'] = json_decode($this->getProvince())->rajaongkir->results;

                $this->session->set_userdata('order_quantity', $items);
                $this->session->set_userdata('total_price', $params['total']);

                get_header('Checkout');
                get_template_part('shop/checkout', $params);
                get_footer();
                break;

            case 'order':
                $quantity = $this->session->userdata('order_quantity');

                $user_id = get_current_user_id();
                $coupon_id = $this->session->userdata('coupon_id');
                $order_number = $this->_create_order_number($quantity, $user_id, $coupon_id);
                $order_date = date('Y-m-d H:i:s');
                $total_price = $this->session->userdata('total_price');
                $total_items = count($quantity);
                $payment = $this->input->post('payment');

                $name = $this->input->post('name');
                $phone_number = $this->input->post('phone_number');
                $address = $this->input->post('address');
                $note = $this->input->post('note');

                $delivery_data = array(
                    'customer' => array(
                        'name' => $name,
                        'phone_number' => $phone_number,
                        'address' => $address
                    ),
                    'note' => $note
                );

                $delivery_data = json_encode($delivery_data);

                $payment_link = "";
                if ($payment == "1") {
                    $ttl = $total_price + $this->input->post('ongkir');
                    $result = $this->midtrans($order_number, $ttl, $name, $phone_number);
                    $result = json_decode($result);

                    $payment_link = $result->payment_url;
                }

                $order = array(
                    'user_id' => $user_id,
                    'coupon_id' => $coupon_id,
                    'order_number' => $order_number,
                    'order_status' => 1,
                    'order_date' => $order_date,
                    'total_price' => $total_price,
                    'total_items' => $total_items,
                    'payment_method' => $payment,
                    'delivery_data' => $delivery_data,
                    'provinsi' => $this->input->post('provinsi'),
                    'kabupaten' => $this->input->post('kabupaten'),
                    'ongkir' => $this->input->post('ongkir'),
                    'expedisi' => $this->input->post('expedisi'),
                    'payment_link' => $payment_link,
                );

                $order = $this->product->create_order($order);

                $n = 0;
                foreach ($quantity as $id => $data) {
                    $items[$n]['order_id'] = $order;
                    $items[$n]['product_id'] = $id;
                    $items[$n]['order_qty'] = $data['qty'];
                    $items[$n]['order_price'] = $data['price'];

                    $n++;
                }

                $this->product->create_order_items($items);

                $this->cart->destroy();
                $this->session->unset_userdata('order_quantity');
                $this->session->unset_userdata('total_price');
                $this->session->unset_userdata('coupon_id');

                $result = $this->wa($phone_number, "Terima kasih telah order. silahkan lakukan pembayaran");

                $this->session->set_flashdata('order_flash', 'Order berhasil ditambahkan');

                redirect('customer/orders/view/' . $order);
                break;
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

    public function midtrans($orderNumber, $nominal, $name, $nohp)
    {
        $curl = curl_init();

        $key = base64_encode("SB-Mid-server-hb8X2izhnUBF52mluA0Ncgk3" . ":");

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.sandbox.midtrans.com/v1/payment-links",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'transaction_details' => [
                    'order_id' => $orderNumber,
                    'gross_amount' => $nominal
                ],
                'customer_details' => [
                    'first_name' => "$name",
                    "email" => "example@gmail.com",
                    "phone" => "$nohp",
                ],
                'usage_limit' => 2
            ]),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "Authorization: Basic $key"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function getProvince()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
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
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function cart_api()
    {
        $action = $this->input->get('action');

        switch ($action) {
            case 'add_item':
                $id = $this->input->post('id');
                $qty = $this->input->post('qty');
                $sku = $this->input->post('sku');
                $name = $this->input->post('name');
                $price = $this->input->post('price');

                $item = array(
                    'id' => $id,
                    'qty' => $qty,
                    'price' => $price,
                    'name' => $name
                );
                $this->cart->insert($item);
                $total_item = count($this->cart->contents());

                $response = array('code' => 200, 'message' => 'Item dimasukkan dalam keranjang', 'total_item' => $total_item);
                break;
            case 'display_cart':
                $carts = [];

                foreach ($this->cart->contents() as $items) {
                    $carts[$items['rowid']]['id'] = $items['id'];
                    $carts[$items['rowid']]['name'] = $items['name'];
                    $carts[$items['rowid']]['qty'] = $items['qty'];
                    $carts[$items['rowid']]['price'] = $items['price'];
                    $carts[$items['rowid']]['subtotal'] = $items['subtotal'];
                }

                $response = array('code' => 200, 'carts' => $carts);
                break;
            case 'cart_info':
                $total_price = $this->cart->total();
                $total_item = count($this->cart->contents());

                $data['total_price'] = $total_price;
                $data['total_item'] = $total_item;

                $response['data'] = $data;
                break;
            case 'remove_item':
                $rowid = $this->input->post('rowid');

                $this->cart->remove($rowid);

                $total_price = $this->cart->total();
                $ongkir = (int) ($total_price >= get_settings('min_shop_to_free_shipping_cost')) ? 0 : get_settings('shipping_cost');
                $data['code'] = 204;
                $data['message'] = 'Item dihapus dari keranjang';
                $data['total']['subtotal'] = 'Rp ' . format_rupiah($total_price);
                $data['total']['ongkir'] = ($ongkir > 0) ? 'Rp ' . format_rupiah($ongkir) : 'Gratis';
                $data['total']['total'] = 'Rp ' . format_rupiah($total_price + $ongkir);

                $response = $data;
                break;
        }

        $response = json_encode($response);
        $this->output->set_content_type('application/json')
            ->set_output($response);
    }

    public function _create_order_number($quantity, $user_id, $coupon_id)
    {
        $this->load->helper('string');

        $alpha = strtoupper(random_string('alpha', 3));
        $num = random_string('numeric', 3);
        $count_qty = count($quantity);


        $number = $alpha . date('j') . date('n') . date('y') . $count_qty . $user_id . $coupon_id . $num;
        //Random 3 letter . Date . Month . Year . Quantity . User ID . Coupon Used . Numeric

        return $number;
    }
}
