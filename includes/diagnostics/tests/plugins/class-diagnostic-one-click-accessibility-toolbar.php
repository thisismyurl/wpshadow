<?php
/**
 * One Click Accessibility Toolbar Diagnostic
 *
 * One Click Accessibility Toolbar not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1094.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One Click Accessibility Toolbar Diagnostic Class
 *
 * @since 1.1094.0000
 */
class Diagnostic_OneClickAccessibilityToolbar extends Diagnostic_Base {

	protected static $slug = 'one-click-accessibility-toolbar';
	protected static $title = 'One Click Accessibility Toolbar';
	protected static $description = 'One Click Accessibility Toolbar not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for One Click Accessibility plugin
		if ( ! function_exists( 'pojo_a11y_load_plugin' ) && ! class_exists( 'Pojo_Accessibility' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Toolbar enabled
		$toolbar_enabled = get_option( 'pojo_a11y_toolbar_position', 'none' );
		if ( 'none' === $toolbar_enabled ) {
			return null;
		}
		
		// Check 2: WCAG compliance mode
		$wcag_mode = get_option( 'pojo_a11y_wcag_mode', 'aa' );
		if ( 'aa' !== $wcag_mode && 'aaa' !== $wcag_mode ) {
			$issues[] = __( 'WCAG compliance mode not properly configured', 'wpshadow' );
		}
		
		// Check 3: Skip links
		$skip_links = get_option( 'pojo_a11y_skip_links', false );
		if ( ! $skip_links ) {
			$issues[] = __( 'Skip navigation links not enabled', 'wpshadow' );
		}
		
		// Check 4: Keyboard navigation
		$keyboard_nav = get_option( 'pojo_a11y_enable_keyboard_navigation', false );
		if ( ! $keyboard_nav ) {
			$issues[] = __( 'Enhanced keyboard navigation not enabled', 'wpshadow' );
		}
		
		// Check 5: Contrast settings
		$high_contrast = get_option( 'pojo_a11y_contrast_mode', false );
		$contrast_options = get_option( 'pojo_a11y_contrast_options', array() );
		
		if ( $high_contrast && empty( $contrast_options ) ) {
			$issues[] = __( 'High contrast enabled but no presets configured', 'wpshadow' );
		}
		
		// Check 6: Screen reader optimization
		$screen_reader = get_option( 'pojo_a11y_screen_reader_text', false );
		if ( ! $screen_reader ) {
			$issues[] = __( 'Screen reader text enhancements disabled', 'wpshadow' );
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
				/* translators: %s: list of accessibility issues */
				__( 'One Click Accessibility toolbar has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/one-click-accessibility-toolbar',
		);
	}
}
