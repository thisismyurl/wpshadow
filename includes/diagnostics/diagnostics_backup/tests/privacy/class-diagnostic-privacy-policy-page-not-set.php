<?php
/**
 * Privacy Policy Page Not Set Diagnostic
 *
 * Checks if privacy policy page is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Page Not Set Diagnostic Class
 *
 * Detects missing privacy policy page configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Privacy_Policy_Page_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if privacy policy page is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$privacy_policy_page = get_option( 'wp_page_for_privacy_policy', 0 );

		if ( ! $privacy_policy_page ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Privacy policy page is not configured. This is required by GDPR and other privacy regulations.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-page-not-set',
			);
		}

		// Verify the page exists and is published
		$page = get_post( $privacy_policy_page );

		if ( ! $page || 'publish' !== $page->post_status ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Privacy policy page is not published or no longer exists. Update the setting to point to a valid page.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-page-not-set',
			);
		}

		return null;
	}
}
