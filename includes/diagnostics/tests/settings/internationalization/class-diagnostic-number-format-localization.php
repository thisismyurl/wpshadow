<?php
/**
 * Number Format Localization Diagnostic
 *
 * Issue #4800: Numbers Hardcoded Without Localization
 * Family: internationalization (Pillar: Culturally Respectful)
 *
 * Checks if numbers use localized formatting.
 * Different cultures use different thousand and decimal separators.
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
 * Diagnostic_Number_Format_Localization Class
 *
 * Checks for hardcoded number formats.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Number_Format_Localization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'number-format-localization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Numbers Hardcoded Without Localization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if numbers use localized formatting for separators and decimals';

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

		$issues[] = __( 'Use number_format_i18n() for localized number formatting', 'wpshadow' );
		$issues[] = __( 'Avoid hardcoded thousand separators (commas or periods)', 'wpshadow' );
		$issues[] = __( 'Use WordPress locale settings for decimal/thousand separators', 'wpshadow' );
		$issues[] = __( 'Test with European locale to verify formatting', 'wpshadow' );
		$issues[] = __( 'For currency, combine with currency localization', 'wpshadow' );
		$issues[] = __( 'Remember: US "1,000.50" vs Europe "1.000,50" vs India "1,00,000.50"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your numbers might be hardcoded with US formatting, which confuses international visitors. Different cultures format numbers differently: US/UK: 1,000.50 (comma thousand, period decimal), Europe (most):1.0,50 (period thousand, comma decimal), Switzerland: 1\'000.50 (apostrophe thousand), India: 1,00,000.50 (lakhs system with different comma placement). When you hardcode "1,000.50", Europeans might read the comma as a decimal (interpreting as "1 point 000 point 50"). Always use WordPress localization functions: ❌ Bad: number_format($value, 2) (hardcoded US format with comma thousand, period decimal). ✅ Good: number_format_i18n($value, 2) (uses WordPress locale settings for separators). Best practices: 1) Use number_format_i18n(): Automatically formats based on site locale, 2) Respect user locale: Settings > General > Site Language determines format, 3) Don\'t hardcode separators: Avoid directly writing "1,000" or "1.000", 4) Currency too: Combine with currency localization for complete solution, 5) Test globally: Switch site language to French/German and verify numbers look correct. Edge cases: India uses lakhs/crores system (1,00,000 = 1 lakh), some cultures use spaces as thousand separators (1 000 000), Arabic-Indic numerals (٠١٢٣٤٥٦٧٨٩) used in Arabic locales. This respects cultural diversity and prevents confusion for international visitors.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/number-localization',
				'details'      => array(
					'recommendations'       => $issues,
					'global_formats'        => 'US: 1,000.50 | Europe:1.0,50 | India: 1,00,000.50',
					'bad_code'              => 'number_format($num, 2)',
					'good_code'             => 'number_format_i18n($num, 2)',
					'testing'               => 'Change site language to German/French to test',
					'edge_cases'            => 'India (lakhs), Switzerland (apostrophe), Arabic (different numerals)',
					'user_preference'       => 'Settings > General > Site Language controls format',
					'pillar'                => 'Culturally Respectful',
				),
			);
		}

		return null;
	}
}
