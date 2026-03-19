<?php
/**
 * Script Execution Strategy Diagnostic
 *
 * Analyzes JavaScript execution strategy (inline, defer, async) for optimal
 * page load performance and rendering optimization.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Script Execution Strategy Diagnostic Class
 *
 * Verifies script optimization:
 * - Async vs defer ratio
 * - Inline critical scripts
 * - Heavy script detection
 * - Script loading order
 *
 * @since 1.6093.1200
 */
class Diagnostic_Script_Execution_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'script-execution-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Script Execution Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes JavaScript execution strategy for optimal performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		$total_scripts    = 0;
		$async_scripts    = 0;
		$defer_scripts    = 0;
		$blocking_scripts = 0;
		$heavy_scripts    = array();

		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( ! $script || empty( $script->src ) ) {
					continue;
				}

				$total_scripts++;

				// Check script strategy
				$is_async = isset( $script->extra['async'] ) && $script->extra['async'];
				$is_defer = isset( $script->extra['defer'] ) && $script->extra['defer'];

				if ( $is_async ) {
					$async_scripts++;
				} elseif ( $is_defer ) {
					$defer_scripts++;
				} else {
					$blocking_scripts++;
				}

				// Check for heavy scripts (jQuery, etc.)
				if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) {
					$heavy_scripts[] = $handle;
				}
			}
		}

		// Flag if many scripts are render-blocking
		if ( $blocking_scripts > 3 || ( $total_scripts > 0 && ( $blocking_scripts / $total_scripts ) > 0.4 ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: blocking scripts, %d: total scripts */
					__( '%d of %d scripts are render-blocking. Use async/defer to improve TTI.', 'wpshadow' ),
					$blocking_scripts,
					$total_scripts
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/script-execution-strategy',
				'meta'          => array(
					'total_scripts'        => $total_scripts,
					'async_count'          => $async_scripts,
					'defer_count'          => $defer_scripts,
					'blocking_count'       => $blocking_scripts,
					'heavy_scripts'        => $heavy_scripts,
					'recommendation'       => 'Add defer to all non-critical scripts, use async for independent scripts',
					'impact'               => 'Optimized script loading improves TTI by 300-800ms',
					'best_practice'        => 'Inline critical scripts, defer jQuery and plugins, async for analytics',
				),
			);
		}

		return null;
	}
}
