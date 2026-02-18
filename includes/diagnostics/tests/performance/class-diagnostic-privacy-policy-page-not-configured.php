<?php
/**
 * Privacy Policy Page Not Configured Diagnostic
 *
 * Tests for privacy policy configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Page Not Configured Diagnostic Class
 *
 * Tests for privacy policy page configuration.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Privacy_Policy_Page_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for privacy policy configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if privacy policy page is set.
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $privacy_page_id ) ) {
			$issues[] = __( 'No privacy policy page is configured', 'wpshadow' );
		} else {
			$privacy_page = get_post( $privacy_page_id );

			if ( ! $privacy_page || $privacy_page->post_status !== 'publish' ) {
				$issues[] = __( 'Privacy policy page is not published or does not exist', 'wpshadow' );
			} elseif ( strlen( $privacy_page->post_content ) < 100 ) {
				$issues[] = __( 'Privacy policy content is very short - may not be adequate', 'wpshadow' );
			}
		}

		// Check for GDPR compliance (EU only).
		$locale = get_locale();

		if ( substr( $locale, -2 ) === 'EU' || in_array( $locale, array( 'de_DE', 'fr_FR', 'es_ES', 'it_IT' ), true ) ) {
			if ( empty( $privacy_page_id ) ) {
				$issues[] = __( 'GDPR requires privacy policy - critical for EU sites', 'wpshadow' );
			}
		}

		// Check if privacy link is in footer.
		$privacy_link = wp_get_privacy_policy_url();

		if ( empty( $privacy_link ) ) {
			$issues[] = __( 'Privacy policy link not available for footer', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/privacy-policy-page-not-configured',
			);
		}

		return null;
	}
}
