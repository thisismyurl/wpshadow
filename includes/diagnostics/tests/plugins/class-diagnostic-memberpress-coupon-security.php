<?php
/**
 * MemberPress Coupon Security Diagnostic
 *
 * MemberPress coupons exploitable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.528.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Coupon Security Diagnostic Class
 *
 * @since 1.528.0000
 */
class Diagnostic_MemberpressCouponSecurity extends Diagnostic_Base {

	protected static $slug = 'memberpress-coupon-security';
	protected static $title = 'MemberPress Coupon Security';
	protected static $description = 'MemberPress coupons exploitable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify coupons have usage limits
		global $wpdb;
		$mepr_coupons_table = $wpdb->prefix . 'mepr_coupons';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$mepr_coupons_table}'" ) === $mepr_coupons_table ) {
			$unlimited_coupons = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$mepr_coupons_table} 
					WHERE (usage_amount = %d OR usage_amount IS NULL)
					AND status = %s",
					0,
					'enabled'
				)
			);
			
			if ( $unlimited_coupons > 5 ) {
				$issues[] = 'unlimited_use_coupons';
			}
			
			// Check 2: Check for expired coupons still active
			$expired_active = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$mepr_coupons_table} 
					WHERE expires_on < NOW()
					AND status = %s",
					'enabled'
				)
			);
			
			if ( $expired_active > 0 ) {
				$issues[] = 'expired_coupons_still_active';
			}
			
			// Check 3: Check for high-value coupons without restrictions
			$high_value_unrestricted = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$mepr_coupons_table} 
					WHERE discount_amount > %d
					AND first_payment_only = %d
					AND status = %s",
					50,
					0,
					'enabled'
				)
			);
			
			if ( $high_value_unrestricted > 0 ) {
				$issues[] = 'high_value_coupons_unrestricted';
			}
			
			// Check 4: Check for predictable coupon codes
			$recent_coupons = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT code FROM {$mepr_coupons_table} 
					WHERE status = %s
					ORDER BY id DESC LIMIT 10",
					'enabled'
				)
			);
			
			foreach ( $recent_coupons as $coupon ) {
				if ( preg_match( '/^(discount|save|promo|test)[0-9]{1,3}$/i', $coupon->code ) ) {
					$issues[] = 'predictable_coupon_codes';
					break;
				}
			}
		}
		
		// Check 5: Verify coupon stacking is disabled
		$allow_stacking = get_option( 'mepr_allow_coupon_stacking', 'no' );
		if ( 'yes' === $allow_stacking ) {
			$issues[] = 'coupon_stacking_enabled';
		}
		
		// Check 6: Verify email domain restrictions are used
		$email_restrictions = get_option( 'mepr_coupon_email_restrictions', 'no' );
		if ( 'no' === $email_restrictions ) {
			$issues[] = 'no_email_domain_restrictions';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of coupon security issues */
				__( 'MemberPress coupon system has security issues: %s. Insecure coupon configurations can lead to revenue loss and membership fraud.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/memberpress-coupon-security',
			);
		}
		
		return null;
	}
}
