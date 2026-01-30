<?php
/**
 * WP Job Manager Listing Security Diagnostic
 *
 * WP Job Manager listings not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.538.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Listing Security Diagnostic Class
 *
 * @since 1.538.0000
 */
class Diagnostic_WpJobManagerListingSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-listing-security';
	protected static $title = 'WP Job Manager Listing Security';
	protected static $description = 'WP Job Manager listings not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Jobs exist
		$job_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'job_listing'
			)
		);
		
		if ( $job_count === 0 ) {
			return null;
		}
		
		// Check 2: Job moderation enabled
		$moderation = get_option( 'job_manager_moderate_new_listings', 0 );
		if ( ! $moderation ) {
			$issues[] = __( 'Job moderation not enabled (spam risk)', 'wpshadow' );
		}
		
		// Check 3: Anonymous job posting
		$allow_anonymous = get_option( 'job_manager_enable_registration', 0 );
		if ( $allow_anonymous ) {
			$issues[] = __( 'Anonymous job posting enabled (abuse potential)', 'wpshadow' );
		}
		
		// Check 4: Expired jobs cleanup
		$expired_jobs = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				 WHERE pm.meta_key = %s AND pm.meta_value < %s AND p.post_status = 'publish'",
				'_job_expires',
				current_time( 'Y-m-d' )
			)
		);
		
		if ( $expired_jobs > 20 ) {
			$issues[] = sprintf( __( '%d expired jobs still published', 'wpshadow' ), $expired_jobs );
		}
		
		// Check 5: Application email validation
		$validate_email = get_option( 'job_manager_application_method_email_validate', 1 );
		if ( ! $validate_email ) {
			$issues[] = __( 'Application email validation disabled (spam applications)', 'wpshadow' );
		}
		
		// Check 6: Captcha protection
		$has_captcha = get_option( 'job_manager_enable_recaptcha', 0 );
		if ( ! $has_captcha && $job_count > 50 ) {
			$issues[] = __( 'No CAPTCHA protection on job submissions', 'wpshadow' );
		}
		
		// Check 7: Application rate limiting
		$rate_limit = get_option( 'job_manager_application_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = __( 'No rate limiting on job applications (automated abuse)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 78;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 72;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'WP Job Manager listings have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-listing-security',
		);
	}
}
