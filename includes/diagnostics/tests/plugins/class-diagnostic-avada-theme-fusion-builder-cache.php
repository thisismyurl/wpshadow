<?php
/**
 * Avada Theme Fusion Builder Cache Diagnostic
 *
 * Avada Theme Fusion Builder Cache needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1306.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Avada Theme Fusion Builder Cache Diagnostic Class
 *
 * @since 1.1306.0000
 */
class Diagnostic_AvadaThemeFusionBuilderCache extends Diagnostic_Base {

	protected static $slug = 'avada-theme-fusion-builder-cache';
	protected static $title = 'Avada Theme Fusion Builder Cache';
	protected static $description = 'Avada Theme Fusion Builder Cache needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Avada theme with Fusion Builder
		$has_avada = class_exists( 'Avada' ) ||
		             defined( 'AVADA_VERSION' ) ||
		             get_template() === 'Avada';
		
		if ( ! $has_avada ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Fusion cache enabled
		$cache_enabled = get_option( 'fusion_cache_enabled', 'yes' );
		if ( 'no' === $cache_enabled ) {
			$issues[] = __( 'Fusion cache disabled (slow page loads)', 'wpshadow' );
		}
		
		// Check 2: Dynamic CSS caching
		$css_cache = get_option( 'fusion_dynamic_css_cache', 'file' );
		if ( 'inline' === $css_cache ) {
			$issues[] = __( 'Inline dynamic CSS (no browser caching)', 'wpshadow' );
		}
		
		// Check 3: Cache invalidation
		$auto_invalidate = get_option( 'fusion_auto_invalidate_cache', 'yes' );
		if ( 'no' === $auto_invalidate ) {
			$issues[] = __( 'Manual cache clearing (stale content)', 'wpshadow' );
		}
		
		// Check 4: Builder mode check
		if ( isset( $_GET['fb-edit'] ) || get_option( 'fusion_builder_mode_active', 'no' ) === 'yes' ) {
			$issues[] = __( 'Builder mode active (caching bypassed)', 'wpshadow' );
		}
		
		// Check 5: Compiler optimization
		$compiler_optimization = get_option( 'fusion_compiler_optimization', 'yes' );
		if ( 'no' === $compiler_optimization ) {
			$issues[] = __( 'Compiler not optimized (slow CSS generation)', 'wpshadow' );
		}
		
		// Check 6: Cache regeneration frequency
		$last_regen = get_option( 'fusion_cache_last_regeneration', 0 );
		if ( $last_regen > 0 && ( time() - $last_regen ) < 3600 ) {
			$issues[] = __( 'Frequent cache regeneration (performance impact)', 'wpshadow' );
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
				/* translators: %s: list of Fusion Builder cache issues */
				__( 'Avada Fusion Builder has %d cache issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/avada-theme-fusion-builder-cache',
		);
	}
}
