<?php
/**
 * Oxygen Builder Javascript Execution Diagnostic
 *
 * Oxygen Builder Javascript Execution issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.817.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder Javascript Execution Diagnostic Class
 *
 * @since 1.817.0000
 */
class Diagnostic_OxygenBuilderJavascriptExecution extends Diagnostic_Base {

	protected static $slug = 'oxygen-builder-javascript-execution';
	protected static $title = 'Oxygen Builder Javascript Execution';
	protected static $description = 'Oxygen Builder Javascript Execution issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CT_VERSION' ) && ! class_exists( 'Oxygen_VSB_Connection' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify deferred JS loading
		$defer_js = get_option( 'oxygen_defer_js', 0 );
		if ( ! $defer_js ) {
			$issues[] = 'Deferred JavaScript loading not enabled';
		}

		// Check 2: Check for inline JS optimization
		$inline_js = get_option( 'oxygen_inline_js', 0 );
		if ( ! $inline_js ) {
			$issues[] = 'Inline JavaScript optimization not enabled';
		}

		// Check 3: Verify JS minification
		$minify_js = get_option( 'oxygen_js_minify', 0 );
		if ( ! $minify_js ) {
			$issues[] = 'JavaScript minification not enabled';
		}

		// Check 4: Check for unused JS removal
		$remove_unused = get_option( 'oxygen_remove_unused_js', 0 );
		if ( ! $remove_unused ) {
			$issues[] = 'Unused JavaScript removal not enabled';
		}

		// Check 5: Verify animation scripts optimization
		$animations = get_option( 'oxygen_optimize_animations', 0 );
		if ( ! $animations ) {
			$issues[] = 'Animation script optimization not enabled';
		}

		// Check 6: Check for delayed script execution
		$delay_js = get_option( 'oxygen_delay_js', 0 );
		if ( ! $delay_js ) {
			$issues[] = 'Delayed JavaScript execution not enabled';
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
					'Found %d Oxygen Builder JavaScript issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/oxygen-builder-javascript-execution',
			);
		}

		return null;
	}
}
