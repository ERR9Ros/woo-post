// Define a function to handle the new order event
function send_order_to_auspost($order_id) {
    // Retrieve the order object
    $order = wc_get_order($order_id);
    
    // Retrieve the necessary order data
    $customer = $order->get_user();
    $shipping_address = $order->get_shipping_address();
    $items = $order->get_items();
    
    // Prepare the order data for Australia Post
    $data = array(
        'sender' => array(
            'name' => 'Your Name',
            'address' => 'Your Address',
            'city' => 'Your City',
            'state' => 'Your State',
            'postcode' => 'Your Postcode',
            'country' => 'Your Country',
            'email' => 'Your Email',
            'phone' => 'Your Phone',
        ),
        'recipient' => array(
            'name' => $shipping_address['first_name'] . ' ' . $shipping_address['last_name'],
            'address' => $shipping_address['address_1'],
            'city' => $shipping_address['city'],
            'state' => $shipping_address['state'],
            'postcode' => $shipping_address['postcode'],
            'country' => $shipping_address['country'],
            'email' => $customer->email,
            'phone' => $shipping_address['phone'],
        ),
        'items' => array(),
    );
    
    // Add items to the order data
    foreach ($items as $item) {
        $product = $item->get_product();
        $data['items'][] = array(
            'sku' => $product->get_sku(),
            'name' => $product->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => $product->get_price(),
        );
    }
    
    // Convert the data to JSON
    $json_data = json_encode($data);
    
    // Make an API request to Australia Post
    $api_endpoint = 'https://api.auspost.com.au/submit_order';
    $api_key = 'YOUR_AUSTRALIA_POST_API_KEY';
    
    $args = array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body' => $json_data,
    );
    
    $response = wp_remote_post($api_endpoint, $args);
    
    // Check the response from Australia Post
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        // Order successfully submitted to Australia Post
        $order->update_status('completed');
    } else {
        // Handle error case
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'An error occurred while sending the order to Australia Post.';
        // Log or display the error message
        error_log('Australia Post API Error: ' . $error_message);
        // Update order status accordingly
        $order->update_status('failed');
    }
}

// Hook into WooCommerce's new order event
add_action('woocommerce_new_order', 'send_order_to_auspost');

