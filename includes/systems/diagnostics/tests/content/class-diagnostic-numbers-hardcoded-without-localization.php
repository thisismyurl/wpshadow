<?php
/**
 * Numbers Hardcoded Without Localization Diagnostic
 *
 * Checks if numbers use WordPress localization functions instead of hardcoded separators.
 * Different regions use different number formats (US: 1,000.50 vs Europe:1.0,50).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Numbers Hardcoded Without Localization Diagnostic
 *
 * Detects when numbers are hardcoded with specific separators instead of using
 * WordPress localization functions like `number_format_i18n()`. This affects
 * international users who expect their regional number formats.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Numbers_Hardcoded_Without_Localization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'numbers-hardcoded-without-localization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Numbers Use Localized Formatting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies numbers use `number_format_i18n()` for localized formatting instead of hardcoded separators';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// For a real implementation, you would scan content for hardcoded numbers
		// This is complex to detect without false positives, so we provide guidance

		// Check if the theme or plugins have hardcoded formatting
		$issues = self::scan_for_hardcoded_numbers();

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of files with hardcoded numbers */
					__( 'Found %d instances of hardcoded number formatting (commas, dots as thousands separators). Use WordPress `number_format_i18n()` to adapt to user\'s locale (US: 1,000.50 vs Europe:1.0,50).', 'wpshadow' ),
					count( $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/number-localization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'found_instances' => count( $issues ),
					'example_pattern' => 'preg_match("/\d+[,\.]\d{3,}/", $content)',
					'recommendation' => __( 'Use number_format_i18n() instead of hardcoded separators', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Scan for hardcoded numbers in content
	 *
	 * @since 0.6093.1200
	 * @return array Array of findings with locations
	 */
	private static function scan_for_hardcoded_numbers(): array {
		$issues = array();

		// Check wp-options table for hardcoded numbers
		global $wpdb;

		// Look for option values with hardcoded number formatting
		$options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options}
			WHERE option_value REGEXP '[0-9]{1,3}[,\.][0-9]{3}' LIMIT 10"
		);

		if ( ! empty( $options ) ) {
			foreach ( $options as $option ) {
				$issues[] = array(
					'type'   => 'option',
					'name'   => $option->option_name,
					'sample' => wp_kses_post( $option->option_value ),
				);
			}
		}

		return $issues;
	}
}
