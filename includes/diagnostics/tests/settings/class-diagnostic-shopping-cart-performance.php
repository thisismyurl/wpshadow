<?php
/**
 * Shopping Cart Performance Diagnostic
 *
 * Tests if shopping cart and checkout pages are optimized for performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopping Cart Performance Diagnostic Class
 *
 * Validates that cart and checkout pages load quickly and are
 * optimized for conversion.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Shopping_Cart_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'shopping-cart-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Shopping Cart Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if shopping cart and checkout pages are optimized for performance';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests cart and checkout page performance including caching
	 * exclusions, script optimization, and database queries.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) || ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Get cart and checkout page IDs.
		$cart_page_id = wc_get_page_id( 'cart' );
		$checkout_page_id = wc_get_page_id( 'checkout' );

		// Check if pages exist and are published.
		$cart_page = get_post( $cart_page_id );
		$checkout_page = get_post( $checkout_page_id );

		$cart_exists = ( $cart_page && 'publish' === $cart_page->post_status );
		$checkout_exists = ( $checkout_page && 'publish' === $checkout_page->post_status );

		// Check cache exclusions for cart/checkout.
		$cart_excluded_from_cache = false;
		$checkout_excluded_from_cache = false;

		// WP Rocket exclusions.
		if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$rocket_options = get_option( 'wp_rocket_settings' );
			$excluded_urls = $rocket_options['cache_reject_uri'] ?? array();
			$cart_excluded_from_cache = in_array( '/cart/', $excluded_urls, true );
			$checkout_excluded_from_cache = in_array( '/checkout/', $excluded_urls, true );
		}

		// W3 Total Cache exclusions.
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$w3tc_config = get_option( 'w3tc_config' );
			$w3tc_excluded = $w3tc_config['pgcache.reject.uri'] ?? array();
			$cart_excluded_from_cache = in_array( 'cart', $w3tc_excluded, true );
			$checkout_excluded_from_cache = in_array( 'checkout', $w3tc_excluded, true );
		}

		// Check AJAX cart fragmentation.
		$cart_fragments_enabled = get_option( 'woocommerce_enable_ajax_add_to_cart' ) === 'yes';

		// Check session handling.
		$session_handler = get_option( 'woocommerce_session_handler' );

		// Check for cart optimization plugins.
		$has_cart_optimization = is_plugin_active( 'woocommerce-cart-tab/woocommerce-cart-tab.php' );

		// Count scripts loaded on checkout.
		global $wp_scripts;
		$checkout_scripts = 0;
		if ( $checkout_exists && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $handle, 'wc-' ) === 0 || strpos( $handle, 'woocommerce' ) !== false ) {
					$checkout_scripts++;
				}
			}
		}

		// Check for persistent cart.
		$persistent_cart = get_option( 'woocommerce_persistent_cart_enabled' ) === 'yes';

		// Check database for cart abandonment.
		global $wpdb;
		$abandoned_carts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_sessions
			 WHERE session_expiry < UNIX_TIMESTAMP() - (7 * 24 * 60 * 60)"
		);

		// Check for guest checkout.
		$guest_checkout_enabled = get_option( 'woocommerce_enable_guest_checkout' ) === 'yes';

		// Check checkout field count.
		$checkout_fields = WC()->checkout()->get_checkout_fields();
		$total_checkout_fields = 0;
		foreach ( $checkout_fields as $fieldset ) {
			$total_checkout_fields += count( $fieldset );
		}

		// Check for issues.
		$issues = array();

		// Issue 1: Cart page not excluded from cache.
		if ( ! $cart_excluded_from_cache && is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$issues[] = array(
				'type'        => 'cart_not_excluded',
				'description' => __( 'Cart page not excluded from cache; users may see stale cart contents', 'wpshadow' ),
			);
		}

		// Issue 2: Checkout page not excluded from cache.
		if ( ! $checkout_excluded_from_cache && is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$issues[] = array(
				'type'        => 'checkout_not_excluded',
				'description' => __( 'Checkout page not excluded from cache; payment issues may occur', 'wpshadow' ),
			);
		}

		// Issue 3: Too many scripts on checkout.
		if ( $checkout_scripts > 15 ) {
			$issues[] = array(
				'type'        => 'excessive_checkout_scripts',
				'description' => sprintf(
					/* translators: %d: number of scripts */
					__( 'Checkout page loads %d WooCommerce scripts; should optimize for faster loading', 'wpshadow' ),
					$checkout_scripts
				),
			);
		}

		// Issue 4: Many abandoned carts not cleaned.
		if ( absint( $abandoned_carts ) > 1000 ) {
			$issues[] = array(
				'type'        => 'abandoned_cart_bloat',
				'description' => sprintf(
					/* translators: %s: number of abandoned carts */
					__( '%s abandoned cart sessions in database; clean old sessions', 'wpshadow' ),
					number_format_i18n( absint( $abandoned_carts ) )
				),
			);
		}

		// Issue 5: Too many checkout fields.
		if ( $total_checkout_fields > 20 ) {
			$issues[] = array(
				'type'        => 'excessive_checkout_fields',
				'description' => sprintf(
					/* translators: %d: field count */
					__( 'Checkout has %d fields; reduce to improve conversion rates', 'wpshadow' ),
					$total_checkout_fields
				),
			);
		}

		// Issue 6: Guest checkout disabled.
		if ( ! $guest_checkout_enabled ) {
			$issues[] = array(
				'type'        => 'guest_checkout_disabled',
				'description' => __( 'Guest checkout disabled; forcing registration reduces conversion by ~25%%', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Shopping cart and checkout pages have performance issues that can reduce conversion rates and sales', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/shopping-cart-performance',
				'details'      => array(
					'cart_page_exists'        => $cart_exists,
					'checkout_page_exists'    => $checkout_exists,
					'cart_excluded_from_cache' => $cart_excluded_from_cache,
					'checkout_excluded_from_cache' => $checkout_excluded_from_cache,
					'cart_fragments_enabled'  => $cart_fragments_enabled,
					'session_handler'         => $session_handler,
					'checkout_scripts_count'  => $checkout_scripts,
					'persistent_cart_enabled' => $persistent_cart,
					'abandoned_carts'         => number_format_i18n( absint( $abandoned_carts ) ),
					'guest_checkout_enabled'  => $guest_checkout_enabled,
					'total_checkout_fields'   => $total_checkout_fields,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Exclude cart/checkout from cache, reduce fields, enable guest checkout, clean old sessions', 'wpshadow' ),
					'performance_impact'      => array(
						'1 second slower checkout' => '7% conversion loss',
						'Forced registration'      => '25% cart abandonment increase',
						'Too many fields'          => '10-15% conversion loss per extra field',
					),
					'optimization_tips'       => array(
						'Exclude from cache'      => 'Cart, checkout, account pages must be dynamic',
						'Minimize fields'         => 'Remove optional fields, use autofill',
						'Enable guest checkout'   => 'Allow purchase without registration',
						'Optimize scripts'        => 'Defer non-critical JS',
						'Session cleanup'         => 'Clean sessions older than 7 days',
						'Progress indicator'      => 'Show checkout steps clearly',
					),
				),
			);
		}

		return null;
	}
}
