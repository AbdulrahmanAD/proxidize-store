<?php
/**
 * Plugin Name: Proxidize Product Search
 * Description: Custom plugin to integrate Proxidize API into product search functionality.
 * Version: 1.0
 * Author: Abdulrahman Abudabaseh
 * Author URI: proxidize.com
 **/

 if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'initialize_proxidize_product_search', 999);


function initialize_proxidize_product_search() {
    add_action('pre_get_posts', 'intercept_product_search');


    function intercept_product_search($query) {
        if ($query->is_search() && $query->get('post_type') === 'product') {
    
            // Fetch existing product names from the database (all at once to reduce the number of queries)
            $existing_product_names = get_existing_product_names();
    
            $search_keyword = sanitize_text_field($query->get('s'));
            $response = wp_remote_get('http://e1.proxidize.com:5500/search?query=' . urlencode($search_keyword));
    
            if (is_wp_error($response)) {
                error_log($response->get_error_message());
                return;
            }
            $data = json_decode(wp_remote_retrieve_body($response));
            if (!is_object($data) || !isset($data->products)) {
                return;
            }
    
            $new_products = array();
            $existing_products = array();
    
            // Seperate the new products from the already existing products.
            foreach ($data->products as $product_data) {
                if (in_array($product_data->name, $existing_product_names)) {
                    $existing_products[] = $product_data;
                } else {
                    $new_products[] = $product_data;
                }
            }
    
            // Process products in batches (batch size can vary depending on what is most efficient)
            $batch_size = 5;
            $num_batches_new = ceil(count($new_products) / $batch_size);
            for ($i = 0; $i < $num_batches_new; $i++) {
                $batch_new = array_slice($new_products, $i * $batch_size, $batch_size);
                process_new_products($batch_new);
            }

            $num_batches_existing = ceil(count($existing_products) / $batch_size);
            for ($i = 0; $i < $num_batches_existing; $i++) {
                $batch_existing = array_slice($existing_products, $i * $batch_size, $batch_size);
                update_existing_products($batch_existing);
            }
        }
    }
    
    function get_existing_product_names() {
        global $wpdb;
        $query = "SELECT post_title FROM $wpdb->posts WHERE post_type = 'product'";
        $product_names = $wpdb->get_col($query);
        return $product_names;
    }
    
    function process_new_products($new_products) { // In this case, the product doesn't exist in the database, so we have to add it.
        foreach ($new_products as $product_data) {
            $product = new WC_Product();
            $product->set_name($product_data->name);
            $product->set_status("publish"); 
            $product->set_catalog_visibility('visible');
            $product->set_description(''); 
            $product->set_sku(''); 
            $product->set_price($product_data->price);
            $product->set_regular_price($product_data->price); 
            $product->set_manage_stock(true); 
            $product->set_stock_quantity(10); // store stock as 10, since it was not specified in the JSON response.
            $product->set_stock_status($product_data->in_stock ? 'instock' : 'outofstock');
            $product->set_backorders('no');
            $product->set_reviews_allowed(true);
            $product->set_sold_individually(false); 
            $product_id = $product->save();
            set_product_categories($product_id, $product_data->category);
        }
    }
    
    function update_existing_products($existing_products) { // In this case, the product already exists in the database, so we just update it's info in case it changed.
        foreach ($existing_products as $product_data) {
            $product_id = product_exists($product_data->name);
            $product = wc_get_product($product_id);
            $product->set_price($product_data->price);
            $product->set_regular_price($product_data->price); 
            $product->set_stock_status($product_data->in_stock ? 'instock' : 'outofstock'); 
            $product_id = $product->save();
            set_product_categories($product_id, $product_data->category);
        }
    }

    // This function sets the categories for the product, if the category doesn't exist, it creates it.
    function set_product_categories($product_id, $category_string) {
        $categories = array_map('trim', explode(';;', $category_string));
        $term_ids = array();
        foreach ($categories as $category) {
            $term = get_term_by('name', $category, 'product_cat');
            if (!$term) {
                $term_info = wp_insert_term($category, 'product_cat');
                if (!is_wp_error($term_info)) {
                    $term_ids[] = $term_info['term_id'];
                }
            } else {
                $term_ids[] = $term->term_id;
            }
        }
        wp_set_object_terms($product_id, $term_ids, 'product_cat');
    }

     // A function to check if a product exists and return it's ID.
    function product_exists($product_name) {
        global $wpdb;
        $post_title = wp_unslash(sanitize_post_field('post_title', $product_name, 0, 'db'));
        $query = "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'product'";
        $product_id = $wpdb->get_var($wpdb->prepare($query, $post_title));
        return $product_id ? $product_id : false;
    }
}
