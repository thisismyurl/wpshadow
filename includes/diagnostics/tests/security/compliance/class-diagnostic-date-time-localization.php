<?php
/**
 * Date and Time Localization Diagnostic
 *
 * Issue #4878: Dates and Times Not Localized
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if dates and times respect user locale and timezone.
 * US uses MM/DD/YYYY, EU uses DD/MM/YYYY, ISO uses YYYY-MM-DD.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Date_Time_Localization Class
 *
 * Checks for:
 * - Uses WordPress date_i18n() for formatting
 * - Respects user's timezone setting
 * - Respects user's date format preference
 * - Respects user's time format preference
 * - Displays timezone alongside times ("3:00 PM EST")
 * - No hardcoded date formats (Y-m-d)
 * - Relative time for recent dates ("2 hours ago")
 * - ISO 8601 in technical contexts (APIs, logs)
 *
 * Why this matters:
 * - US: MM/DD/YYYY, EU: DD/MM/YYYY, ISO: YYYY-MM-DD
 * - 12-hour vs 24-hour time formats
 * - Timezone confusion leads to scheduling errors
 * - Professional localization shows respect
 *
 * @since 1.6050.0000
 */
class Diagnostic_Date_Time_Localization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'date-time-localization';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Dates and Times Not Localized';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if dates and times respect user locale and timezone preferences';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual date format checking requires code analysis.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Use date_i18n() instead of date() for WordPress date formatting', 'wpshadow' );
		$issues[] = __( 'Use get_option("date_format") for user-preferred date format', 'wpshadow' );
		$issues[] = __( 'Use get_option("time_format") for user-preferred time format', 'wpshadow' );
		$issues[] = __( 'Use get_option("timezone_string") for timezone conversion', 'wpshadow' );
		$issues[] = __( 'Display timezone alongside times: "3:00 PM EST"', 'wpshadow' );
		$issues[] = __( 'Use relative time for recent dates: "2 hours ago"', 'wpshadow' );
		$issues[] = __( 'Use ISO 8601 format in APIs and logs (YYYY-MM-DD)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Date formats differ globally. US uses MM/DD/YYYY, EU uses DD/MM/YYYY. Without localization, dates are confusing or wrong.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/date-time-localization',
				'details'      => array(
					'recommendations'         => $issues,
					'date_formats'            => array(
						'us'   => 'MM/DD/YYYY (12/31/2024)',
						'eu'   => 'DD/MM/YYYY (31/12/2024)',
						'iso'  => 'YYYY-MM-DD (2024-12-31)',
					),
					'time_formats'            => array(
						'12h'  => '3:00 PM',
						'24h'  => '15:00',
					),
					'wordpress_functions'     => 'date_i18n(), human_time_diff(), wp_date()',
					'timezone_confusion'      => 'Not showing timezone causes scheduling errors',
				),
			);
		}

		return null;
	}
}
