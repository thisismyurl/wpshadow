<?php
/**
 * Salient Theme Portfolio Performance Diagnostic
 *
 * Salient Theme Portfolio Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1325.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Salient Theme Portfolio Performance Diagnostic Class
 *
 * @since 1.1325.0000
 */
class Diagnostic_SalientThemePortfolioPerformance extends Diagnostic_Base {

	protected static $slug = 'salient-theme-portfolio-performance';
	protected static $title = 'Salient Theme Portfolio Performance';
	protected static $description = 'Salient Theme Portfolio Performance needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Salient theme
		$theme = wp_get_theme();
		if ( 'Salient' !== $theme->get( 'Name' ) && 'Salient' !== $theme->get_template() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Portfolio item count
		$portfolio_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'portfolio',
				'publish'
			)
		);
		
		if ( $portfolio_count === 0 ) {
			return null;
		}
		
		if ( $portfolio_count > 100 ) {
			$issues[] = sprintf( __( '%d portfolio items (grid loading slow)', 'wpshadow' ), $portfolio_count );
		}
		
		// Check 2: Lazy loading
		$lazy_load = get_option( 'salient_portfolio_lazy_load', 'off' );
		if ( 'off' === $lazy_load && $portfolio_count > 20 ) {
			$issues[] = __( 'Portfolio lazy loading disabled (loads all images)', 'wpshadow' );
		}
		
		// Check 3: Thumbnail regeneration
		$thumb_sizes = get_option( 'salient_portfolio_thumbnail_sizes', array() );
		if ( count( $thumb_sizes ) > 5 ) {
			$issues[] = sprintf( __( '%d thumbnail sizes (storage overhead)', 'wpshadow' ), count( $thumb_sizes ) );
		}
		
		// Check 4: Animation effects
		$animations = get_option( 'salient_portfolio_animations', 'on' );
		if ( 'on' === $animations ) {
			$issues[] = __( 'Portfolio animations enabled (CPU intensive)', 'wpshadow' );
		}
		
		// Check 5: Isotope filtering
		$isotope = get_option( 'salient_portfolio_isotope', 'on' );
		if ( 'on' === $isotope && $portfolio_count > 50 ) {
			$issues[] = __( 'Isotope filtering on large portfolio (DOM manipulation lag)', 'wpshadow' );
		}
		
		// Check 6: AJAX pagination
		$ajax_pagination = get_option( 'salient_portfolio_ajax_pagination', 'off' );
		if ( 'off' === $ajax_pagination && $portfolio_count > 30 ) {
			$issues[] = __( 'AJAX pagination disabled (full page reloads)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of portfolio performance issues */
				__( 'Salient portfolio has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/salient-theme-portfolio-performance',
		);
	}
}
