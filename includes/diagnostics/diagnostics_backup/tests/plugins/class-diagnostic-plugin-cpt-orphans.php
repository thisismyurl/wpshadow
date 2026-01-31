<?php
/**
 * Plugin Custom Post Type Orphans Diagnostic
 *
 * Identifies orphaned custom post types from deleted plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin CPT Orphans Class
 *
 * Detects custom post types without active plugins.
 *
 * @since 1.5030.1045
 */
class Diagnostic_Plugin_CPT_Orphans extends Diagnostic_Base {

	protected static $slug        = 'plugin-cpt-orphans';
	protected static $title       = 'Plugin Custom Post Type Orphans';
	protected static $description = 'Identifies orphaned custom post types';
	protected static $family      = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_cpt_orphans';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// Get all custom post types.
		$registered_cpts = get_post_types( array( '_builtin' => false ), 'objects' );

		// Get post types that actually have posts.
		$results = $wpdb->get_results(
			"SELECT post_type, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_type NOT IN ('post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation')
			GROUP BY post_type
			HAVING count > 0"
		);

		$orphaned = array();

		foreach ( $results as $row ) {
			$post_type = $row->post_type;
			$count     = (int) $row->count;

			// Check if post type is registered.
			if ( ! isset( $registered_cpts[ $post_type ] ) ) {
				$orphaned[] = array(
					'post_type'  => $post_type,
					'post_count' => $count,
					'status'     => 'Unregistered (plugin likely deactivated)',
				);
			}
		}

		if ( ! empty( $orphaned ) ) {
			$total_orphaned = array_sum( array_column( $orphaned, 'post_count' ) );

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of post types, 2: number of posts */
					__( '%1$d orphaned post types found with %2$d total posts. Clean up to free database space.', 'wpshadow' ),
					count( $orphaned ),
					$total_orphaned
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-cpt-orphans',
				'data'         => array(
					'orphaned_post_types' => $orphaned,
					'total_orphaned'      => $total_orphaned,
					'post_type_count'     => count( $orphaned ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
