<?php
/**
 * Woocommerce Wishlist Guest Users Diagnostic
 *
 * Woocommerce Wishlist Guest Users issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1238.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Wishlist Guest Users Diagnostic Class
 *
 * @since 1.1238.0000
 */
class Diagnostic_WoocommerceWishlistGuestUsers extends Diagnostic_Base {

	protected static $slug = 'woocommerce-wishlist-guest-users';
	protected static $title = 'Woocommerce Wishlist Guest Users';
	protected static $description = 'Woocommerce Wishlist Guest Users issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for popular wishlist plugins
		$wishlist_active = class_exists( 'YITH_WCWL' ) || function_exists( 'tinvwl' ) || class_exists( 'WC_Wishlists_Plugin' );
		
		if ( ! $wishlist_active ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Guest wishlist enabled
		$guest_enabled = get_option( 'yith_wcwl_enable_guest_wishlists', 'no' );
		if ( 'no' === $guest_enabled ) {
			return null;
		}
		
		// Check 2: Guest wishlist storage method
		$storage_method = get_option( 'wishlist_guest_storage', 'session' );
		if ( 'session' === $storage_method ) {
			$issues[] = __( 'Guest wishlists use session storage (lost on logout)', 'wpshadow' );
		}
		
		// Check 3: Guest wishlist persistence
		$cookie_expiry = get_option( 'yith_wcwl_guest_cookie_expiry', 30 );
		if ( $cookie_expiry < 90 ) {
			$issues[] = sprintf( __( 'Guest wishlist cookie expires in %d days (short retention)', 'wpshadow' ), $cookie_expiry );
		}
		
		// Check 4: Guest-to-user migration
		$auto_migrate = get_option( 'wishlist_migrate_on_login', false );
		if ( ! $auto_migrate ) {
			$issues[] = __( 'Guest wishlists not migrated on user registration/login', 'wpshadow' );
		}
		
		// Check 5: Old guest wishlist cleanup
		if ( class_exists( 'YITH_WCWL' ) ) {
			$old_wishlists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl
					 WHERE user_id = %d AND dateadded < DATE_SUB(NOW(), INTERVAL 90 DAY)",
					0
				)
			);
			
			if ( $old_wishlists > 100 ) {
				$issues[] = sprintf( __( '%d guest wishlists older than 90 days (cleanup needed)', 'wpshadow' ), $old_wishlists );
			}
		}
		
		// Check 6: Guest email collection
		$collect_email = get_option( 'yith_wcwl_ask_guest_email', 'no' );
		if ( 'no' === $collect_email ) {
			$issues[] = __( 'Guest email not collected (no recovery/marketing opportunity)', 'wpshadow' );
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
				/* translators: %s: list of guest wishlist issues */
				__( 'WooCommerce guest wishlists have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-wishlist-guest-users',
		);
	}
}
