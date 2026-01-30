<?php
/**
 * HTML Detect Missing Async Where Appropriate Diagnostic
 *
 * Detects scripts that should be async but aren't.
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
 * HTML Detect Missing Async Where Appropriate Diagnostic Class
 *
 * Identifies scripts that could benefit from async attribute to improve
 * page load performance. Scripts that don't need DOM before execution
 * should use async.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Async_Where_Appropriate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-async-where-appropriate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Async Attribute on Scripts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects scripts that could benefit from async attribute';

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

		$missing_async = array();

		// Scripts that typically don't need DOM and can be async.
		$can_be_async = array(
			'analytics' => 'Google Analytics',
			'gtag'      => 'Google Analytics (gtag)',
			'facebook'  => 'Facebook Pixel',
			'hotjar'    => 'Hotjar',
			'intercom'  => 'Intercom',
			'drift'     => 'Drift',
			'stripe'    => 'Stripe',
			'paypal'    => 'PayPal',
		);

		// Check WordPress scripts.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->src ) ) {
					$src = (string) $script_obj->src;

					// Check if script is typically async-able.
					foreach ( $can_be_async as $key => $label ) {
						if ( strpos( strtolower( $src ), $key ) !== false ) {
							// Check if it has async attribute.
							if ( ! isset( $script_obj->extra['async'] ) || ! $script_obj->extra['async'] ) {
								$missing_async[] = array(
									'handle' => $handle,
									'src'    => $src,
									'type'   => $label,
									'issue'  => sprintf(
										__( '%s script could use async attribute', 'wpshadow' ),
										$label
									),
								);
							}
						}
					}
				}

				// Check for scripts with external data attributes that suggest they're async-able.
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for script tags with src but no async.
					if ( preg_match_all( '/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[0] as $tag ) {
							// Only report if not already async or defer.
							if ( ! preg_match( '/\s(?:async|defer)/i', $tag ) ) {
								// Check if it's a third-party tracking script.
								if ( preg_match( '/(google|facebook|hotjar|intercom|drift|stripe|analytics|pixel)/i', $tag ) ) {
									$missing_async[] = array(
										'handle' => $handle,
										'tag'    => substr( $tag, 0, 80 ),
										'issue'  => __( 'Third-party tracking script could use async attribute', 'wpshadow' ),
									);
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $missing_async ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $missing_async, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $item['handle'] ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $missing_async ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more scripts missing async", 'wpshadow' ),
				count( $missing_async ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d script(s) that could benefit from async attribute. Third-party analytics and tracking scripts (Google Analytics, Facebook Pixel, etc.) don\'t need to run before the page renders. Using async="async" speeds up page load without affecting functionality.%2$s', 'wpshadow' ),
				count( $missing_async ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-async-where-appropriate',
			'meta'         => array(
				'scripts' => $missing_async,
			),
		);
	}
}
