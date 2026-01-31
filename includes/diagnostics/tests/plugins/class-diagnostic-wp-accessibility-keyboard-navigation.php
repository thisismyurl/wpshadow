<?php
/**
 * Wp Accessibility Keyboard Navigation Diagnostic
 *
 * Wp Accessibility Keyboard Navigation not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1091.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Accessibility Keyboard Navigation Diagnostic Class
 *
 * @since 1.1091.0000
 */
class Diagnostic_WpAccessibilityKeyboardNavigation extends Diagnostic_Base {

	protected static $slug = 'wp-accessibility-keyboard-navigation';
	protected static $title = 'Wp Accessibility Keyboard Navigation';
	protected static $description = 'Wp Accessibility Keyboard Navigation not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WP_Accessibility' ) && ! function_exists( 'wpa_init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify skip link is enabled
		$skip_link = get_option( 'wpa_skiplink', '' );
		if ( empty( $skip_link ) ) {
			$issues[] = 'Skip navigation link not configured';
		}

		// Check 2: Check for focus outline visibility
		$focus_outline = get_option( 'wpa_focus_outline', '' );
		if ( empty( $focus_outline ) ) {
			$issues[] = 'Focus outline not enabled for keyboard navigation';
		}

		// Check 3: Verify keyboard navigation for dropdowns
		$keyboard_nav = get_option( 'wpa_keyboard_nav', '' );
		if ( empty( $keyboard_nav ) ) {
			$issues[] = 'Dropdown keyboard navigation not enabled';
		}

		// Check 4: Check ARIA landmark support
		$landmarks = get_option( 'wpa_insert_roles', '' );
		if ( empty( $landmarks ) ) {
			$issues[] = 'ARIA landmark roles not added';
		}

		// Check 5: Verify tabindex removal for accessibility
		$tabindex = get_option( 'wpa_tabindex', '' );
		if ( empty( $tabindex ) ) {
			$issues[] = 'Tabindex correction not enabled';
		}

		// Check 6: Check for longdesc support
		$longdesc = get_option( 'wpa_longdesc', '' );
		if ( empty( $longdesc ) ) {
			$issues[] = 'Image longdesc support not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d accessibility keyboard navigation issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-accessibility-keyboard-navigation',
			);
		}

		return null;
	}
}
