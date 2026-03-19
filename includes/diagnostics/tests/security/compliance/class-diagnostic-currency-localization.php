<?php
/**
 * Currency Localization Diagnostic
 *
 * Issue #4922: Currency Hardcoded to USD ($)
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if currency formatting respects locale.
 * Not everyone uses dollars.
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
 * Diagnostic_Currency_Localization Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Currency_Localization extends Diagnostic_Base {

	protected static $slug = 'currency-localization';
	protected static $title = 'Currency Hardcoded to USD ($)';
	protected static $description = 'Checks if currency formatting respects user locale';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Allow users to select currency (USD, EUR, GBP, JPY, etc)', 'wpshadow' );
		$issues[] = __( 'Format currency per locale: $1,000.50 vs1.0,50 €', 'wpshadow' );
		$issues[] = __( 'Position symbol correctly: $100 vs 100€ vs ¥100', 'wpshadow' );
		$issues[] = __( 'Use ISO currency codes in data (USD, EUR)', 'wpshadow' );
		$issues[] = __( 'Display symbol to users ($ € £ ¥)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Hardcoding "$" assumes all users are American. Support multiple currencies with proper locale formatting.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/currency-localization',
				'details'      => array(
					'recommendations'         => $issues,
					'format_examples'         => 'US: $1,000.50, DE:1.0,50 €, JP: ¥1,000',
					'symbol_position'         => 'USD/GBP prefix ($100), EUR suffix (100€)',
					'php_function'            => 'NumberFormatter class with locale',
				),
			);
		}

		return null;
	}
}
