<?php
/**
 * Kalium Theme Creative Portfolio Diagnostic
 *
 * Kalium Theme Creative Portfolio needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1336.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kalium Theme Creative Portfolio Diagnostic Class
 *
 * @since 1.1336.0000
 */
class Diagnostic_KaliumThemeCreativePortfolio extends Diagnostic_Base {

	protected static $slug = 'kalium-theme-creative-portfolio';
	protected static $title = 'Kalium Theme Creative Portfolio';
	protected static $description = 'Kalium Theme Creative Portfolio needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Portfolio grid optimization
		$grid = get_option( 'kalium_portfolio_grid_optimized', 0 );
		if ( ! $grid ) {
			$issues[] = 'Portfolio grid not optimized';
		}

		// Check 2: Image lazy loading
		$lazy = get_option( 'kalium_portfolio_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Portfolio image lazy loading not enabled';
		}

		// Check 3: Lightbox optimization
		$lightbox = get_option( 'kalium_portfolio_lightbox_optimized', 0 );
		if ( ! $lightbox ) {
			$issues[] = 'Portfolio lightbox not optimized';
		}

		// Check 4: Filtering performance
		$filter = get_option( 'kalium_portfolio_filter_performance_optimized', 0 );
		if ( ! $filter ) {
			$issues[] = 'Portfolio filtering performance not optimized';
		}

		// Check 5: Pagination
		$pagination = get_option( 'kalium_portfolio_pagination_configured', 0 );
		if ( ! $pagination ) {
			$issues[] = 'Portfolio pagination not properly configured';
		}

		// Check 6: Cache settings
		$cache = get_option( 'kalium_portfolio_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Portfolio caching not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d portfolio issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/kalium-theme-creative-portfolio',
			);
		}

		return null;
	}
}
