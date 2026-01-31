<?php
/**
 * GeoDirectory Listing Spam Diagnostic
 *
 * GeoDirectory spam protection insufficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.553.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Listing Spam Diagnostic Class
 *
 * @since 1.553.0000
 */
class Diagnostic_GeodirectoryListingSpam extends Diagnostic_Base {

	protected static $slug = 'geodirectory-listing-spam';
	protected static $title = 'GeoDirectory Listing Spam';
	protected static $description = 'GeoDirectory spam protection insufficient';
	protected static $family = 'security';

	public static function check() {
		// Check for GeoDirectory plugin (uses wpbdp function check)
		if ( ! function_exists( 'geodir_get_option' ) && ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Listings exist
		$listing_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type LIKE 'gd_%place' OR post_type = 'wpbdp_listing'"
		);
		
		if ( $listing_count === 0 ) {
			return null;
		}
		
		// Check 2: Listing moderation
		$moderation = get_option( 'geodir_moderate_new_listings', 0 );
		if ( ! $moderation ) {
			$issues[] = __( 'New listing moderation not enabled (spam risk)', 'wpshadow' );
		}
		
		// Check 3: CAPTCHA protection
		$captcha_enabled = get_option( 'geodir_captcha_listing_form', 0 );
		if ( ! $captcha_enabled ) {
			$issues[] = __( 'CAPTCHA not enabled on listing forms (automated spam)', 'wpshadow' );
		}
		
		// Check 4: Email verification
		$email_verify = get_option( 'geodir_verify_email_before_listing', 0 );
		if ( ! $email_verify ) {
			$issues[] = __( 'Email verification not required (fake listings)', 'wpshadow' );
		}
		
		// Check 5: Akismet integration
		$akismet_enabled = get_option( 'geodir_enable_akismet', 0 );
		if ( ! $akismet_enabled && defined( 'AKISMET_VERSION' ) ) {
			$issues[] = __( 'Akismet available but not integrated with listings', 'wpshadow' );
		}
		
		// Check 6: Submission rate limiting
		$rate_limit = get_option( 'geodir_listing_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = __( 'No rate limiting on listing submissions (spam floods)', 'wpshadow' );
		}
		
		// Check 7: Pending spam count
		$pending_spam = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE (post_type LIKE 'gd_%place' OR post_type = 'wpbdp_listing')
			 AND post_status = 'pending' AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);
		
		if ( $pending_spam > 20 ) {
			$issues[] = sprintf( __( '%d old pending listings (likely spam, needs cleanup)', 'wpshadow' ), $pending_spam );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 74;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 67;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of spam protection issues */
				__( 'GeoDirectory listing spam protection has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/geodirectory-listing-spam',
		);
	}
}
