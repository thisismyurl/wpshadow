<?php
/**
 * HTML Detect Blocking Synchronous JavaScript Diagnostic
 *
 * Detects synchronous scripts that block page rendering.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Blocking Synchronous JavaScript Diagnostic Class
 *
 * Identifies synchronous scripts in the <head> that block page rendering
 * until they finish loading. These should be moved or converted to async/defer.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Blocking_Synchronous_Javascript extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-blocking-synchronous-javascript';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blocking Synchronous JavaScript';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects synchronous scripts that block page rendering';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$blocking_scripts = array();

		// Check WordPress scripts for non-async/defer scripts with src (indicate blocking).
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->src ) ) {
					$src = (string) $script_obj->src;

					// Check for src (not inline).
					if ( ! empty( $src ) ) {
						// Check if it has async or defer.
						$has_async = isset( $script_obj->extra['async'] ) && $script_obj->extra['async'];
						$has_defer = isset( $script_obj->extra['defer'] ) && $script_obj->extra['defer'];

						// If no async/defer, it's synchronous (blocking).
						if ( ! $has_async && ! $has_defer ) {
							// Check if it's in head position (more problematic).
							// WordPress outputs scripts in two places: head and footer.
							// Scripts in head are more blocking.
							if ( isset( $script_obj->extra['in_footer'] ) && ! $script_obj->extra['in_footer'] ) {
								$blocking_scripts[] = array(
									'handle'     => $handle,
									'src'        => $src,
									'position'   => 'head',
									'issue'      => __( 'Synchronous script in <head> blocks page rendering', 'wpshadow' ),
									'size_bytes' => strlen( $src ),
								);
							} else {
								// Footer scripts are less blocking but still synchronous.
								$blocking_scripts[] = array(
									'handle'     => $handle,
									'src'        => $src,
									'position'   => 'footer',
									'issue'      => __( 'Synchronous footer script delays interactive page state', 'wpshadow' ),
									'size_bytes' => strlen( $src ),
								);
							}
						}
					}
				}

				// Check for inline script data with script src tags (blocking patterns).
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for synchronous script loads via document.write or similar.
					if ( preg_match( '/document\.write/i', $data ) || preg_match( '/document\.createElement.*script/i', $data ) ) {
						if ( ! preg_match( '/async|defer/i', $data ) ) {
							$blocking_scripts[] = array(
								'handle' => $handle,
								'issue'  => __( 'Script uses document.write() to load scripts (blocks parsing)', 'wpshadow' ),
								'type'   => 'document.write',
							);
						}
					}
				}
			}
		}

		// Deduplicate and limit.
		$unique_blocking = array();
		$seen_handles    = array();

		foreach ( $blocking_scripts as $script ) {
			if ( ! in_array( $script['handle'], $seen_handles, true ) ) {
				$unique_blocking[] = $script;
				$seen_handles[]    = $script['handle'];
			}
		}

		if ( empty( $unique_blocking ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $unique_blocking, 0, $max_items ) as $script ) {
			$items_list .= sprintf(
				"\n- %s (%s): %s",
				esc_html( $script['handle'] ),
				isset( $script['position'] ) ? esc_html( $script['position'] ) : esc_html( $script['type'] ?? 'dynamic' ),
				esc_html( $script['issue'] )
			);
		}

		if ( count( $unique_blocking ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more blocking scripts", 'wpshadow' ),
				count( $unique_blocking ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d blocking synchronous script(s). These scripts load and execute before the browser can render the page, slowing down page display and interactivity. Move scripts to footer, use async="async" for non-critical scripts, or use defer="defer" for scripts that depend on DOM.%2$s', 'wpshadow' ),
				count( $unique_blocking ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-blocking-synchronous-javascript',
			'meta'         => array(
				'scripts' => $unique_blocking,
			),
		);
	}
}
