<?php
/**
 * Privacy Policy Content Diagnostic
 *
 * Validates privacy policy exists and has substantial, custom content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2602.0100
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
 * Validates that a privacy policy page exists, is published, and contains
 * substantial custom content beyond WordPress defaults.
 *
 * @since 1.2602.0100
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
	protected static $description = 'Validates privacy policy exists and has content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.0100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		$issues  = array();
		$details = array(
			'page_id' => $page_id,
		);

		// Check if privacy policy page is set.
		if ( 0 === $page_id ) {
			$issues[] = __( 'No privacy policy page configured. This is required for GDPR, CCPA, and other privacy regulations.', 'wpshadow' );
		} else {
			// Get the page.
			$page = get_post( $page_id );

			if ( empty( $page ) ) {
				$issues[] = __( 'Configured privacy policy page not found. The page may have been deleted.', 'wpshadow' );
			} else {
				$details['page_status'] = $page->post_status;
				$details['page_title']  = $page->post_title;

				// Check if page is published.
				if ( 'publish' !== $page->post_status ) {
					$issues[] = sprintf(
						/* translators: %s: Current page status */
						__( 'Privacy policy page is not published (current status: %s). Users cannot access it.', 'wpshadow' ),
						$page->post_status
					);
				}

				// Check content length.
				$content        = wp_strip_all_tags( $page->post_content );
				$content_length = strlen( $content );
				$word_count     = str_word_count( $content );

				$details['content_length'] = $content_length;
				$details['word_count']     = $word_count;

				// Check if content is too short.
				if ( $content_length < 500 ) {
					$issues[] = sprintf(
						/* translators: %d: Current character count */
						__( 'Privacy policy content is too short (%d characters). Recommended: at least 500 characters for legal compliance.', 'wpshadow' ),
						$content_length
					);
				}

				// Check for key privacy terms to ensure it's not default boilerplate.
				$has_data_term        = stripos( $content, 'data' ) !== false;
				$has_privacy_term     = stripos( $content, 'privacy' ) !== false;
				$has_cookies_term     = stripos( $content, 'cookie' ) !== false;
				$has_personal_term    = stripos( $content, 'personal' ) !== false;
				$has_information_term = stripos( $content, 'information' ) !== false;

				$details['has_privacy_terms'] = array(
					'data'        => $has_data_term,
					'privacy'     => $has_privacy_term,
					'cookies'     => $has_cookies_term,
					'personal'    => $has_personal_term,
					'information' => $has_information_term,
				);

				$key_terms_found = ( $has_data_term ? 1 : 0 ) +
								( $has_privacy_term ? 1 : 0 ) +
								( $has_cookies_term ? 1 : 0 ) +
								( $has_personal_term ? 1 : 0 ) +
								( $has_information_term ? 1 : 0 );

				if ( $key_terms_found < 3 ) {
					$issues[] = __( 'Privacy policy content appears incomplete. It should cover data collection, privacy practices, cookies, and personal information handling.', 'wpshadow' );
				}

				// Check for WordPress default boilerplate.
				if ( stripos( $content, 'Who we are' ) !== false && stripos( $content, 'Suggested text:' ) !== false ) {
					$issues[] = __( 'Privacy policy contains WordPress default boilerplate. Customize it for your specific site and practices.', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Privacy policy page is missing, not published, or has insufficient content. This is required for legal compliance with GDPR, CCPA, and other privacy laws.', 'wpshadow' ),
				'severity'           => 'high',
				'threat_level'       => 60,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/privacy-privacy-policy-content',
				'family'             => self::$family,
				'details'            => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
