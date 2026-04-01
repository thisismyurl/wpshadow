<?php
/**
 * Geographic Expansion Diagnostic
 *
 * Checks whether geo-expansion capabilities are in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geographic Expansion Diagnostic Class
 *
 * Verifies multi-currency, localization, and international readiness.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Geographic_Expansion extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'geographic-expansion';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Geographic Expansion Strategy';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for localization, multi-currency, and regional support';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'growth-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for multi-currency or multi-language plugins (50 points).
		$expansion_plugins = array(
			'woocommerce/woocommerce.php'                      => 'WooCommerce',
			'woocommerce-multilingual/wpml-woocommerce.php'     => 'WooCommerce Multilingual',
			'polylang/polylang.php'                             => 'Polylang',
			'sitepress-multilingual-cms/sitepress.php'          => 'WPML',
			'currency-switcher-woocommerce/woocommerce-currency-switcher.php' => 'Currency Switcher',
		);

		$active_expansion = array();
		foreach ( $expansion_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_expansion[] = $plugin_name;
				$earned_points     += 15;
			}
		}

		if ( count( $active_expansion ) > 0 ) {
			$stats['expansion_tools'] = implode( ', ', $active_expansion );
		} else {
			$issues[] = __( 'No localization or multi-currency tools detected', 'wpshadow' );
		}

		// Check for international shipping (30 points).
		if ( class_exists( 'WooCommerce' ) ) {
			$earned_points += 15;
			$stats['woocommerce'] = 'enabled';
		} else {
			$warnings[] = __( 'No e-commerce platform detected for international checkout', 'wpshadow' );
		}

		// Check for location-based content (20 points).
		$location_pages = self::find_pages_by_keywords(
			array(
				'locations',
				'international',
				'global',
				'shipping',
				'regions',
			)
		);

		if ( count( $location_pages ) > 0 ) {
			$earned_points           += 20;
			$stats['location_pages'] = implode( ', ', $location_pages );
		} else {
			$warnings[] = __( 'No location-specific content detected', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your geographic expansion readiness scored %s. Expanding to new regions can multiply growth, but only if localization and regional support are in place. Without it, growth stays limited to one market.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/geographic-expansion?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
