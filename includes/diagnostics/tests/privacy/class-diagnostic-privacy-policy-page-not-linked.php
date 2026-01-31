<?php
/**
 * Privacy Policy Page Not Linked Diagnostic
 *
 * Checks if privacy policy page is linked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2349
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Page Not Linked Diagnostic Class
 *
 * Detects unlinked privacy policy.
 *
 * @since 1.2601.2349
 */
class Diagnostic_Privacy_Policy_Page_Not_Linked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-not-linked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Not Linked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if privacy policy page is linked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if privacy policy page is set
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $privacy_page ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Privacy policy page is not configured. Create and link a privacy policy page for GDPR and legal compliance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/privacy-policy-page-not-linked',
			);
		}

		return null;
	}
}
