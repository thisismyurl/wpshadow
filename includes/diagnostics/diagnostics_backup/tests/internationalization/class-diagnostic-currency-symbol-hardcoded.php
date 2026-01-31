<?php
/**
 * Currency Symbol Hardcoded Diagnostic
 *
 * Detects hardcoded USD currency symbol ($) which confuses non-US customers
 * and breaks multi-currency sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1735
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Currency_Symbol_Hardcoded Class
 *
 * Scans theme and WooCommerce templates for hardcoded $ symbols
 * in price displays instead of proper currency functions.
 *
 * @since 1.6028.1735
 */
class Diagnostic_Currency_Symbol_Hardcoded extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'currency-symbol-hardcoded';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Currency Symbol Hardcoded as $';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hardcoded USD currency symbol breaking multi-currency sites';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1735
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::scan_for_hardcoded_currency();

		if ( $analysis['hardcoded_count'] === 0 ) {
			return null; // No hardcoded currency symbols found.
		}

		// Determine severity based on count and context.
		if ( $analysis['hardcoded_count'] > 10 || $analysis['has_woocommerce'] ) {
			$severity     = 'low';
			$threat_level = 35;
		} else {
			$severity     = 'info';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of hardcoded currency symbols */
				__( 'Found %d hardcoded $ currency symbols, breaking multi-currency support', 'wpshadow' ),
				$analysis['hardcoded_count']
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/currency-symbols',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'  => $analysis['hardcoded_count'],
				'files_affected'  => count( $analysis['files'] ),
				'has_woocommerce' => $analysis['has_woocommerce'],
				'recommended'     => __( 'Use currency functions or settings', 'wpshadow' ),
				'impact_level'    => 'low',
				'immediate_actions' => array(
					__( 'Replace hardcoded $ with get_woocommerce_currency_symbol()', 'wpshadow' ),
					__( 'Use number_format_i18n() for prices', 'wpshadow' ),
					__( 'Configure currency in WooCommerce settings', 'wpshadow' ),
					__( 'Test multi-currency plugins', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Hardcoded currency symbols confuse international customers, break multi-currency plugins, and look unprofessional. Users expect to see their local currency (€, £, ¥, etc.). E-commerce sites lose trust when prices show $ for non-US customers. Currency should come from settings, not hardcoded strings.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Customer Confusion: Non-US customers see USD when they expect local currency', 'wpshadow' ),
					__( 'Multi-Currency Broken: Currency switcher plugins can\'t override hardcoded $', 'wpshadow' ),
					__( 'Lost Trust: Appears unprofessional for international market', 'wpshadow' ),
					__( 'Conversion Loss: Users uncertain about actual currency', 'wpshadow' ),
				),
				'currency_analysis' => array(
					'hardcoded_count'  => $analysis['hardcoded_count'],
					'files_affected'   => count( $analysis['files'] ),
					'has_woocommerce'  => $analysis['has_woocommerce'],
					'examples'         => $analysis['examples'],
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'WooCommerce Currency Functions', 'wpshadow' ),
						'description' => __( 'Use WooCommerce built-in currency handling', 'wpshadow' ),
						'steps'       => array(
							__( 'Replace "$" with get_woocommerce_currency_symbol()', 'wpshadow' ),
							__( 'Use wc_price($amount) for formatted prices', 'wpshadow' ),
							__( 'Configure currency in WooCommerce → Settings → General', 'wpshadow' ),
							__( 'Test with different currency settings', 'wpshadow' ),
							__( 'Verify checkout displays correctly', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Multi-Currency Plugin', 'wpshadow' ),
						'description' => __( 'Add automatic currency conversion with WooCommerce Multi-Currency', 'wpshadow' ),
						'steps'       => array(
							__( 'Install WooCommerce Multi-Currency or Currency Switcher', 'wpshadow' ),
							__( 'Configure supported currencies', 'wpshadow' ),
							__( 'Set up exchange rate updates', 'wpshadow' ),
							__( 'Add currency switcher to header', 'wpshadow' ),
							__( 'Test prices update when currency changes', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Custom Currency System', 'wpshadow' ),
						'description' => __( 'Build flexible currency handling for custom pricing', 'wpshadow' ),
						'steps'       => array(
							__( 'Create currency settings in plugin/theme options', 'wpshadow' ),
							__( 'Store currency symbol in database', 'wpshadow' ),
							__( 'Create helper function: get_site_currency_symbol()', 'wpshadow' ),
							__( 'Use filter: apply_filters(\'site_currency_symbol\', $symbol)', 'wpshadow' ),
							__( 'Replace all hardcoded $ with function calls', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Always use currency functions, never hardcode symbols', 'wpshadow' ),
					__( 'WooCommerce: Use wc_price() for formatted output', 'wpshadow' ),
					__( 'Support currency position (before/after price)', 'wpshadow' ),
					__( 'Respect thousand/decimal separators by locale', 'wpshadow' ),
					__( 'Test with €, £, ¥, and other common currencies', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Change WooCommerce currency to EUR', 'wpshadow' ),
						__( 'View products and cart - verify € appears', 'wpshadow' ),
						__( 'Search codebase for remaining "$" + number patterns', 'wpshadow' ),
						__( 'Test with multi-currency plugin if available', 'wpshadow' ),
					),
					'expected_result' => __( 'All prices display configured currency symbol', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Scan theme and plugin files for hardcoded currency symbols.
	 *
	 * @since  1.6028.1735
	 * @return array Analysis results with counts and examples.
	 */
	private static function scan_for_hardcoded_currency() {
		$result = array(
			'hardcoded_count'  => 0,
			'files'            => array(),
			'examples'         => array(),
			'has_woocommerce'  => class_exists( 'WooCommerce' ),
		);

		// Get active theme directory.
		$theme_dir = get_stylesheet_directory();
		$files     = self::get_template_files( $theme_dir );

		// Pattern to find hardcoded $ near numbers (price context).
		// Matches: "$10", "$10.99", "Price: $", etc.
		$pattern = '/[\'">\s]\$\s*\d|Price[:\s]*\$|Total[:\s]*\$/i';

		$example_limit = 10;

		foreach ( $files as $file ) {
			$content = @file_get_contents( $file );
			if ( $content === false ) {
				continue;
			}

			// Skip if file uses proper currency functions.
			if ( strpos( $content, 'get_woocommerce_currency_symbol' ) !== false ||
			     strpos( $content, 'wc_price' ) !== false ||
			     strpos( $content, 'currency_symbol' ) !== false ) {
				continue;
			}

			preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE );

			if ( ! empty( $matches[0] ) ) {
				$result['hardcoded_count'] += count( $matches[0] );
				$result['files'][]          = str_replace( ABSPATH, '', $file );

				if ( count( $result['examples'] ) < $example_limit ) {
					foreach ( $matches[0] as $match ) {
						if ( count( $result['examples'] ) >= $example_limit ) {
							break;
						}

						$line_num = substr_count( substr( $content, 0, $match[1] ), "\n" ) + 1;
						$result['examples'][] = array(
							'file'    => str_replace( ABSPATH, '', $file ),
							'line'    => $line_num,
							'context' => trim( $match[0] ),
						);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get template files to scan for currency symbols.
	 *
	 * @since  1.6028.1735
	 * @param  string $dir Directory path.
	 * @return array Array of file paths.
	 */
	private static function get_template_files( $dir ) {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$ext = $file->getExtension();
				// Check PHP and template files.
				if ( in_array( $ext, array( 'php', 'html', 'tpl' ), true ) ) {
					// Skip vendor directories.
					if ( strpos( $file->getPathname(), '/vendor/' ) !== false ) {
						continue;
					}
					$files[] = $file->getPathname();
				}
			}
		}

		return array_slice( $files, 0, 100 ); // Limit for performance.
	}
}
