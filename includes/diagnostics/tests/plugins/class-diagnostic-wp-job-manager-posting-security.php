<?php
/**
 * WP Job Manager Posting Security Diagnostic
 *
 * Job submissions not properly validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.244.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Posting Security Diagnostic Class
 *
 * @since 1.244.0000
 */
class Diagnostic_WpJobManagerPostingSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-posting-security';
	protected static $title = 'WP Job Manager Posting Security';
	protected static $description = 'Job submissions not properly validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Job moderation required
		$require_moderation = get_option( 'job_manager_submission_requires_approval', 1 );
		if ( ! $require_moderation ) {
			$issues[] = __( 'Jobs auto-published without moderation (spam risk)', 'wpshadow' );
		}
		
		// Check 2: CAPTCHA enabled
		$captcha_enabled = get_option( 'job_manager_enable_captcha', 0 );
		if ( ! $captcha_enabled ) {
			$issues[] = __( 'No CAPTCHA on job forms (bot submissions)', 'wpshadow' );
		}
		
		// Check 3: Guest job posting
		$allow_guest = get_option( 'job_manager_enable_registration', 0 );
		if ( ! $allow_guest ) {
			// If registration is disabled, check if guests can post
			$user_can_post = get_option( 'job_manager_user_can_post_without_account', 0 );
			if ( $user_can_post ) {
				$issues[] = __( 'Guest posting allowed (accountability issue)', 'wpshadow' );
			}
		}
		
		// Check 4: Email validation
		$validate_email = get_option( 'job_manager_validate_user_email', 1 );
		if ( ! $validate_email ) {
			$issues[] = __( 'Email validation disabled (fake accounts)', 'wpshadow' );
		}
		
		// Check 5: Application email required
		$require_email = get_option( 'job_manager_application_method_email', 1 );
		if ( ! $require_email ) {
			$issues[] = __( 'Application emails not required (contact loss)', 'wpshadow' );
		}
		
		// Check 6: Content sanitization
		$allow_html = get_option( 'job_manager_job_description_allow_html', 1 );
		if ( $allow_html ) {
			$issues[] = __( 'HTML allowed in job descriptions (XSS risk)', 'wpshadow' );
		}
		
		// Check 7: Submission rate limiting
		$rate_limit = get_option( 'job_manager_submission_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No submission rate limit (spam floods)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 80;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 73;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of posting security issues */
				__( 'WP Job Manager posting has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-posting-security',
		);
	}
}
