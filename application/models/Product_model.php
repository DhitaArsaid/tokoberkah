<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_products()
    {
        return $this->db->get('products')->result();
    }

    public function best_deal_product()
    {
        return $this->db->where('is_available', 1)
            ->order_by('current_discount', 'DESC')
            ->limit(1)
            ->get('products')
            ->row();
    }

    public function is_product_exist($id, $sku)
    {
        return $this->db->where(array('id' => $id, 'sku' => $sku))->get('products')->num_rows() > 0;
    }

    public function product_data($id)
    {
        return $this->db->query("
            SELECT p.*, pc.name as category_name
            FROM products p
            JOIN product_category pc ON pc.id = p.category_id
            WHERE p.id = '$id'
        ")->row();
    }

    public function related_products($current, $category)
    {
        return $this->db->where(array('id !=' => $current, 'category_id' => $category))
            ->limit(4)
            ->get('products')
            ->result();
    }

    public function create_order(array $data)
    {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    public function create_order_items($data)
    {
        return $this->db->insert_batch('order_items', $data);
    }

    public function get_available_products()
    {
        return $this->db->where('is_available', 1)->get('products')->result();
    }

    // Function to get product stock by product_id
    public function get_product_stock($product_id)
    {
        $this->db->select('stock');
        $this->db->from('products');
        $this->db->where('id', $product_id);
        $query = $this->db->get();

        // Check if query result exists before accessing the row
        if ($query->num_rows() > 0) {
            return $query->row()->stock;
        } else {
            return 0; // or any default value
        }
    }
}
