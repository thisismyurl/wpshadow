<?php
/**
 * Accessible Poetry Form Labels Diagnostic
 *
 * Accessible Poetry Form Labels not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1099.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible Poetry Form Labels Diagnostic Class
 *
 * @since 1.1099.0000
 */
class Diagnostic_AccessiblePoetryFormLabels extends Diagnostic_Base {

	protected static $slug = 'accessible-poetry-form-labels';
	protected static $title = 'Accessible Poetry Form Labels';
	protected static $description = 'Accessible Poetry Form Labels not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Accessible_Poetry' ) && ! function_exists( 'accessible_poetry_init' ) ) {
			return null;
		}

		$issues = array();

		// Check for form submissions without labels
		global $wpdb;
		$forms_table = $wpdb->prefix . 'accessible_poetry_forms';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$forms_table}'" ) === $forms_table ) {
			$missing_labels = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$forms_table}
				 WHERE form_data NOT LIKE '%<label%'
				 OR form_data LIKE '%<input%' AND form_data NOT LIKE '%aria-label%'"
			);

			if ( $missing_labels > 0 ) {
				$issues[] = "forms without proper labels ({$missing_labels} forms)";
			}
		}

		// Check for ARIA attributes configuration
		$aria_enabled = get_option( 'accessible_poetry_aria_enabled', '1' );
		if ( '0' === $aria_enabled ) {
			$issues[] = 'ARIA attributes disabled (reduces accessibility)';
		}

		// Check for keyboard navigation support
		$keyboard_nav = get_option( 'accessible_poetry_keyboard_nav', '1' );
		if ( '0' === $keyboard_nav ) {
			$issues[] = 'keyboard navigation disabled (accessibility issue)';
		}

		// Check for screen reader optimization
		$screen_reader = get_option( 'accessible_poetry_screen_reader', '1' );
		if ( '0' === $screen_reader ) {
			$issues[] = 'screen reader optimization disabled';
		}

		// Check for form validation messages
		$validation_msgs = get_option( 'accessible_poetry_validation_messages', array() );
		if ( empty( $validation_msgs ) || ! is_array( $validation_msgs ) ) {
			$issues[] = 'no accessible validation messages configured';
		}

		// Check for WCAG compliance level
		$wcag_level = get_option( 'accessible_poetry_wcag_level', 'AA' );
		if ( 'A' === $wcag_level || empty( $wcag_level ) ) {
			$issues[] = 'WCAG compliance level below AA standard';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Accessible Poetry form accessibility issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessible-poetry-form-labels',
			);
		}

		return null;
	}
}
