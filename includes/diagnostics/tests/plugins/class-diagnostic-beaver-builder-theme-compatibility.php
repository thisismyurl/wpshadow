<?php
/**
 * Beaver Builder Theme Compatibility Diagnostic
 *
 * Beaver Builder theme conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.346.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Theme Compatibility Diagnostic Class
 *
 * @since 1.346.0000
 */
class Diagnostic_BeaverBuilderThemeCompatibility extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-theme-compatibility';
	protected static $title = 'Beaver Builder Theme Compatibility';
	protected static $description = 'Beaver Builder theme conflicts';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();
		$theme = wp_get_theme();

		// Check 1: Theme compatibility mode
		$compat_mode = get_option( 'fl_builder_theme_compat', 'auto' );
		if ( 'auto' === $compat_mode ) {
			$issues[] = __( 'Auto theme detection (conflicts possible)', 'wpshadow' );
		}

		// Check 2: CSS conflicts
		$css_conflicts = get_option( 'fl_builder_css_conflicts', array() );
		if ( ! empty( $css_conflicts ) ) {
			$issues[] = sprintf( __( '%d CSS conflicts detected', 'wpshadow' ), count( $css_conflicts ) );
		}

		// Check 3: JavaScript conflicts
		$js_conflicts = get_option( 'fl_builder_js_conflicts', 'no' );
		if ( 'yes' === $js_conflicts ) {
			$issues[] = __( 'JavaScript conflicts (broken features)', 'wpshadow' );
		}

		// Check 4: Header/footer override
		$override_header = get_option( 'fl_builder_override_header', 'no' );
		if ( 'no' === $override_header && $theme->get( 'Name' ) !== 'Beaver Builder Theme' ) {
			$issues[] = __( 'Theme header used (layout issues)', 'wpshadow' );
		}

		// Check 5: Content width
		$content_width = get_option( 'fl_builder_content_width', 0 );
		if ( $content_width === 0 ) {
			$issues[] = __( 'No content width set (responsive issues)', 'wpshadow' );
		}

		// Check 6: Template compatibility
		$template_compat = get_option( 'fl_builder_template_compat', 'enabled' );
		if ( 'disabled' === $template_compat ) {
			$issues[] = __( 'Template compatibility disabled (broken layouts)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 40;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 52;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 46;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Beaver Builder has %d theme compatibility issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-theme-compatibility',
		);
	}
}
