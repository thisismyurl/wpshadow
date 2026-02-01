<?php
/**
 * Comment Cookies Consent Diagnostic
 *
 * Tests GDPR cookie consent on comment forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1912
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Cookies Consent Diagnostic Class
 *
 * Validates that cookie consent is properly configured on comment forms.
 *
 * @since 1.2601.1912
 */
class Diagnostic_Comment_Cookies_Consent extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-cookies-consent';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Cookies Consent';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests GDPR cookie consent on comment forms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1912
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check show_comments_cookies_opt_in option.
		$show_comments_cookies = get_option( 'show_comments_cookies_opt_in', '0' );
		if ( '0' === $show_comments_cookies || 0 === $show_comments_cookies ) {
			$issues[] = __( 'Comment cookie consent checkbox is disabled', 'wpshadow' );
		}

		// Check if comments are enabled at all.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( 'closed' === $default_comment_status || 'close' === $default_comment_status ) {
			// Comments are disabled, so cookie consent is not relevant.
			return null;
		}

		// Check if the site is in the EU or targets EU users (we can't know for sure, but we can check the timezone).
		$timezone_string = get_option( 'timezone_string', '' );
		$is_likely_eu    = false;
		if ( ! empty( $timezone_string ) ) {
			$eu_timezones = array( 'Europe/', 'GMT', 'UTC' );
			foreach ( $eu_timezones as $eu_tz ) {
				if ( 0 === strpos( $timezone_string, $eu_tz ) ) {
					$is_likely_eu = true;
					break;
				}
			}
		}

		// Check if there's a privacy policy page (GDPR requirement).
		$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_page_id && ( '0' === $show_comments_cookies || 0 === $show_comments_cookies ) ) {
			$issues[] = __( 'No privacy policy page configured and cookie consent disabled - GDPR compliance may be affected', 'wpshadow' );
		}

		// Check if comment registration is required (reduces cookie concerns).
		$comment_registration = get_option( 'comment_registration', '0' );
		if ( '0' === $comment_registration && ( '0' === $show_comments_cookies || 0 === $show_comments_cookies ) ) {
			$issues[] = __( 'Anonymous commenting allowed without cookie consent - may violate GDPR', 'wpshadow' );
		}

		// Estimate potential impact.
		global $wpdb;
		$comment_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'" );
		if ( $comment_count > 100 && ( '0' === $show_comments_cookies || 0 === $show_comments_cookies ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of comments */
				__( 'Site has %d approved comments - cookie consent is important for active commenting sites', 'wpshadow' ),
				$comment_count
			);
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment cookie consent issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'medium',
			'threat_level'       => 60,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/comment-cookies-consent',
			'family'             => self::$family,
			'details'            => array(
				'issues'                 => $issues,
				'show_comments_cookies'  => $show_comments_cookies,
				'default_comment_status' => $default_comment_status,
				'privacy_policy_page_id' => $privacy_policy_page_id,
				'comment_registration'   => $comment_registration,
				'is_likely_eu'           => $is_likely_eu,
				'comment_count'          => isset( $comment_count ) ? $comment_count : 0,
			),
		);
	}
}
