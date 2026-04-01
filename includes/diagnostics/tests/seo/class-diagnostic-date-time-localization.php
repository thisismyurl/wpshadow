<?php
/**
 * Date and Time Localization Diagnostic
 *
 * Tests if dates and times are properly localized for international audiences.
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
 * Date and Time Localization Diagnostic Class
 *
 * Validates that dates, times, and numbers are properly formatted
 * according to locale conventions.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Date_Time_Localization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-time-localization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Date and Time Localization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if dates and times are properly localized for international audiences';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests date/time localization including WordPress date formats,
	 * timezone settings, and proper use of localization functions.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Get WordPress date/time settings.
		$date_format = get_option( 'date_format', 'F j, Y' );
		$time_format = get_option( 'time_format', 'g:i a' );
		$timezone_string = get_option( 'timezone_string', '' );
		$gmt_offset = get_option( 'gmt_offset', 0 );
		$start_of_week = get_option( 'start_of_week', 0 );

		// Check if timezone is properly set.
		$timezone_properly_set = ! empty( $timezone_string );

		// Check theme files for hardcoded date formats.
		$theme_dir = get_template_directory();
		$theme_files = array( 'index.php', 'archive.php', 'single.php', 'functions.php' );
		$hardcoded_dates = 0;
		$uses_date_i18n = false;

		foreach ( $theme_files as $file ) {
			$filepath = $theme_dir . '/' . $file;
			if ( file_exists( $filepath ) ) {
				$content = file_get_contents( $filepath );

				// Check for hardcoded date() instead of date_i18n().
				preg_match_all( '/\bdate\s*\(\s*["\']/', $content, $matches );
				$hardcoded_dates += count( $matches[0] );

				// Check for proper date_i18n() usage.
				if ( strpos( $content, 'date_i18n(' ) !== false ) {
					$uses_date_i18n = true;
				}
			}
		}

		// Check for hardcoded month/day names.
		$hardcoded_names = false;
		foreach ( $theme_files as $file ) {
			$filepath = $theme_dir . '/' . $file;
			if ( file_exists( $filepath ) ) {
				$content = file_get_contents( $filepath );
				$month_names = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
				foreach ( $month_names as $month ) {
					if ( strpos( $content, $month ) !== false ) {
						$hardcoded_names = true;
						break 2;
					}
				}
			}
		}

		// Check recent posts for date display.
		global $wpdb;
		$recent_posts = $wpdb->get_results(
			"SELECT post_content, post_date FROM {$wpdb->posts}
			 WHERE post_type = 'post' AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT 5",
			ARRAY_A
		);

		// Check if using default US date format.
		$uses_us_format = ( $date_format === 'F j, Y' );

		// Check for number localization.
		$uses_number_format_i18n = false;
		$functions_file = $theme_dir . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			$uses_number_format_i18n = ( strpos( $functions_content, 'number_format_i18n(' ) !== false );
		}

		// Check for currency formatting.
		$ecommerce_active = is_plugin_active( 'woocommerce/woocommerce.php' );
		$uses_currency_locale = false;

		if ( $ecommerce_active && function_exists( 'wc_get_price_decimals' ) ) {
			$decimal_separator = wc_get_price_decimal_separator();
			$thousand_separator = wc_get_price_thousand_separator();
			// Check if using locale-appropriate separators.
			$uses_currency_locale = ( $decimal_separator !== '.' || $thousand_separator !== ',' );
		}

		// Check for relative time display.
		$uses_relative_time = false;
		foreach ( $theme_files as $file ) {
			$filepath = $theme_dir . '/' . $file;
			if ( file_exists( $filepath ) ) {
				$content = file_get_contents( $filepath );
				if ( strpos( $content, 'human_time_diff(' ) !== false ) {
					$uses_relative_time = true;
					break;
				}
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: Timezone not properly configured.
		if ( ! $timezone_properly_set ) {
			$issues[] = array(
				'type'        => 'timezone_not_set',
				'description' => __( 'Timezone not configured; using GMT offset instead of named timezone', 'wpshadow' ),
			);
		}

		// Issue 2: Hardcoded date() instead of date_i18n().
		if ( $hardcoded_dates > 3 ) {
			$issues[] = array(
				'type'        => 'hardcoded_dates',
				'description' => sprintf(
					/* translators: %d: number of hardcoded dates */
					__( '%d instances of date() found; should use date_i18n() for localization', 'wpshadow' ),
					$hardcoded_dates
				),
			);
		}

		// Issue 3: Hardcoded month/day names.
		if ( $hardcoded_names ) {
			$issues[] = array(
				'type'        => 'hardcoded_date_names',
				'description' => __( 'Hardcoded month/day names detected; not translatable to other languages', 'wpshadow' ),
			);
		}

		// Issue 4: Using default US date format.
		if ( $uses_us_format && get_locale() !== 'en_US' ) {
			$issues[] = array(
				'type'        => 'us_date_format',
				'description' => __( 'Using US date format (F j, Y) for non-US locale; may confuse international users', 'wpshadow' ),
			);
		}

		// Issue 5: Not using number_format_i18n().
		if ( ! $uses_number_format_i18n ) {
			$issues[] = array(
				'type'        => 'no_number_localization',
				'description' => __( 'Numbers not localized; decimal/thousand separators not adapted to locale', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Dates, times, and numbers are not properly localized, causing confusion for international users', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/date-time-localization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'date_format'             => $date_format,
					'time_format'             => $time_format,
					'timezone_string'         => $timezone_string,
					'gmt_offset'              => $gmt_offset,
					'start_of_week'           => $start_of_week,
					'timezone_properly_set'   => $timezone_properly_set,
					'hardcoded_dates'         => $hardcoded_dates,
					'uses_date_i18n'          => $uses_date_i18n,
					'hardcoded_names'         => $hardcoded_names,
					'uses_us_format'          => $uses_us_format,
					'uses_number_format_i18n' => $uses_number_format_i18n,
					'uses_relative_time'      => $uses_relative_time,
					'ecommerce_active'        => $ecommerce_active,
					'uses_currency_locale'    => $uses_currency_locale,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Use date_i18n(), set proper timezone, localize numbers, adapt date format to locale', 'wpshadow' ),
					'correct_functions'       => array(
						'date_i18n( $format, $timestamp )' => 'Localized date/time',
						'number_format_i18n( $number, $decimals )' => 'Localized numbers',
						'human_time_diff( $from, $to )' => 'Relative time (e.g., "2 hours ago")',
						'get_option( "date_format" )'  => 'User\'s preferred format',
						'current_time( "timestamp" )'  => 'Site timezone timestamp',
					),
					'date_format_examples'    => array(
						'US'        => 'F j, Y (January 5, 2026)',
						'Europe'    => 'd/m/Y (05/01/2026)',
						'ISO'       => 'Y-m-d (2026-01-05)',
						'Japan'     => 'Y年m月d日 (2026年01月05日)',
					),
					'timezone_importance'     => 'Named timezones handle DST automatically; GMT offsets do not',
					'number_format_examples'  => array(
						'US/UK'     => '1,234.56 (comma thousand, period decimal)',
						'Europe'    => '1.234,56 (period thousand, comma decimal)',
						'Switzerland' => '1\'234.56 (apostrophe thousand)',
					),
				),
			);
		}

		return null;
	}
}
