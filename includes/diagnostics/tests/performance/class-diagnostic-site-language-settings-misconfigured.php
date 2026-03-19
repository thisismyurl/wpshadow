<?php
/**
 * Site Language Settings Misconfigured Diagnostic
 *
 * Tests for language and locale settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Language Settings Misconfigured Diagnostic Class
 *
 * Tests for language and locale configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Site_Language_Settings_Misconfigured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-language-settings-misconfigured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Language Settings Misconfigured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for language and locale settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check site language.
		$site_language = get_option( 'WPLANG' );

		if ( empty( $site_language ) ) {
			$site_language = 'en_US'; // Default.
		}

		// Check if language pack is installed.
		$translations = wp_get_installed_translations( 'core' );

		if ( $site_language !== 'en_US' && empty( $translations[ $site_language ] ) ) {
			$issues[] = sprintf(
				/* translators: %s: language code */
				__( 'Language pack for %s not installed', 'wpshadow' ),
				$site_language
			);
		}

		// Check timezone setting.
		$timezone_string = get_option( 'timezone_string' );

		if ( empty( $timezone_string ) ) {
			$gmt_offset = get_option( 'gmt_offset' );
			if ( empty( $gmt_offset ) ) {
				$issues[] = __( 'No timezone configured - times may display incorrectly', 'wpshadow' );
			} else {
				$issues[] = __( 'Using GMT offset instead of timezone string - daylight savings may not work correctly', 'wpshadow' );
			}
		} else {
			// Verify timezone is valid.
			if ( ! timezone_open( $timezone_string ) ) {
				$issues[] = sprintf(
					/* translators: %s: timezone string */
					__( 'Invalid timezone string: %s', 'wpshadow' ),
					$timezone_string
				);
			}
		}

		// Check date and time format settings.
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		if ( empty( $date_format ) || $date_format === 'F j, Y' ) {
			$issues[] = __( 'Using default date format - should match your locale', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-language-settings-misconfigured',
			);
		}

		return null;
	}
}
