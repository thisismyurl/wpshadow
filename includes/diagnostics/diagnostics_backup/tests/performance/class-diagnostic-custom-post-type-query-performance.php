<?php
/**
 * Custom Post Type Query Performance Diagnostic
 *
 * Checks for custom post type query optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Type Query Performance Diagnostic Class
 *
 * Detects custom post type performance issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Custom_Post_Type_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-post-type-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom post types are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_post_types;

		// Count custom post types
		$custom_count = 0;
		if ( ! empty( $wp_post_types ) ) {
			foreach ( $wp_post_types as $post_type ) {
				if ( ! in_array( $post_type->name, array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item' ), true ) ) {
					$custom_count++;
				}
			}
		}

		if ( $custom_count > 15 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Site has %d custom post types. Excessive custom post types can slow down post type queries and admin menu rendering.', 'wpshadow' ),
					absint( $custom_count )
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/custom-post-type-query-performance',
			);
		}

		return null;
	}
}
