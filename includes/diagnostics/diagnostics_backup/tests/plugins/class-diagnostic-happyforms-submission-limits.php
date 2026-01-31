<?php
/**
 * Happyforms Submission Limits Diagnostic
 *
 * Happyforms Submission Limits issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1209.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Happyforms Submission Limits Diagnostic Class
 *
 * @since 1.1209.0000
 */
class Diagnostic_HappyformsSubmissionLimits extends Diagnostic_Base {

	protected static $slug = 'happyforms-submission-limits';
	protected static $title = 'Happyforms Submission Limits';
	protected static $description = 'Happyforms Submission Limits issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for HappyForms plugin
		$has_happyforms = class_exists( 'HappyForms' ) ||
		                  defined( 'HAPPYFORMS_VERSION' ) ||
		                  function_exists( 'happyforms_get_form' );

		if ( ! $has_happyforms ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Form count
		$form_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				'happyform'
			)
		);

		if ( $form_count === 0 ) {
			return null;
		}

		// Check 2: Submission limits
		$has_limits = get_option( 'happyforms_submission_limits', 'no' );
		if ( 'no' === $has_limits ) {
			$issues[] = __( 'No submission limits (spam risk)', 'wpshadow' );
		}

		// Check 3: Rate limiting
		$rate_limit = get_option( 'happyforms_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (flood attacks)', 'wpshadow' );
		}

		// Check 4: CAPTCHA
		$captcha_enabled = get_option( 'happyforms_captcha', 'no' );
		if ( 'no' === $captcha_enabled ) {
			$issues[] = __( 'CAPTCHA disabled (bot submissions)', 'wpshadow' );
		}

		// Check 5: Email notifications
		$notify_admin = get_option( 'happyforms_notify_admin', 'yes' );
		if ( 'yes' === $notify_admin ) {
			$submission_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_happyforms_submission'"
			);
			if ( $submission_count > 100 ) {
				$issues[] = sprintf( __( '%d submissions with email notifications (mail queue)', 'wpshadow' ), $submission_count );
			}
		}

		// Check 6: Data retention
		$retention_days = get_option( 'happyforms_retention_days', 0 );
		if ( $retention_days === 0 ) {
			$issues[] = __( 'Submissions kept forever (database bloat)', 'wpshadow' );
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
				__( 'HappyForms has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/happyforms-submission-limits',
		);
	}
}
