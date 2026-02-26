<?php
/**
 * WCAG 3.1.1 Language of Page Diagnostic
 *
 * Validates that the HTML lang attribute is properly set so screen readers
 * can pronounce content correctly.
 *
 * @since   1.6035.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Language of Page Diagnostic Class
 *
 * Checks for proper lang attribute on <html> element (WCAG 3.1.1 Level A).
 *
 * @since 1.6035.1200
 */
class Diagnostic_WCAG_Language_Of_Page extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-language-of-page';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Language of Page (WCAG 3.1.1)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML lang attribute for screen reader pronunciation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme header.php for lang attribute.
		$theme_header = get_template_directory() . '/header.php';
		if ( file_exists( $theme_header ) ) {
			$content = file_get_contents( $theme_header );

			// Check if <html> has lang attribute.
			if ( ! preg_match( '/<html[^>]+lang=["\'][a-z]{2,3}(-[A-Z]{2})?["\']/', $content ) ) {
				$issues[] = __( 'HTML element missing lang attribute (e.g., <html lang="en">)', 'wpshadow' );
			}

			// Check for hardcoded English on non-English sites.
			$site_locale   = get_locale();
			$expected_lang = substr( $site_locale, 0, 2 ); // 'en_US' -> 'en'.

			if ( 'en' !== $expected_lang && preg_match( '/<html[^>]+lang=["\']en["\']/', $content ) ) {
				$issues[] = sprintf(
					/* translators: 1: expected language code, 2: site locale */
					__( 'Site locale is %2$s but HTML lang is hardcoded as "en". Should be "%1$s"', 'wpshadow' ),
					$expected_lang,
					$site_locale
				);
			}
		}

		// Check if WordPress language_attributes() is being used.
		$uses_wp_function = false;
		if ( file_exists( $theme_header ) ) {
			$content = file_get_contents( $theme_header );
			if ( strpos( $content, 'language_attributes()' ) !== false ) {
				$uses_wp_function = true;
			}
		}

		if ( ! $uses_wp_function && ! empty( $issues ) ) {
			$issues[] = __( 'Theme should use language_attributes() function for automatic locale handling', 'wpshadow' );
		}

		// Check WordPress site language configuration.
		$site_language = get_bloginfo( 'language' );
		if ( empty( $site_language ) ) {
			$issues[] = __( 'WordPress site language not configured in Settings > General', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site\'s HTML language attribute helps screen readers pronounce text correctly. About 2% of users rely on screen readers and need this set properly. Without it, a French screen reader might try to read English text with French pronunciation, making it unintelligible. This is like having someone read Spanish with an English accent—confusing for everyone.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-language-of-page',
			);
		}

		return null;
	}
}
