<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function count_all_orders($search = null)
    {
        if (empty($search)) {
            return $this->db->count_all('orders');
        } else {
            $count = $this->db->from('orders o')
                ->join('customers c', 'c.id = o.user_id')
                ->like('u.name', $search)
                ->or_like('o.order_number', $search)
                ->get()
                ->num_rows();
        }
    }

    public function get_all_orders($limit, $start, $search = null)
    {
        // $orders = $this->db->query("
        //     SELECT o.id, o.order_number, o.order_date, o.order_status, o.payment_method, o.total_price, o.total_items, c.name AS coupon, cu.name AS customer
        //     FROM orders o
        //     LEFT JOIN coupons c
        //         ON c.id = o.coupon_id
        //     JOIN customers cu
        //         ON cu.user_id = o.user_id
        //     ORDER BY o.order_date DESC
        //     LIMIT $start, $limit
        // ");

        $this->db->select('o.id, o.order_number, o.order_date, o.order_status, o.payment_method, o.total_price, o.total_items, c.name AS coupon, cu.name AS customer')
            ->join('coupons c', 'c.id = o.coupon_id', 'left')
            ->join('customers cu', 'cu.user_id = o.user_id', 'left')
            ->order_by('o.order_date', 'DESC')
            ->limit($limit, $start);

        if (!is_null($search)) {
            $this->db->like('o.order_number', $search)
                ->or_like('o.total_price', $search)
                ->or_like('cu.name', $search);
        }

        $orders = $this->db->get('orders o');

        return $orders->result();
    }

    public function latest_orders()
    {
        $orders = $this->db->query("
            SELECT o.id, o.order_number, o.order_date, o.order_status, o.payment_method, o.total_price, o.total_items, c.name AS coupon, cu.name AS customer
            FROM orders o
            LEFT JOIN coupons c
                ON c.id = o.coupon_id
            JOIN customers cu
                ON cu.user_id = o.user_id
            ORDER BY o.order_date DESC
            LIMIT 5
        ");

        return $orders->result();
    }

    public function is_order_exist($id)
    {
        return ($this->db->where('id', $id)->get('orders')->num_rows() > 0) ? TRUE : FALSE;
    }

    public function order_data($id)
    {
        $data = $this->db->query("
            SELECT o.*, c.name, c.code, p.id as payment_id, p.payment_price, p.payment_date, p.picture_name, p.payment_status, p.confirmed_date, p.payment_data
            FROM orders o
            LEFT JOIN coupons c
                ON c.id = o.coupon_id
            LEFT JOIN payments p
                ON p.order_id = o.id
            WHERE o.id = '$id'
        ");

        return $data->row();
    }

    public function order_items($id)
    {
        $items = $this->db->query("
            SELECT oi.product_id, oi.order_qty, oi.order_price, p.name, p.picture_name
            FROM order_items oi
            JOIN products p
	            ON p.id = oi.product_id
            WHERE order_id = '$id'");

        return $items->result();
    }

    public function set_status($status, $order_id)
    {
        // Fetch order details
        $order = $this->db->where('id', $order_id)->get('orders')->row();

        // Check if the payment method is COD and the status is to be completed
        if ($order->payment_method == 2 && $status == '3') {
            // Fetch order items
            $order_items = $this->db->where('order_id', $order_id)->get('order_items')->result();

            // Reduce product stock
            foreach ($order_items as $item) {
                $product_id = $item->product_id;
                $quantity = $item->order_qty;
                $this->reduce_product_stock($product_id, $quantity);
            }
        }

        // Update order status
        return $this->db->where('id', $order_id)->update('orders', array('order_status' => $status));
    }

    private function reduce_product_stock($product_id, $quantity)
    {
        // Fetch current product stock
        $product = $this->db->where('id', $product_id)->get('products')->row();

        if (!$product) {
            log_message('error', 'Product with ID ' . $product_id . ' not found.');
            return false; // or handle the error as needed
        }

        $current_stock = $product->stock;

        // Calculate new stock
        $new_stock = $current_stock - $quantity;

        // Update product stock
        return $this->db->where('id', $product_id)->update('products', array('stock' => $new_stock));
    }


    public function product_ordered($id)
    {
        $orders = $this->db->query("
            SELECT oi.*, o.id as order_id, o.order_number, o.order_date, c.name, p.product_unit AS unit
            FROM order_items oi
            JOIN orders o
	            ON o.id = oi.order_id
            JOIN customers c
                ON c.user_id = o.user_id
            JOIN products p
	            ON p.id = oi.product_id
            WHERE oi.product_id = '1'");

        return $orders->result();
    }

    public function order_by($id)
    {
        return $this->db->where('user_id', $id)->order_by('order_date', 'DESC')->get('orders')->result();
    }

    public function order_overview()
    {
        $overview = $this->db->query("
            SELECT MONTH(order_date) month, COUNT(order_date) sale 
            FROM orders
            WHERE order_date >= NOW() - INTERVAL 1 YEAR
            GROUP BY MONTH(order_date)");

        return $overview->result();
    }

    public function income_overview()
    {
        $data = $this->db->query("
            SELECT  MONTH(order_date) AS month, SUM(total_price) AS income
            FROM orders
            GROUP BY MONTH(order_date)");

        return $data->result();
    }

    public function get_order_with_customer($id)
    {
        $this->db->select('orders.*, customers.phone_number as customer_phone, customers.name as customer_name');
        $this->db->from('orders');
        $this->db->join('customers', 'orders.user_id = customers.user_id');
        $this->db->where('orders.user_id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_pending_orders_with_phone()
    {
        $this->db->select('orders.*, customers.phone_number as customer_phone, customers.name as customer_name'); // Tambahkan customer_name di sini
        $this->db->from('orders');
        $this->db->join('customers', 'customers.user_id = orders.user_id');
        $this->db->where('orders.order_status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }
}
