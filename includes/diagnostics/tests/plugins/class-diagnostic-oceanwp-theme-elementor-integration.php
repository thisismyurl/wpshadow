<?php
/**
 * Oceanwp Theme Elementor Integration Diagnostic
 *
 * Oceanwp Theme Elementor Integration needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1294.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oceanwp Theme Elementor Integration Diagnostic Class
 *
 * @since 1.1294.0000
 */
class Diagnostic_OceanwpThemeElementorIntegration extends Diagnostic_Base {

	protected static $slug = 'oceanwp-theme-elementor-integration';
	protected static $title = 'Oceanwp Theme Elementor Integration';
	protected static $description = 'Oceanwp Theme Elementor Integration needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for OceanWP theme and Elementor
		$has_oceanwp = get_template() === 'oceanwp' || class_exists( 'OCEANWP_Theme_Class' );
		$has_elementor = defined( 'ELEMENTOR_VERSION' ) || class_exists( '\Elementor\Plugin' );
		
		if ( ! $has_oceanwp || ! $has_elementor ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Compatibility mode
		$compat_mode = get_option( 'oceanwp_elementor_compatibility', 'yes' );
		if ( 'no' === $compat_mode ) {
			$issues[] = __( 'Compatibility mode disabled (style conflicts)', 'wpshadow' );
		}
		
		// Check 2: Widget conflicts
		$registered_widgets = get_option( 'elementor_widget_blacklist', array() );
		if ( empty( $registered_widgets ) ) {
			$issues[] = __( 'No widget blacklist (duplicate widgets)', 'wpshadow' );
		}
		
		// Check 3: Template caching
		$cache_templates = get_option( 'oceanwp_elementor_cache_templates', 'yes' );
		if ( 'no' === $cache_templates ) {
			$issues[] = __( 'Template caching disabled (slow page builds)', 'wpshadow' );
		}
		
		// Check 4: Custom CSS loading
		$inline_css = get_option( 'oceanwp_elementor_inline_css', 'no' );
		if ( 'yes' === $inline_css ) {
			$issues[] = __( 'Inline CSS (no browser caching)', 'wpshadow' );
		}
		
		// Check 5: Responsive settings sync
		$sync_responsive = get_option( 'oceanwp_elementor_sync_responsive', 'yes' );
		if ( 'no' === $sync_responsive ) {
			$issues[] = __( 'Responsive settings not synced (mobile issues)', 'wpshadow' );
		}
		
		// Check 6: Font awesome loading
		$fa_loading = get_option( 'oceanwp_disable_elementor_fontawesome', 'no' );
		if ( 'no' === $fa_loading ) {
			$issues[] = __( 'Duplicate Font Awesome loading (extra requests)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Elementor integration issues */
				__( 'OceanWP Elementor integration has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/oceanwp-theme-elementor-integration',
		);
	}
}
