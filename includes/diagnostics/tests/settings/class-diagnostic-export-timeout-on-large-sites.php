<?php
/**
 * Export Timeout on Large Sites Diagnostic
 *
 * Tests whether WordPress export completes or times out when
 * exporting sites with thousands of posts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Timeout on Large Sites Diagnostic Class
 *
 * Tests whether export operations will timeout on large sites.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Export_Timeout_On_Large_Sites extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-timeout-on-large-sites';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Timeout on Large Sites';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether export will timeout on large sites';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Determines if export will timeout based on content size
	 * and server timeout settings.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Count total posts.
		$total_posts = wp_count_posts();
		$publishable_posts = 0;

		// Count public posts.
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $type ) {
			$count = wp_count_posts( $type );
			$publishable_posts += isset( $count->publish ) ? $count->publish : 0;
		}

		// Estimate export file size.
		$avg_post_size = 50000; // ~50KB average per post.
		$avg_meta_size = 10000; // ~10KB average for postmeta.
		$estimated_export_size = ( $publishable_posts * ( $avg_post_size + $avg_meta_size ) );

		// Get actual postmeta size.
		$total_meta = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );

		// Check for large posts with lots of content.
		$large_posts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type IN (%s, %s) 
				AND CHAR_LENGTH(post_content) > %d",
				'post',
				'page',
				500000
			)
		);

		// Get server timeout settings.
		$max_execution_time = (int) ini_get( 'max_execution_time' );
		$default_socket_timeout = (int) ini_get( 'default_socket_timeout' );

		// Estimate processing time.
		$estimated_time = ceil( ( $publishable_posts * 0.5 ) + ( $total_meta * 0.1 ) ); // ~0.5s per post, 0.1s per meta entry.

		// Check for memory limit issues.
		$memory_limit_str = ini_get( 'memory_limit' );
		$memory_limit = self::convert_to_bytes( $memory_limit_str );

		// Check PHP version (newer is faster).
		$php_version = phpversion();

		// Check for export plugins that might speed up process.
		$export_plugins = array(
			'wordpress-importer/wordpress-importer.php' => 'WordPress Importer',
			'all-in-one-import-export/wp-import-export.php' => 'All In One Import Export',
			'wp-powerexport/wp-powerexport.php' => 'WP Power Export',
		);

		$fast_export_plugin_active = false;
		foreach ( $export_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$fast_export_plugin_active = true;
				break;
			}
		}

		// Check if site size is problematic.
		$is_large_site = $publishable_posts > 5000;
		$will_timeout = ( $estimated_time > $max_execution_time ) || ( $estimated_export_size > $memory_limit );

		if ( $is_large_site || $will_timeout ) {
			$timeout_risk = 'high';

			if ( $publishable_posts > 10000 ) {
				$timeout_risk = 'critical';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts, %s: risk level */
					__( 'Export with %d posts has %s timeout risk', 'wpshadow' ),
					$publishable_posts,
					$timeout_risk
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/export-timeout-on-large-sites',
				'details'      => array(
					'total_publishable_posts'       => $publishable_posts,
					'total_postmeta_entries'        => $total_meta,
					'large_posts_detected'          => $large_posts,
					'estimated_export_size'         => size_format( $estimated_export_size ),
					'estimated_processing_time'     => $estimated_time . ' seconds',
					'server_max_execution_time'     => $max_execution_time . ' seconds',
					'server_memory_limit'           => $memory_limit_str,
					'php_version'                   => $php_version,
					'timeout_risk_level'            => $timeout_risk,
					'will_likely_timeout'           => $will_timeout,
					'fast_export_plugin_active'     => $fast_export_plugin_active,
					'backup_capability'             => __( 'Large site unable to export via native WordPress tool', 'wpshadow' ),
					'data_protection_risk'          => __( 'Site cannot be reliably backed up using standard export', 'wpshadow' ),
					'disaster_recovery'             => __( 'Disaster recovery severely limited without viable backup option', 'wpshadow' ),
					'migration_blocker'             => __( 'Site migration blocked due to export timeout issues', 'wpshadow' ),
					'fix_methods'                   => array(
						__( 'Use export plugin with chunked/streamed export support', 'wpshadow' ),
						__( 'Export in batches (by category, date range, post type)', 'wpshadow' ),
						__( 'Request hosting increase PHP timeout temporarily', 'wpshadow' ),
						__( 'Use database backup instead of XML export', 'wpshadow' ),
						__( 'Increase PHP memory_limit in wp-config.php or .htaccess', 'wpshadow' ),
					),
					'optimization'                  => array(
						__( 'Split export by post type (pages, posts, custom types separately)', 'wpshadow' ),
						__( 'Export by date range (monthly batches)', 'wpshadow' ),
						__( 'Exclude revisions and autosaves to reduce size', 'wpshadow' ),
						__( 'Cleanup spam comments before export', 'wpshadow' ),
						__( 'Archive old content separately', 'wpshadow' ),
					),
					'verification'                  => array(
						__( 'Attempt export to identify exact failure point', 'wpshadow' ),
						__( 'Check error logs for timeout messages', 'wpshadow' ),
						__( 'Monitor memory usage during export attempt', 'wpshadow' ),
						__( 'Test with reduced batch size', 'wpshadow' ),
						__( 'Verify database backup as primary backup method', 'wpshadow' ),
					),
					'critical_note'                 => __( 'Large sites cannot reliably backup via XML export - must use database backup or specialized tools', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Convert memory string to bytes.
	 *
	 * @since 1.6093.1200
	 * @param  string $value Memory limit string (e.g., "128M", "2G").
	 * @return int Memory in bytes.
	 */
	private static function convert_to_bytes( $value ) {
		$value = trim( $value );
		$last = strtolower( $value[ strlen( $value ) - 1 ] );

		$value = (int) $value;

		switch ( $last ) {
			case 'g':
				$value *= 1024 * 1024 * 1024;
				break;
			case 'm':
				$value *= 1024 * 1024;
				break;
			case 'k':
				$value *= 1024;
				break;
		}

		return $value;
	}
}
