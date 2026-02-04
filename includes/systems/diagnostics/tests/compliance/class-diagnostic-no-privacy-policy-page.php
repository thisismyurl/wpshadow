<?php
/**
 * No Privacy Policy Page Diagnostic
 *
 * Detects when a privacy policy page is missing,
 * violating privacy laws and user trust expectations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Privacy Policy Page
 *
 * Checks whether a privacy policy page exists
 * and is properly linked in the footer.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Privacy_Policy_Page extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-privacy-policy-page';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a privacy policy page exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for privacy policy page
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $privacy_page_id ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site doesn\'t have a privacy policy page. Privacy policies are legally required in most jurisdictions and build user trust. They explain: what data you collect, how you use it, who you share it with, how long you keep it, user rights. WordPress can auto-generate a privacy policy page. Without one, users don\'t know what to expect, and you\'re exposed to legal risk.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Legal Compliance & Trust',
					'potential_gain' => 'Legal protection and user trust',
					'roi_explanation' => 'Privacy policy is legally required and builds user trust by explaining data practices transparently.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-page',
			);
		}

		// Check if privacy policy is linked in footer
		$footer = wp_remote_get( home_url() );
		if ( ! is_wp_error( $footer ) ) {
			$body = wp_remote_retrieve_body( $footer );
			$privacy_url = get_permalink( $privacy_page_id );
			$is_linked = strpos( $body, $privacy_url ) !== false;

			if ( ! $is_linked ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __(
						'Your privacy policy exists but isn\'t linked anywhere on your site. Users can\'t find it. Privacy policies should be linked in the footer (where users expect to find legal pages) so users can access them easily.',
						'wpshadow'
					),
					'severity'      => 'medium',
					'threat_level'  => 50,
					'auto_fixable'  => false,
					'business_impact' => array(
						'metric'         => 'User Trust & Visibility',
						'potential_gain' => 'Make privacy policy discoverable',
						'roi_explanation' => 'Privacy policy link in footer makes legal commitment visible to users and search engines.',
					),
					'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-footer-link',
				);
			}
		}

		return null;
	}
}
