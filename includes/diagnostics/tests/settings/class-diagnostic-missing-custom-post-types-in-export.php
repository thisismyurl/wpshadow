<?php
/**
 * Missing Custom Post Types in Export Diagnostic
 *
 * Tests whether WordPress export includes all custom post types
 * or only defaults (posts, pages).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Custom Post Types in Export Diagnostic Class
 *
 * Tests whether WordPress export includes all custom post types
 * registered on the site.
 *
 * @since 1.7033.1200
 */
class Diagnostic_Missing_Custom_Post_Types_In_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-custom-post-types-in-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Custom Post Types in Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether export includes all custom post types';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that all custom post types are exportable
	 * and not missing from export configuration.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get all custom post types (exclude built-ins).
		$built_in_types = array(
			'post',
			'page',
			'attachment',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
		);

		$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
		$custom_types = array();
		$non_exportable = array();

		foreach ( $all_post_types as $type ) {
			if ( ! in_array( $type->name, $built_in_types, true ) ) {
				$custom_types[] = $type;

				// Check if type has export capability.
				if ( ! isset( $type->can_export ) || ! $type->can_export ) {
					$non_exportable[] = $type->name;
				}
			}
		}

		// Count posts by type.
		$cpt_post_counts = array();
		$total_cpt_posts = 0;

		foreach ( $custom_types as $type ) {
			$count = wp_count_posts( $type->name );
			$published_count = isset( $count->publish ) ? $count->publish : 0;

			if ( $published_count > 0 ) {
				$cpt_post_counts[ $type->name ] = $published_count;
				$total_cpt_posts += $published_count;
			}
		}

		// Check export filter status.
		$export_options = (int) get_option( 'blog_export_type', 0 );

		// Check if export_filter is set to exclude certain types.
		$export_filters_active = has_filter( 'query_posts_request' );

		// Check for export-related plugins that might limit export.
		$export_plugins = array(
			'export-post-types/export-post-types.php' => 'Export Custom Post Types',
			'all-in-one-import-export/wp-import-export.php' => 'All In One Import Export',
			'wp-powerexport/wp-powerexport.php' => 'WP Power Export',
		);

		$export_plugin_active = false;
		foreach ( $export_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$export_plugin_active = true;
				break;
			}
		}

		// Check WordPress export tool configuration.
		$wxr_post_types = apply_filters( 'wxr_export_post_types', array( 'post', 'page' ) );

		$missing_cpt_from_export = array();
		foreach ( $custom_types as $type ) {
			if ( ! in_array( $type->name, $wxr_post_types, true ) ) {
				$missing_cpt_from_export[] = $type->name;
			}
		}

		// Check for custom post type registration without export support.
		$cpt_without_export = $wpdb->get_results(
			"SELECT DISTINCT post_type FROM {$wpdb->posts} 
			WHERE post_type NOT IN (" . implode( ',', array_map( 'esc_sql', $built_in_types ) ) . ') 
			LIMIT 20'
		);

		$issues_found = ! empty( $non_exportable ) || ! empty( $missing_cpt_from_export ) || $total_cpt_posts > 0 && empty( $wxr_post_types );

		if ( $issues_found ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of custom post types, %d: number of posts */
					__( '%d custom post types with %d posts are not included in WordPress exports', 'wpshadow' ),
					count( $custom_types ),
					$total_cpt_posts
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/missing-custom-post-types-in-export',
				'details'      => array(
					'custom_post_types_found'      => count( $custom_types ),
					'custom_post_types_list'       => array_column( $custom_types, 'label' ),
					'total_custom_posts'           => $total_cpt_posts,
					'posts_by_type'                => $cpt_post_counts,
					'non_exportable_types'         => $non_exportable,
					'missing_from_wxr_export'      => $missing_cpt_from_export,
					'current_wxr_types'            => $wxr_post_types,
					'export_plugin_installed'      => $export_plugin_active,
					'data_loss_risk'               => sprintf(
						/* translators: %d: number of posts at risk */
						__( '%d custom posts will be lost if exported with native WordPress exporter', 'wpshadow' ),
						$total_cpt_posts
					),
					'backup_incompleteness'        => __( 'Backup exports will be missing significant content and business data', 'wpshadow' ),
					'migration_risk'               => __( 'Complete site migrations impossible without custom post type data', 'wpshadow' ),
					'restoration_issue'            => __( 'Site restoration from backup will lose all custom post type content', 'wpshadow' ),
					'fix_methods'                  => array(
						__( 'Use export plugin with custom post type support', 'wpshadow' ),
						__( 'Add filter to include custom types in WordPress export', 'wpshadow' ),
						__( 'Register custom types with can_export=true', 'wpshadow' ),
						__( 'Ensure exportable post types registered before export', 'wpshadow' ),
						__( 'Use admin export tool with all types selected', 'wpshadow' ),
					),
					'verification'                 => array(
						__( 'Test export by downloading XML file', 'wpshadow' ),
						__( 'Search XML for custom post type entries', 'wpshadow' ),
						__( 'Verify post count matches site content', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Audit export configuration settings', 'wpshadow' ),
					),
					'critical_note'                => __( 'Custom post types without export support represent unbackable, unmigrable content', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
