<?php
/**
 * HTML Detect Duplicate CSS Includes Diagnostic
 *
 * Detects duplicate CSS file includes.
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
 * HTML Detect Duplicate CSS Includes Diagnostic Class
 *
 * Identifies pages where the same CSS file is being included multiple
 * times, which wastes bandwidth and slows page load.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Duplicate_Css_Includes extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-duplicate-css-includes';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate CSS File Includes';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects CSS files included multiple times on the same page';

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

		$css_files    = array();
		$duplicates   = array();
		$seen_handles = array();

		// Check WordPress style queue.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					$style_obj = $wp_styles->registered[ $handle ];

					if ( isset( $style_obj->src ) ) {
						$src = (string) $style_obj->src;

						// Normalize URL for comparison.
						$normalized_src = strtolower( trim( $src ) );

						if ( isset( $css_files[ $normalized_src ] ) ) {
							// Duplicate found.
							$duplicates[] = array(
								'file'   => $src,
								'count'  => count( array_filter( $css_files, function( $f ) use ( $normalized_src ) {
									return strtolower( trim( $f ) ) === $normalized_src;
								} ) ) + 1,
								'handle' => $handle,
							);
						}

						$css_files[ $normalized_src ] = $src;
						$seen_handles[ $handle ]       = $normalized_src;
					}
				}
			}
		}

		// Check scripts for inline CSS link patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for <link rel="stylesheet"> tags.
					if ( preg_match_all( '/<link[^>]*rel=["\']?stylesheet["\']?[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[1] as $href ) {
							$normalized_href = strtolower( trim( $href ) );

							if ( isset( $css_files[ $normalized_href ] ) ) {
								$duplicates[] = array(
									'file'   => $href,
									'count'  => count( array_filter( $css_files, function( $f ) use ( $normalized_href ) {
										return strtolower( trim( $f ) ) === $normalized_href;
									} ) ) + 1,
									'handle' => $handle,
								);
							}

							$css_files[ $normalized_href ] = $href;
						}
					}
				}
			}
		}

		// Count only actual duplicates (appears more than once).
		$actual_duplicates = array();
		$file_count        = array_count_values( array_map( 'strtolower', array_values( $css_files ) ) );

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
				__( "\n...and %d more duplicate CSS files", 'wpshadow' ),
				count( $actual_duplicates ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d duplicate CSS file(s) on this page. CSS files are being included multiple times, wasting bandwidth and slowing page load. WordPress should automatically deduplicate styles, but plugins may be adding redundant includes.%2$s', 'wpshadow' ),
				count( $actual_duplicates ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-duplicate-css-includes',
			'meta'         => array(
				'duplicates' => $actual_duplicates,
			),
		);
	}
}
