<?php
/**
 * Kalium Theme Page Builder Integration Diagnostic
 *
 * Kalium Theme Page Builder Integration needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1337.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kalium Theme Page Builder Integration Diagnostic Class
 *
 * @since 1.1337.0000
 */
class Diagnostic_KaliumThemePageBuilderIntegration extends Diagnostic_Base {

	protected static $slug = 'kalium-theme-page-builder-integration';
	protected static $title = 'Kalium Theme Page Builder Integration';
	protected static $description = 'Kalium Theme Page Builder Integration needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$theme = wp_get_theme();
		if ( 'Kalium' !== $theme->get( 'Name' ) && 'Kalium' !== $theme->get_template() ) {
			return null;
		}

		$issues = array();

		// Check 1: Page builder compatibility
		$has_elementor = defined( 'ELEMENTOR_VERSION' );
		$has_wpbakery = defined( 'WPB_VC_VERSION' );
		$builder_support = get_option( 'kalium_page_builder_support', 'auto' );

		if ( ( $has_elementor || $has_wpbakery ) && 'auto' === $builder_support ) {
			$issues[] = __( 'Auto builder detection (conflicts possible)', 'wpshadow' );
		}

		// Check 2: CSS conflicts
		$css_conflicts = get_option( 'kalium_css_conflicts', 'no' );
		if ( 'yes' === $css_conflicts ) {
			$issues[] = __( 'CSS conflicts detected (layout issues)', 'wpshadow' );
		}

		// Check 3: JavaScript conflicts
		$js_conflicts = get_option( 'kalium_js_conflicts', 'no' );
		if ( 'yes' === $js_conflicts ) {
			$issues[] = __( 'JavaScript conflicts (broken features)', 'wpshadow' );
		}

		// Check 4: Portfolio pages
		$portfolio_builder = get_option( 'kalium_portfolio_builder', 'theme' );
		if ( 'theme' === $portfolio_builder && $has_elementor ) {
			$issues[] = __( 'Theme portfolio with Elementor (compatibility)', 'wpshadow' );
		}

		// Check 5: Custom fonts
		$custom_fonts = get_option( 'kalium_custom_fonts', 'no' );
		if ( 'yes' === $custom_fonts && $has_elementor ) {
			$issues[] = __( 'Duplicate font loading (theme + builder)', 'wpshadow' );
		}

		// Check 6: Template overrides
		$overrides = get_option( 'kalium_template_overrides', array() );
		if ( ! empty( $overrides ) ) {
			$issues[] = sprintf( __( '%d template overrides (update issues)', 'wpshadow' ), count( $overrides ) );
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
				__( 'Kalium theme has %d page builder integration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/kalium-theme-page-builder-integration',
		);
	}
}
