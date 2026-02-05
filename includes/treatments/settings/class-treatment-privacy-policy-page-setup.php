<?php
/**
 * Privacy Policy Page Setup Treatment
 *
 * Verifies that a privacy policy page is properly configured and accessible,
 * which is required for GDPR and other privacy law compliance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Page Setup Treatment Class
 *
 * Ensures the privacy policy page exists, is published, and properly configured.
 *
 * @since 1.6032.1600
 */
class Treatment_Privacy_Policy_Page_Setup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-setup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Setup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies privacy policy page configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Privacy policy page is set in Settings > Privacy
	 * - Page exists and is published
	 * - Page is accessible (not password protected)
	 * - Page has actual content
	 *
	 * @since  1.6032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get the privacy policy page ID.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( 0 === $privacy_page_id ) {
			$issues[] = __( 'No privacy policy page has been set in Settings > Privacy', 'wpshadow' );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/privacy-policy-page-setup',
			);
		}

		// Check if the page exists.
		$privacy_page = get_post( $privacy_page_id );

		if ( ! $privacy_page ) {
			$issues[] = __( 'The configured privacy policy page no longer exists', 'wpshadow' );
		} else {
			// Check if page is published.
			if ( 'publish' !== $privacy_page->post_status ) {
				$issues[] = sprintf(
					/* translators: %s: current post status */
					__( 'Privacy policy page exists but is not published (status: %s)', 'wpshadow' ),
					$privacy_page->post_status
				);
			}

			// Check if page is password protected.
			if ( ! empty( $privacy_page->post_password ) ) {
				$issues[] = __( 'Privacy policy page is password protected, making it inaccessible to visitors', 'wpshadow' );
			}

			// Check if page has content.
			$content = trim( $privacy_page->post_content );
			if ( empty( $content ) ) {
				$issues[] = __( 'Privacy policy page exists but has no content', 'wpshadow' );
			} elseif ( strlen( $content ) < 500 ) {
				$issues[] = __( 'Privacy policy page has very little content (less than 500 characters)', 'wpshadow' );
			}

			// Check if page title is appropriate.
			$title = trim( $privacy_page->post_title );
			if ( empty( $title ) ) {
				$issues[] = __( 'Privacy policy page has no title', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/privacy-policy-page-setup',
			);
		}

		return null;
	}
}
