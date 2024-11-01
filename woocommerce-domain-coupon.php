<?php 

/**
 * Plugin Name: Woocommerce Domain Coupon
 * Plugin URI: https://www.webewox.com/wp-plugins
 * Description: Woocommerce Domain Coupon
 * Version: 1.0.0
 * Author: Usama Farooq
 * Author URI: https://www.webewox.com
 * Copyright: © 2018 WooCommerce / Domain Coupon.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-domain-coupon
 * WC requires at least: 2.6
 */

require_once __DIR__ . '/Domain.php';
define('DOMAIN_BASEPATH',plugin_basename( __FILE__ ));
$app = new Domain_coupon();
$app->init();