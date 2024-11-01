<?php 

/**
 * 
 */
class Domain_coupon 
{
	
	function __construct()
	{
		// add domain field action
		add_action( 'woocommerce_coupon_options', array($this,'add_coupon_revenue_dropdown_checkbox'), 10, 2 );
		// save domain field action
		add_action( 'woocommerce_coupon_options_save', array($this,'save_coupon_revenue_dropdown_checkbox'));
		// add coupon by email domain action
		add_action( 'woocommerce_cart_calculate_fees',array($this, 'add_extra_coupon'));
		// add coupon on email change action
        add_action( 'wp_footer', array($this, 'cart_update_script'), 999 );
	}

	// check woocommerce is active
	public function init() {
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $class = 'notice notice-error';
            $message = __("Error! <a href='https://wordpress.org/plugins/woocommerce/' target='_blank'>WooCommerce</a> Plugin is required to activate Woocommerce Gift Product", 'optinspin');
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
            if (!function_exists('deactivate_plugins')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            deactivate_plugins(GIFT_BASEPATH);
        }
    }

    // add domain field in woocommerce coupon section
    public function add_coupon_revenue_dropdown_checkbox($coupon_get_id, $coupon) { 
	    woocommerce_wp_text_input(
			array(
				'id'          => 'coupon_domain',
				'label'       => __( 'Coupon Domain', 'woocommerce' ),
				'placeholder' => 'gmail.com,outlook.com',
				'description' => __( 'Domains of the coupon.', 'woocommerce' ),
				'data_type'   => 'text',
				'desc_tip'    => true,
				'value'       => get_post_meta($coupon_get_id, 'coupon_domain', true),
			)
		);
	}

	// save domain field in woocommerce coupon section
	public function save_coupon_revenue_dropdown_checkbox( $post_id ) {
	    $coupon_domain = $_POST['coupon_domain'];
	    update_post_meta( $post_id, 'coupon_domain', $coupon_domain );
	}

	// add coupon by email domain
	public function add_extra_coupon()
	{
		global $woocommerce;
		$email = WC()->customer->get_billing_email();
		if (isset($_POST['post_data'])) {
			$data = explode('&', $_POST['post_data']);
			for ($i=0; $i < sizeof($data); $i++) { 
				if (strpos($data[$i], 'billing_email=') > -1) {
					$email = explode('=', $data[$i])[1];
					$email = str_replace('%40', '@', $email);
					$email = sanitize_email($email);
				}
			}
		}
		$coupons = get_posts( array(
		    'posts_per_page'   => -1,
		    'post_type'        => 'shop_coupon',
		    'post_status'      => 'publish',
		) );
		if (!empty($coupons) && !empty($email)) {
			$email = explode('@', $email);
			$email = $email[1];
            foreach ($coupons as $coupon) {
            	$post_id = $coupon->ID;
            	$getCouponDomain = get_post_meta($post_id, 'coupon_domain', true);
            	$getCouponExpair = get_post_meta($post_id, 'expiry_date', true);
            	$coupon_code = __(get_the_title($post_id), 'woocommerce');
            	if ($this->is_referral_coupon_valid( $coupon_code )) {
            		$getCouponDomain = explode(',', $getCouponDomain);
            		$key = array_search($email, $getCouponDomain);
            		if ($key > -1) {
                        if (array_key_exists($key, $getCouponDomain)) {
                            $co = $woocommerce->cart->has_discount( $coupon_code );
						    if (!$co){
						        WC()->cart->remove_coupons();
						        $woocommerce->cart->add_discount( $coupon_code );
						    }
                        }
                    }
                    else{
                    	$woocommerce->cart->remove_coupon( $coupon_code );
                    }
            	}
            	
            }
        }
	}

	// check coupon is valid
	public function is_referral_coupon_valid( $coupon_code ) {
	    $coupon = new WC_Coupon( $coupon_code );   
	    $discounts = new WC_Discounts( WC()->cart );
	    $valid_response = $discounts->is_coupon_valid( $coupon );
	    if ( is_wp_error( $valid_response ) ) {
	        return false;
	    } else {
	        return true;
	    }
	}

	// add coupon on email change
    public function cart_update_script() {
        if (is_checkout()) :
        ?>
        <script>
            jQuery( function( $ ) {
                if ( typeof woocommerce_params === 'undefined' ) {
                    return false;
                }
                $checkout_form = $( 'form.checkout' );
                $checkout_form.on( 'blur', 'input[name="billing_email"]', function() {
                    $checkout_form.trigger( 'update' );
                });
            });
        </script>
        <?php
        endif;
    }
}