<?php
/**
 * Wp Accessibility Focus Management Diagnostic
 *
 * Wp Accessibility Focus Management not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1089.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Accessibility Focus Management Diagnostic Class
 *
 * @since 1.1089.0000
 */
class Diagnostic_WpAccessibilityFocusManagement extends Diagnostic_Base {

	protected static $slug = 'wp-accessibility-focus-management';
	protected static $title = 'Wp Accessibility Focus Management';
	protected static $description = 'Wp Accessibility Focus Management not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WP_Accessibility' ) && ! function_exists( 'wpa_init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify focus styles are enabled
		$focus_styles = get_option( 'wpa_focus_outline', '' );
		if ( empty( $focus_styles ) ) {
			$issues[] = 'Focus outline styles not enabled';
		}

		// Check 2: Check for skip link focus handling
		$skip_link = get_option( 'wpa_skiplink', '' );
		if ( empty( $skip_link ) ) {
			$issues[] = 'Skip link focus management not configured';
		}

		// Check 3: Verify focus on form errors
		$focus_errors = get_option( 'wpa_focus_form_errors', 0 );
		if ( ! $focus_errors ) {
			$issues[] = 'Focus on form errors not enabled';
		}

		// Check 4: Check for focus restoration after modals
		$focus_restore = get_option( 'wpa_restore_focus', 0 );
		if ( ! $focus_restore ) {
			$issues[] = 'Focus restoration after modal dialogs not enabled';
		}

		// Check 5: Verify keyboard navigation helpers
		$keyboard_helpers = get_option( 'wpa_keyboard_nav', '' );
		if ( empty( $keyboard_helpers ) ) {
			$issues[] = 'Keyboard navigation helpers not enabled';
		}

		// Check 6: Check for admin focus styles
		$admin_focus = get_option( 'wpa_admin_focus', 0 );
		if ( ! $admin_focus ) {
			$issues[] = 'Admin focus styles not enabled';
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
					'Found %d focus management issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-accessibility-focus-management',
			);
		}

		return null;
	}
}
