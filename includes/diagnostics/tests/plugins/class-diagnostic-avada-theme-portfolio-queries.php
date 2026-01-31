<?php
/**
 * Avada Theme Portfolio Queries Diagnostic
 *
 * Avada Theme Portfolio Queries needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1308.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Avada Theme Portfolio Queries Diagnostic Class
 *
 * @since 1.1308.0000
 */
class Diagnostic_AvadaThemePortfolioQueries extends Diagnostic_Base {

	protected static $slug = 'avada-theme-portfolio-queries';
	protected static $title = 'Avada Theme Portfolio Queries';
	protected static $description = 'Avada Theme Portfolio Queries needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'avada_portfolio_shortcode' ) && ! function_exists( 'avada_get_portfolio' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Portfolio lazy loading.
		$portfolio_lazy = get_option( 'avada_portfolio_lazy_load', '0' );
		if ( '0' === $portfolio_lazy ) {
			$issues[] = 'portfolio lazy loading disabled';
		}

		// Check 2: Portfolio pagination.
		$portfolio_per_page = get_option( 'avada_portfolio_per_page', -1 );
		if ( -1 === $portfolio_per_page ) {
			$issues[] = 'pagination disabled (all items load at once)';
		}

		// Check 3: Image compression.
		$image_compress = get_option( 'avada_image_compression', '0' );
		if ( '0' === $image_compress ) {
			$issues[] = 'image compression disabled';
		}

		// Check 4: Caching.
		$cache_enabled = get_option( 'avada_enable_caching', '1' );
		if ( '0' === $cache_enabled ) {
			$issues[] = 'caching disabled';
		}

		// Check 5: Debug mode.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'debug mode enabled';
		}

		// Check 6: Portfolio size.
		$portfolio_count = get_option( 'avada_portfolio_count', 0 );
		if ( $portfolio_count > 500 ) {
			$issues[] = "heavy portfolio ({$portfolio_count} items)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Avada portfolio issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/avada-theme-portfolio-queries',
			);
		}

		return null;
	}
}
