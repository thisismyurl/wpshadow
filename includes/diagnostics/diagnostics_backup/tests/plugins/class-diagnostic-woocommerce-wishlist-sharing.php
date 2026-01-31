<?php
/**
 * Woocommerce Wishlist Sharing Diagnostic
 *
 * Woocommerce Wishlist Sharing issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1237.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Wishlist Sharing Diagnostic Class
 *
 * @since 1.1237.0000
 */
class Diagnostic_WoocommerceWishlistSharing extends Diagnostic_Base {

	protected static $slug = 'woocommerce-wishlist-sharing';
	protected static $title = 'Woocommerce Wishlist Sharing';
	protected static $description = 'Woocommerce Wishlist Sharing issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for wishlist plugins
		$has_wishlist = defined( 'YITH_WCWL_VERSION' ) || 
		                defined( 'TINVWL_FVERSION' ) ||
		                class_exists( 'WC_Wishlists' );
		
		if ( ! $has_wishlist ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Wishlist count (YITH)
		if ( defined( 'YITH_WCWL_VERSION' ) ) {
			$wishlist_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl_lists"
			);
			
			if ( $wishlist_count > 1000 ) {
				$issues[] = sprintf( __( '%d wishlists (database overhead)', 'wpshadow' ), $wishlist_count );
			}
			
			// Check 2: Public sharing enabled
			$public_sharing = get_option( 'yith_wcwl_share_fb', 'yes' );
			if ( 'no' === $public_sharing ) {
				$issues[] = __( 'Social sharing disabled (limits virality)', 'wpshadow' );
			}
			
			// Check 3: Privacy settings
			$default_privacy = get_option( 'yith_wcwl_default_privacy', 'public' );
			if ( 'public' === $default_privacy ) {
				$issues[] = __( 'Wishlists public by default (privacy concern)', 'wpshadow' );
			}
		}
		
		// Check 4: Guest wishlists
		$guest_wishlist = get_option( 'yith_wcwl_enable_wishlist', 'yes' );
		if ( 'yes' === $guest_wishlist ) {
			$issues[] = __( 'Guest wishlists enabled (session storage overhead)', 'wpshadow' );
		}
		
		// Check 5: Email sharing throttling
		$email_sharing = get_option( 'yith_wcwl_share_email', 'yes' );
		if ( 'yes' === $email_sharing ) {
			$rate_limit = get_option( 'yith_wcwl_email_rate_limit', 0 );
			if ( 0 === $rate_limit ) {
				$issues[] = __( 'No email rate limiting (spam risk)', 'wpshadow' );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of wishlist sharing issues */
				__( 'WooCommerce wishlist has %d sharing issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-wishlist-sharing',
		);
	}
}
