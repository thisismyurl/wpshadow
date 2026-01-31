<?php
/**
 * Query String Optimization Not Implemented Diagnostic
 *
 * Checks if query strings are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query String Optimization Not Implemented Diagnostic Class
 *
 * Detects unoptimized query strings.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Query_String_Optimization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-string-optimization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query String Optimization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if query strings are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		// Check for version strings in enqueued items
		$has_query_strings = false;

		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ];
				if ( strpos( $script->src, '?' ) !== false ) {
					$has_query_strings = true;
					break;
				}
			}
		}

		if ( $has_query_strings ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Query strings are not optimized. Use static URLs without version parameters for better CDN caching.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/query-string-optimization-not-implemented',
			);
		}

		return null;
	}
}
