<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {
	wp_enqueue_style('astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all');
}
add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);

// Generate Shiprocket JWT Token
function generate_shiprocket_jwt_token() {
	$endpoint = 'https://apiv2.shiprocket.in/v1/external/auth/login';
	$api_email = 'acc.sayandey@gmail.com';
	$api_passwd = 'Sayan@1234';

	$response = wp_remote_post($endpoint, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'email' => $api_email,
            'password' => $api_passwd,
        )),
    ));

	if (is_wp_error($response)) {
        error_log('Shiprocket API error: ' . $response->get_error_message());
        return array('success' => false, 'token' => null);
    }

	$body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

	if (isset($data['token'])) {
        return array('success' => true, 'token' => $data['token']);
    } else {
        return array('success' => false, 'token' => null);
    }
}

// Save JWT token in option table
function get_shiprocket_jwt_token() {
    $token_data = get_option('shiprocket_jwt_token');
    if (!$token_data || current_time('timestamp') - $token_data['timestamp'] > 60) {
        $generate_token = generate_shiprocket_jwt_token();
        if ($generate_token['success']) {
            $token_data = array(
                'token' => $generate_token['token'],
                'timestamp' => current_time('timestamp')
            );
            update_option('shiprocket_jwt_token', $token_data);
            return array('success' => true, 'token' => $token_data['token']);
        } else {
            // Return failure status if token generation fails
            return array('success' => false, 'token' => null);
        }
    }
    return array('success' => true, 'token' => $token_data['token']);
}

function refresh_shiprocket_jwt_token() {
    $generate_token = get_shiprocket_jwt_token();
    if ($generate_token['success']) {
        $token_data = array(
            'token' => $generate_token['token'],
            'timestamp' => current_time('timestamp')
        );
        update_option('shiprocket_jwt_token', $token_data);
    }
}

if (!wp_next_scheduled('refresh_shiprocket_jwt_token_weekly')) {
    wp_schedule_event(current_time('timestamp'), 'weekly', 'refresh_shiprocket_jwt_token_weekly');
}
add_action('refresh_shiprocket_jwt_token_weekly', 'refresh_shiprocket_jwt_token');

// if (function_exists('get_shiprocket_jwt_token')) {
//     $token_data = get_shiprocket_jwt_token();
//     if ($token_data['success']) {
//         echo '<p>Access Token: ' . esc_html($token_data['token']) . '</p>';
// 		echo '<p>Timestamp: ' . esc_html($token_data) . '</p>';
// 		echo '<pre>';
// 		print_r($token_data);
// 		echo '</pre>';
//     } else {
//         echo '<p>Failed to retrieve access token.</p>';
//     }
// }


function show_pincode_form_cart() {
	?>
	<form id="wpzc-store-pincode-checker-form" class="wpzc-pincode-checker__form wpzc-pincode-checker__form--show" method="post">
		<input type="text" placeholder="Enter Pincode" name="pincode" value="" autocomplete="off">
		<input type="submit" class="button" value="Check">
	</form>
	<div class="wpzc-pincode-checker__response"></div>
	<?php
}
add_action('woocommerce_before_cart_totals', 'show_pincode_form_cart', 10);



// add_filter( 'request', function( $vars ) {
 
//     global $wpdb;
 
//     if( ! empty( $vars['pagename'] ) || ! empty( $vars['category_name'] ) || ! empty( $vars['name'] ) || ! empty( $vars['attachment'] ) ) {
 
//         $slug = ! empty( $vars['pagename'] ) ? $vars['pagename'] : ( ! empty( $vars['name'] ) ? $vars['name'] : ( !empty( $vars['category_name'] ) ? $vars['category_name'] : $vars['attachment'] ) );
 
//         $exists = $wpdb->get_var( $wpdb->prepare( "SELECT t.term_id FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'product_cat' AND t.slug = %s" ,array( $slug )));
 
//         if( $exists ){
 
//             $old_vars = $vars;
 
//             $vars = array('product_cat' => $slug );
 
//             if ( !empty( $old_vars['paged'] ) || !empty( $old_vars['page'] ) )
 
//                 $vars['paged'] = ! empty( $old_vars['paged'] ) ? $old_vars['paged'] : $old_vars['page'];
 
//             if ( !empty( $old_vars['orderby'] ) )
 
//                     $vars['orderby'] = $old_vars['orderby'];
 
//                 if ( !empty( $old_vars['order'] ) )
 
//                     $vars['order'] = $old_vars['order'];    
 
//         }
 
//     }
 
//     return $vars;
 
// });
