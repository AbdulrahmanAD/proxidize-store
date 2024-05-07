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

            // Fetch existing product names from the database (all at once to reduce the number of queries)
            global $wpdb;
            $existing_products = $wpdb->get_col("SELECT post_title FROM $wpdb->posts WHERE post_type = 'product'");

            $batch_products = array();
            foreach ($data->products as $product_data) {
                if (!in_array($product_data->name, $existing_products)) {
                    $batch_products[] = $product_data;
                }
            }

            // Process products in batches (batch size can vary depending on what is most efficient)
            $batch_size = 5;
            $num_batches = ceil(count($batch_products) / $batch_size);
            for ($i = 0; $i < $num_batches; $i++) {
                $batch = array_slice($batch_products, $i * $batch_size, $batch_size);
                process_products($batch);
            }
        }
    }

    function process_products($products) {
        foreach ($products as $product_data) {
            // Add or update product information (batch by batch)
            $product_id = product_exists($product_data->name);
            if (!$product_id) { // In this case, the product doesn't exist in the database, so we have to add it.
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
            } else { // In this case, the product already exists in the database, so we just update it's info in case it changed.
                $product = wc_get_product($product_id);
                $product->set_price($product_data->price);
                $product->set_regular_price($product_data->price); 
                $product->set_stock_status($product_data->in_stock ? 'instock' : 'outofstock'); 
                $product_id = $product->save();
            }
            // This piece of code sets the categories for the product, if the category doesn't exist, it creates it.
            $categories = array_map('trim', explode(';;', $product_data->category));
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
    }

     // A function to check if a product exists in the database using the product name, since no SKUs or any other unique identifiers where specified in the API response
    function product_exists($product_name) {
        global $wpdb;
        $post_title = wp_unslash(sanitize_post_field('post_title', $product_name, 0, 'db'));
        $query = "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'product'";
        $product_id = $wpdb->get_var($wpdb->prepare($query, $post_title));
        return $product_id ? $product_id : false;
    }
}
