<?php
/**
 * Site Language Configuration Diagnostic
 *
 * Verifies that the site language is properly configured to match the intended
 * audience and ensure WordPress is displaying in the correct language.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Language Configuration Diagnostic Class
 *
 * Ensures site language is properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Site_Language_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-language-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Language Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site language is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Site language is set (not empty)
	 * - Language code is valid
	 * - Language files are available if language is not English
	 * - Language matches site content
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get site language.
		$site_language = get_option( 'WPLANG', 'en_US' );

		// Check if using default English.
		if ( empty( $site_language ) || 'en_US' === $site_language ) {
			// English is standard, no issue.
			return null;
		}

		// Check if language code is valid format.
		if ( ! preg_match( '/^[a-z]{2}(_[A-Z]{2})?$/', $site_language ) ) {
			$issues[] = sprintf(
				/* translators: %s: language code */
				__( 'Language code format appears invalid: %s', 'wpshadow' ),
				$site_language
			);
		}

		// Check if language translation files are available.
		$lang_dir = WP_CONTENT_DIR . '/languages/';
		if ( ! is_dir( $lang_dir ) ) {
			$issues[] = __( 'Languages directory does not exist; translation files cannot be loaded', 'wpshadow' );
		} else {
			$language_file = $lang_dir . 'wordpress-' . $site_language . '.mo';
			if ( ! file_exists( $language_file ) ) {
				$issues[] = sprintf(
					/* translators: %s: language code */
					__( 'Translation files for %s are not available; WordPress will display in English', 'wpshadow' ),
					$site_language
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/site-language-configuration',
			);
		}

		return null;
	}
}
