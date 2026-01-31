<?php
/**
 * HTML Detect Duplicate JavaScript Includes Diagnostic
 *
 * Detects duplicate JavaScript file includes.
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
 * HTML Detect Duplicate JavaScript Includes Diagnostic Class
 *
 * Identifies pages where the same JavaScript file is being included
 * multiple times, which wastes bandwidth and can cause conflicts.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Duplicate_Javascript_Includes extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-duplicate-javascript-includes';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate JavaScript File Includes';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript files included multiple times on the same page';

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

		$js_files   = array();
		$duplicates = array();

		// Check WordPress script queue.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$script_obj = $wp_scripts->registered[ $handle ];

					if ( isset( $script_obj->src ) ) {
						$src = (string) $script_obj->src;

						// Normalize URL for comparison.
						$normalized_src = strtolower( trim( $src ) );

						if ( isset( $js_files[ $normalized_src ] ) ) {
							// Duplicate found.
							$duplicates[] = array(
								'file'   => $src,
								'handle' => $handle,
							);
						}

						$js_files[ $normalized_src ] = $src;
					}
				}
			}
		}

		// Check scripts for inline script src patterns.
		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for <script src="..."> tags.
					if ( preg_match_all( '/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[1] as $src ) {
							$normalized_src = strtolower( trim( $src ) );

							if ( isset( $js_files[ $normalized_src ] ) ) {
								$duplicates[] = array(
									'file'   => $src,
									'handle' => $handle,
								);
							}

							$js_files[ $normalized_src ] = $src;
						}
					}
				}
			}
		}

		// Count only actual duplicates (appears more than once).
		$actual_duplicates = array();
		$file_count        = array_count_values( array_map( 'strtolower', array_values( $js_files ) ) );

		foreach ( $file_count as $file => $count ) {
			if ( $count > 1 ) {
				$actual_duplicates[] = array(
					'file'  => $file,
					'count' => $count,
				);
			}
		}

		if ( empty( $actual_duplicates ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $actual_duplicates, 0, $max_items ) as $dup ) {
			$items_list .= sprintf(
				"\n- %s (loaded %d times)",
				esc_html( basename( $dup['file'] ) ),
				(int) $dup['count']
			);
		}

		if ( count( $actual_duplicates ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more duplicate JS files", 'wpshadow' ),
				count( $actual_duplicates ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d duplicate JavaScript file(s) on this page. JavaScript files are being included multiple times, wasting bandwidth and potentially causing script conflicts. WordPress should automatically deduplicate scripts, but plugins may be adding redundant includes.%2$s', 'wpshadow' ),
				count( $actual_duplicates ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-duplicate-javascript-includes',
			'meta'         => array(
				'duplicates' => $actual_duplicates,
			),
		);
	}
}
