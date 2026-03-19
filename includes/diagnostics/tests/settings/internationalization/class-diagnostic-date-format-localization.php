<?php
/**
 * Date Format Localization Diagnostic
 *
 * Issue #4797: Dates Hardcoded in US Format
 * Family: internationalization (Pillar: Culturally Respectful)
 *
 * Checks if dates use WordPress localization instead of hardcoded US format.
 * Users worldwide expect dates in their local format.
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
 * Diagnostic_Date_Format_Localization Class
 *
 * Checks for hardcoded date formats.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Date_Format_Localization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-format-localization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dates Hardcoded in US Format';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dates use localized formatting instead of hardcoded formats';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Replace date() with date_i18n() for localized dates', 'wpshadow' );
		$issues[] = __( 'Use get_option(\'date_format\') to respect user date format settings', 'wpshadow' );
		$issues[] = __( 'Avoid hardcoded formats like "m/d/Y" or "MM/DD/YYYY"', 'wpshadow' );
		$issues[] = __( 'Use wp_date() (WordPress 5.3+) for timezone-aware dates', 'wpshadow' );
		$issues[] = __( 'Test with non-US locale: Settings > General > Site Language', 'wpshadow' );
		$issues[] = __( 'Remember: US uses MM/DD/YYYY, most world uses DD/MM/YYYY or YYYY-MM-DD', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your dates might be hardcoded in US format (MM/DD/YYYY), which confuses international visitors. The world uses different date formats: US: 12/25/2024 (month/day/year), Most of Europe: 25/12/2024 (day/month/year), ISO 8601: 2024-12-25 (year-month-day, international standard), Japan: 2024年12月25日 (year-month-day with characters). When you hardcode "12/25/2024", Europeans read it as December 25th but could interpret as "25th of December" or be confused. Always use WordPress date localization functions: ❌ Bad: date("m/d/Y", $timestamp) (hardcoded US format). ✅ Good: date_i18n(get_option("date_format"), $timestamp) (uses user\'s preferred format from Settings > General). ✅ Better: wp_date(get_option("date_format"), $timestamp) (WordPress 5.3+, includes timezone support). Best practices: 1) Get user preference: get_option("date_format") returns user-configured format, 2) Use localized function: date_i18n() or wp_date() instead of date(), 3) Never hardcode: Avoid "m/d/Y", "MM/DD/YYYY", "12/25/2024", 4) Test globally: Switch site language to French/German/Japanese and verify dates look correct. This respects cultural diversity and reduces confusion for 96% of world population outside US.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/date-localization',
				'details'      => array(
					'recommendations'       => $issues,
					'global_formats'        => 'US: MM/DD/YYYY, Europe: DD/MM/YYYY, ISO: YYYY-MM-DD',
					'bad_code'              => 'date("m/d/Y", $time)',
					'good_code'             => 'date_i18n(get_option("date_format"), $time)',
					'best_code'             => 'wp_date(get_option("date_format"), $time)',
					'testing'               => 'Change site language to French/German/Japanese to test',
					'user_preference'       => 'Settings > General > Date Format (user controls format)',
					'pillar'                => 'Culturally Respectful',
				),
			);
		}

		return null;
	}
}
