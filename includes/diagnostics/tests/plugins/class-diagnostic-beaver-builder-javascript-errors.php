<?php
/**
 * Beaver Builder JavaScript Errors Diagnostic
 *
 * Beaver Builder JavaScript causing errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.344.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder JavaScript Errors Diagnostic Class
 *
 * @since 1.344.0000
 */
class Diagnostic_BeaverBuilderJavascriptErrors extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-javascript-errors';
	protected static $title = 'Beaver Builder JavaScript Errors';
	protected static $description = 'Beaver Builder JavaScript causing errors';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: JS error logging.
		$log_errors = get_option( '_fl_builder_log_js_errors', '0' );
		if ( '0' === $log_errors ) {
			$issues[] = 'JS error logging disabled';
		}

		// Check 2: jQuery compatibility.
		$jquery_compat = get_option( '_fl_builder_jquery_compat', '1' );
		if ( '0' === $jquery_compat ) {
			$issues[] = 'jQuery compatibility mode off';
		}

		// Check 3: Strict mode.
		$strict_mode = get_option( '_fl_builder_js_strict_mode', '0' );
		if ( '0' === $strict_mode ) {
			$issues[] = 'strict mode disabled';
		}

		// Check 4: Error handling.
		$error_handler = get_option( '_fl_builder_js_error_handler', '1' );
		if ( '0' === $error_handler ) {
			$issues[] = 'error handler disabled';
		}

		// Check 5: Console logging.
		$console_log = get_option( '_fl_builder_console_logging', '0' );
		if ( '1' === $console_log ) {
			$issues[] = 'console logging enabled (performance hit)';
		}

		// Check 6: Debug JS.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$issues[] = 'script debug mode on';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Beaver Builder JS issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-javascript-errors',
			);
		}

		return null;
	}
}
