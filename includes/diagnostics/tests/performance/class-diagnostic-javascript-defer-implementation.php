<?php
/**
 * JavaScript Defer Implementation Diagnostic
 *
 * Issue #4935: JavaScript Not Deferred (Blocks Rendering)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if JavaScript is deferred or async.
 * Synchronous JS blocks HTML parsing and delays page display.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_JavaScript_Defer_Implementation Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_JavaScript_Defer_Implementation extends Diagnostic_Base {

	protected static $slug = 'javascript-defer-implementation';
	protected static $title = 'JavaScript Not Deferred (Blocks Rendering)';
	protected static $description = 'Checks if JavaScript files use defer or async';
	protected static $family = 'performance';

	public static function check() {
		global $wp_scripts;

		$issues = array();
		$blocking_scripts = 0;

		// Check for blocking scripts
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				// Skip jQuery (must be synchronous for compatibility)
				if ( $handle === 'jquery-core' || $handle === 'jquery' ) {
					continue;
				}

				if ( $script->src && empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
					$blocking_scripts++;
				}
			}
		}

		if ( $blocking_scripts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d JavaScript files blocking page rendering', 'wpshadow' ),
				$blocking_scripts
			);
		}

		$issues[] = __( 'Use defer for scripts that need DOM ready', 'wpshadow' );
		$issues[] = __( 'Use async for independent scripts (analytics)', 'wpshadow' );
		$issues[] = __( 'Move scripts to footer (wp_enqueue_script in_footer=true)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'JavaScript blocks HTML parsing by default. Using defer or async allows the browser to continue rendering while scripts load.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/defer-javascript',
				'details'      => array(
					'recommendations'         => $issues,
					'blocking_scripts'        => $blocking_scripts,
					'defer_vs_async'          => 'defer: Execute in order, async: Execute when ready',
					'wordpress_function'      => 'wp_enqueue_script( $handle, $src, $deps, $ver, true );',
				),
			);
		}

		return null;
	}
}
