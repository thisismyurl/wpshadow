<?php
/**
 * Userway Widget Customization Diagnostic
 *
 * Userway Widget Customization not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1102.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Userway Widget Customization Diagnostic Class
 *
 * @since 1.1102.0000
 */
class Diagnostic_UserwayWidgetCustomization extends Diagnostic_Base {

	protected static $slug = 'userway-widget-customization';
	protected static $title = 'Userway Widget Customization';
	protected static $description = 'Userway Widget Customization not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for UserWay accessibility widget
		$userway_account = get_option( 'userway_account_id', '' );
		if ( empty( $userway_account ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Widget position
		$position = get_option( 'userway_widget_position', 'bottom-right' );
		if ( 'middle-left' === $position || 'middle-right' === $position ) {
			$issues[] = __( 'Widget in middle position (content obstruction)', 'wpshadow' );
		}
		
		// Check 2: Color scheme
		$color_scheme = get_option( 'userway_color_scheme', 'auto' );
		if ( 'auto' !== $color_scheme ) {
			$issues[] = __( 'Fixed color scheme (may clash with theme)', 'wpshadow' );
		}
		
		// Check 3: Mobile visibility
		$hide_mobile = get_option( 'userway_hide_on_mobile', false );
		if ( $hide_mobile ) {
			$issues[] = __( 'Widget hidden on mobile (accessibility reduced)', 'wpshadow' );
		}
		
		// Check 4: Trigger size
		$trigger_size = get_option( 'userway_trigger_size', 'medium' );
		if ( 'small' === $trigger_size ) {
			$issues[] = __( 'Small trigger button (harder to click)', 'wpshadow' );
		}
		
		// Check 5: Keyboard shortcuts
		$shortcuts_enabled = get_option( 'userway_keyboard_shortcuts', true );
		if ( ! $shortcuts_enabled ) {
			$issues[] = __( 'Keyboard shortcuts disabled (reduced navigation)', 'wpshadow' );
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
				/* translators: %s: list of widget customization issues */
				__( 'UserWay widget has %d customization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/userway-widget-customization',
		);
	}
}
