<?php
/**
 * Woocommerce Memberships Access Diagnostic
 *
 * Woocommerce Memberships Access issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.641.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Memberships Access Diagnostic Class
 *
 * @since 1.641.0000
 */
class Diagnostic_WoocommerceMembershipsAccess extends Diagnostic_Base {

	protected static $slug = 'woocommerce-memberships-access';
	protected static $title = 'Woocommerce Memberships Access';
	protected static $description = 'Woocommerce Memberships Access issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify membership access control
		$access_control = get_option( 'wc_memberships_access_control', false );
		if ( ! $access_control ) {
			$issues[] = __( 'Membership access control not enabled', 'wpshadow' );
		}

		// Check 2: Check product access rules
		$product_rules = get_option( 'wc_memberships_product_rules', array() );
		if ( empty( $product_rules ) ) {
			$issues[] = __( 'No membership product access rules configured', 'wpshadow' );
		}

		// Check 3: Verify post access rules
		$post_rules = get_option( 'wc_memberships_post_rules', array() );
		if ( empty( $post_rules ) ) {
			$issues[] = __( 'No membership post access rules configured', 'wpshadow' );
		}

		// Check 4: Check discount rules configuration
		$discount_rules = get_option( 'wc_memberships_discount_rules', false );
		if ( ! $discount_rules ) {
			$issues[] = __( 'Membership discount rules not configured', 'wpshadow' );
		}

		// Check 5: Verify member content protection
		$content_protection = get_option( 'wc_memberships_content_protection', false );
		if ( ! $content_protection ) {
			$issues[] = __( 'Member-only content protection not enabled', 'wpshadow' );
		}

		// Check 6: Check access caching
		$access_cache = get_transient( 'wc_memberships_access_cache' );
		if ( false === $access_cache ) {
			$issues[] = __( 'Membership access caching not active', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WooCommerce Memberships access issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-memberships-access',
			);
		}

		return null;
	}
}
