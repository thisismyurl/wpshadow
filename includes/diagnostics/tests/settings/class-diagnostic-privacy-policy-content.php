<?php
/**
 * Privacy Policy Content Diagnostic
 *
 * Analyzes privacy policy content to ensure it covers essential topics
 * required by GDPR and other privacy regulations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Content Diagnostic Class
 *
 * Validates that privacy policy content includes required sections
 * and disclosures for compliance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Privacy_Policy_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates privacy policy content completeness';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Privacy policy covers data collection
	 * - Covers data usage and sharing
	 * - Covers user rights (GDPR requirements)
	 * - Has contact information
	 * - Has been recently updated
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get the privacy policy page.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( 0 === $privacy_page_id ) {
			// No privacy policy page set - this is handled by another diagnostic.
			return null;
		}

		$privacy_page = get_post( $privacy_page_id );

		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			// Page doesn't exist or isn't published - handled by another diagnostic.
			return null;
		}

		$content = strtolower( strip_tags( $privacy_page->post_content ) );

		// Required topics that should be covered.
		$required_topics = array(
			'data'        => array( 'data collect', 'information collect', 'personal data', 'personal information' ),
			'cookies'     => array( 'cookie', 'tracking' ),
			'rights'      => array( 'rights', 'access', 'deletion', 'rectification', 'portability' ),
			'contact'     => array( 'contact', 'email', 'reach us', 'reach out' ),
			'third_party' => array( 'third party', 'third-party', 'share', 'disclose' ),
		);

		$missing_topics = array();
		foreach ( $required_topics as $topic => $keywords ) {
			$found = false;
			foreach ( $keywords as $keyword ) {
				if ( false !== strpos( $content, $keyword ) ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				$missing_topics[] = $topic;
			}
		}

		if ( ! empty( $missing_topics ) ) {
			$topics_list = implode( ', ', array_map( 'ucwords', str_replace( '_', ' ', $missing_topics ) ) );
			$issues[]    = sprintf(
				/* translators: %s: comma-separated list of missing topics */
				__( 'Privacy policy may be missing important sections: %s', 'wpshadow' ),
				$topics_list
			);
		}

		// Check last update date.
		$last_modified = strtotime( $privacy_page->post_modified );
		$one_year_ago  = strtotime( '-1 year' );

		if ( $last_modified < $one_year_ago ) {
			$issues[] = __( 'Privacy policy has not been updated in over a year; it should be reviewed regularly', 'wpshadow' );
		}

		// Check for placeholder text (common in templates).
		$placeholders = array( '[your company]', '[company name]', '[your email]', '[insert', 'todo', 'to do' );
		foreach ( $placeholders as $placeholder ) {
			if ( false !== strpos( $content, $placeholder ) ) {
				$issues[] = __( 'Privacy policy appears to contain placeholder text that needs to be customized', 'wpshadow' );
				break;
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/privacy-policy-content?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
