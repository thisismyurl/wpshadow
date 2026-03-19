<?php
/**
 * Number Formatting Localization Diagnostic
 *
 * Issue #4923: Numbers Not Locale-Formatted
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if numbers respect locale formatting.
 * 1,000.50 vs1.0,50 confuses international users.
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
 * Diagnostic_Number_Formatting_Localization Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Number_Formatting_Localization extends Diagnostic_Base {

	protected static $slug = 'number-formatting-localization';
	protected static $title = 'Numbers Not Locale-Formatted';
	protected static $description = 'Checks if numbers use locale-appropriate formatting';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use number_format_i18n() for WordPress locale', 'wpshadow' );
		$issues[] = __( 'Respect decimal separator: . (US) vs , (EU)', 'wpshadow' );
		$issues[] = __( 'Respect thousands separator: , (US) vs . (EU) vs space (FR)', 'wpshadow' );
		$issues[] = __( 'Format large numbers: 1,000,000 vs1.0', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Number formatting varies globally. "1,000.50" is clear to Americans but means1.0 to Europeans. Use locale-aware formatting.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/number-formatting',
				'details'      => array(
					'recommendations'         => $issues,
					'us_format'               => '1,000.50 (comma thousands, period decimal)',
					'eu_format'               => '1.000,50 (period thousands, comma decimal)',
					'wp_function'             => 'number_format_i18n( $number, $decimals )',
				),
			);
		}

		return null;
	}
}
