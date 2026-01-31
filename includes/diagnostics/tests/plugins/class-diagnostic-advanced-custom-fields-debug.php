<?php
/**
 * Advanced Custom Fields Debug Diagnostic
 *
 * Advanced Custom Fields Debug issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Custom Fields Debug Diagnostic Class
 *
 * @since 1.1050.0000
 */
class Diagnostic_AdvancedCustomFieldsDebug extends Diagnostic_Base {

	protected static $slug = 'advanced-custom-fields-debug';
	protected static $title = 'Advanced Custom Fields Debug';
	protected static $description = 'Advanced Custom Fields Debug issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Debug mode enabled in production.
		if ( defined( 'ACF_DEBUG' ) && ACF_DEBUG && ! defined( 'WP_DEBUG' ) ) {
			$issues[] = 'ACF_DEBUG enabled in production (exposes field structure)';
		}

		// Check 2: Show admin messages.
		$show_admin = get_option( 'acf_show_admin', '1' );
		if ( '1' === $show_admin && ! current_user_can( 'manage_options' ) ) {
			$issues[] = 'ACF admin notices visible to non-administrators';
		}

		// Check 3: Error logging enabled.
		$error_log = get_option( 'acf_error_log', '0' );
		if ( '1' === $error_log ) {
			global $wpdb;
			$log_entries = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
					'_acf_error_%'
				)
			);
			if ( $log_entries > 100 ) {
				$issues[] = "{$log_entries} error log entries (database bloat)";
			}
		}

		// Check 4: Development mode indicators.
		$dev_mode = get_option( 'acf_dev_mode', '0' );
		if ( '1' === $dev_mode && ! defined( 'WP_DEBUG' ) ) {
			$issues[] = 'ACF development mode active (should disable in production)';
		}

		// Check 5: Field key debugging.
		$show_keys = get_option( 'acf_show_field_keys', '0' );
		if ( '1' === $show_keys ) {
			$issues[] = 'field keys visible in admin (security concern)';
		}

		// Check 6: PHP error display.
		if ( defined( 'ACF_SHOW_ERRORS' ) && ACF_SHOW_ERRORS ) {
			$issues[] = 'ACF_SHOW_ERRORS enabled (exposes internal errors to users)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF debug configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-custom-fields-debug',
			);
		}

		return null;
	}
}
