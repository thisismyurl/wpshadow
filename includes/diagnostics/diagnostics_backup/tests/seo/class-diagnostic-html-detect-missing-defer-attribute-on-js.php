<?php
/**
 * HTML Detect Missing Defer Attribute On JavaScript Diagnostic
 *
 * Detects scripts that should use defer attribute but don't.
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
 * HTML Detect Missing Defer Attribute On JavaScript Diagnostic Class
 *
 * Identifies scripts that could benefit from defer attribute to improve
 * page load performance. Defer allows HTML parsing to continue while
 * scripts load in the background.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Defer_Attribute_On_Js extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-defer-attribute-on-js';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Defer Attribute on JavaScript';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects scripts that could benefit from defer attribute';

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

		$missing_defer = array();

		// Scripts that can benefit from defer (not critical for first paint).
		$deferrable_patterns = array(
			'theme' => 'Theme JavaScript',
			'main'  => 'Main Script',
			'app'   => 'Application Script',
			'lib'   => 'Library Script',
		);

		// Check WordPress scripts.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->src ) ) {
					$src = (string) $script_obj->src;

					// Check if it's a deferrable script.
					$is_deferrable = false;
					$type_label    = '';

					foreach ( $deferrable_patterns as $pattern => $label ) {
						if ( stripos( $handle, $pattern ) !== false ) {
							$is_deferrable = true;
							$type_label    = $label;
							break;
						}
					}

					if ( $is_deferrable ) {
						// Check if it already has defer or async.
						$has_defer = isset( $script_obj->extra['defer'] ) && $script_obj->extra['defer'];
						$has_async = isset( $script_obj->extra['async'] ) && $script_obj->extra['async'];

						if ( ! $has_defer && ! $has_async ) {
							$missing_defer[] = array(
								'handle' => $handle,
								'src'    => $src,
								'type'   => $type_label,
								'issue'  => sprintf(
									__( '%s could benefit from defer attribute', 'wpshadow' ),
									$type_label
								),
							);
						}
					}
				}

				// Check scripts data for src patterns.
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for script tags with src but no defer/async.
					if ( preg_match_all( '/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[0] as $tag ) {
							// Only report if not already defer or async, and not inline.
							if ( ! preg_match( '/\s(?:async|defer)/i', $tag ) && ! preg_match( '/\s(?:type|src)=["\']text\/(?:html|template)["\']/', $tag ) ) {
								// Check if looks like main site script.
								if ( preg_match( '/(?:theme|main|app)\.js/i', $tag ) ) {
									$missing_defer[] = array(
										'handle' => $handle,
										'tag'    => substr( $tag, 0, 80 ),
										'issue'  => __( 'Main site script could use defer attribute', 'wpshadow' ),
									);
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $missing_defer ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $missing_defer, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $item['handle'] ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $missing_defer ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more scripts missing defer", 'wpshadow' ),
				count( $missing_defer ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d script(s) that could benefit from defer attribute. Scripts that don\'t need to run before page content loads should use defer="defer". This allows HTML parsing to continue while scripts load in the background, speeding up page display.%2$s', 'wpshadow' ),
				count( $missing_defer ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-defer-attribute-on-js',
			'meta'         => array(
				'scripts' => $missing_defer,
			),
		);
	}
}
