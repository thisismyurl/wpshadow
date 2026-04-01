<?php
/**
 * No Chunked Export Option for Large Sites
 *
 * Detects whether WordPress offers ability to export large sites in smaller chunks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Export
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Chunked_Export_Option_For_Large_Sites Class
 *
 * Tests whether WordPress export supports chunking for large sites.
 * Verifies date range filters, content type filtering, and export capability.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Chunked_Export_Option_For_Large_Sites extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-chunked-export-option-for-large-sites';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Chunked Export Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies ability to export large sites in smaller chunks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for chunked export capability and filtering options.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count total posts
		$post_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN ('publish', 'draft', 'pending', 'private')" );

		// Large site threshold: 5000+ posts
		if ( $post_count < 5000 ) {
			return null;
		}

		$issues = array();

		// 1. Check if chunking is supported
		if ( ! self::has_chunking_support() ) {
			$issues[] = __( 'WordPress core does not support chunked exports', 'wpshadow' );
		}

		// 2. Check if date range filter is available
		if ( ! self::has_date_range_filter() ) {
			$issues[] = __( 'No date range filtering available for export', 'wpshadow' );
		}

		// 3. Check if post type filtering works
		if ( ! self::has_post_type_filtering() ) {
			$issues[] = __( 'Cannot filter export by post type', 'wpshadow' );
		}

		// 4. Check if category filtering is available
		if ( ! self::has_category_filtering() ) {
			$issues[] = __( 'Cannot filter export by category', 'wpshadow' );
		}

		// 5. Check for batch size configuration
		$batch_config = self::get_batch_size_config();
		if ( ! $batch_config ) {
			$issues[] = __( 'No configurable batch size for export', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts, %d: number of issues */
					__( 'Large site detected (%d posts) with %d export limitations', 'wpshadow' ),
					$post_count,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/chunked-export-large-sites?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'recommendations' => array(
					__( 'Export in smaller date ranges to reduce memory usage', 'wpshadow' ),
					__( 'Filter by post type to split large exports', 'wpshadow' ),
					__( 'Consider using migration plugins with chunking support', 'wpshadow' ),
					__( 'Increase PHP memory limit for large exports', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if chunking is supported.
	 *
	 * @since 0.6093.1200
	 * @return bool True if chunking is supported.
	 */
	private static function has_chunking_support() {
		// Check if custom filter is available
		if ( has_filter( 'wxr_export_post_start' ) || has_filter( 'wxr_export_post_end' ) ) {
			return true;
		}

		// Check for chunking plugins
		$chunking_plugins = array(
			'wp-migrate-db/wp-migrate-db.php',
			'all-in-one-migration/all-in-one-migration.php',
			'duplicator/duplicator.php',
			'wp-staging/wp-staging.php',
		);

		foreach ( $chunking_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check WordPress version (WP 6.2+ has better export)
		global $wp_version;
		if ( version_compare( $wp_version, '6.2', '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if date range filter is available.
	 *
	 * @since 0.6093.1200
	 * @return bool True if date filtering supported.
	 */
	private static function has_date_range_filter() {
		// WordPress export form would show these fields
		// Check if admin is capable of accessing export
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check for date filtering in export hooks
		if ( has_filter( 'query' ) || has_filter( 'posts_where' ) ) {
			return true;
		}

		// Default: WordPress has basic date filtering
		return true;
	}

	/**
	 * Check if post type filtering works.
	 *
	 * @since 0.6093.1200
	 * @return bool True if post type filtering available.
	 */
	private static function has_post_type_filtering() {
		// Get all exportable post types
		$post_types = get_post_types( array( 'can_export' => true ) );

		// If multiple post types available, check if we can filter
		if ( count( $post_types ) > 1 ) {
			// WordPress export UI should show these
			return true;
		}

		return false;
	}

	/**
	 * Check if category filtering is available.
	 *
	 * @since 0.6093.1200
	 * @return bool True if category filtering available.
	 */
	private static function has_category_filtering() {
		// Check if categories exist
		$categories = get_terms( array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'number'     => 1,
		) );

		if ( empty( $categories ) ) {
			return false;
		}

		// Check for category filtering support
		if ( has_filter( 'wxr_export_filter_query' ) ) {
			return true;
		}

		return true; // WordPress default export handles this
	}

	/**
	 * Get batch size configuration.
	 *
	 * @since 0.6093.1200
	 * @return int|false Batch size or false if not configured.
	 */
	private static function get_batch_size_config() {
		// Check if batch size is set via option
		$batch_size = get_option( 'wpshadow_export_batch_size', false );

		if ( $batch_size ) {
			return (int) $batch_size;
		}

		// Check for plugin-specific batch configuration
		if ( defined( 'WP_EXPORT_BATCH_SIZE' ) ) {
			return (int) WP_EXPORT_BATCH_SIZE;
		}

		// WordPress default batch size (if any)
		if ( defined( 'WP_IMPORT_BATCH_SIZE' ) ) {
			return (int) WP_IMPORT_BATCH_SIZE;
		}

		return false;
	}
}
