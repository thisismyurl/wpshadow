<?php
/**
 * Privacy Policy Link Visibility Diagnostic
 *
 * Tests if privacy policy link displays correctly in footer and forms.
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
 * Privacy Policy Link Visibility Diagnostic Class
 *
 * Validates that privacy policy link is properly configured and visible.
 *
 * @since 1.2601.1912
 */
class Diagnostic_Privacy_Policy_Link_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-link-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Link Visibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if privacy policy link displays correctly';

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

		// Check if privacy policy page is set.
		$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_page_id ) {
			$issues[] = __( 'No privacy policy page has been configured', 'wpshadow' );
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Privacy policy page is not configured', 'wpshadow' ),
				'severity'           => 'medium',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/privacy-policy-link-visibility',
				'family'             => self::$family,
				'details'            => array(
					'issues'                     => $issues,
					'privacy_policy_page_id'     => $privacy_policy_page_id,
					'privacy_policy_page_status' => 'not_set',
				),
			);
		}

		// Check if the privacy policy page exists and is published.
		$privacy_page = get_post( $privacy_policy_page_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			$status   = $privacy_page ? $privacy_page->post_status : 'deleted';
			$issues[] = sprintf(
				/* translators: %s: page status */
				__( 'Privacy policy page exists but is not published (status: %s)', 'wpshadow' ),
				$status
			);
		}

		// Check if comment form shows cookies checkbox (GDPR requirement).
		$show_comments_cookies = get_option( 'show_comments_cookies_opt_in', '0' );
		if ( '0' === $show_comments_cookies || 0 === $show_comments_cookies ) {
			$issues[] = __( 'Comment cookie consent checkbox is disabled - may affect GDPR compliance', 'wpshadow' );
		}

		// Check if privacy policy link is in footer (WordPress adds it by default).
		// We can't directly test HTML rendering here, but we can check if the page is accessible.
		if ( $privacy_page ) {
			$privacy_url = get_permalink( $privacy_policy_page_id );
			if ( false === $privacy_url || empty( $privacy_url ) ) {
				$issues[] = __( 'Privacy policy page exists but permalink cannot be generated', 'wpshadow' );
			}
		}

		// Check if get_the_privacy_policy_link() function is available (WP 4.9.6+).
		if ( ! function_exists( 'get_the_privacy_policy_link' ) ) {
			$issues[] = __( 'Privacy policy link function not available - WordPress version may be outdated', 'wpshadow' );
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
				__( 'Found %d privacy policy visibility issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'medium',
			'threat_level'       => 50,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/privacy-policy-link-visibility',
			'family'             => self::$family,
			'details'            => array(
				'issues'                     => $issues,
				'privacy_policy_page_id'     => $privacy_policy_page_id,
				'privacy_policy_page_status' => isset( $privacy_page ) ? $privacy_page->post_status : 'unknown',
				'show_comments_cookies'      => $show_comments_cookies,
			),
		);
	}
}
