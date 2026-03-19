<?php
/**
 * Memory Limit Exceeded During Export Diagnostic
 *
 * Detects when export process crashes due to insufficient
 * PHP memory.
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
 * Memory Limit Exceeded During Export Diagnostic Class
 *
 * Detects memory limit issues during export operations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Memory_Limit_Exceeded_During_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-limit-exceeded-during-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Limit Exceeded During Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects memory limit issues during export operations';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Determines if export will run into memory limit issues
	 * based on site size and server configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get PHP memory configuration.
		$memory_limit_str = ini_get( 'memory_limit' );
		$memory_limit_bytes = self::convert_to_bytes( $memory_limit_str );

		// Count content.
		$total_posts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN (%s, %s)",
				'publish',
				'draft'
			)
		);

		// Count postmeta.
		$total_meta = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}"
			)
		);

		// Get average post content size.
		$avg_content_size = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(CHAR_LENGTH(post_content)) FROM {$wpdb->posts} 
				WHERE post_status = %s",
				'publish'
			)
		);

		// Count attachments and media.
		$total_attachments = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'attachment'
			)
		);

		// Estimate memory usage during export.
		// Each post needs roughly: post data + meta data + attachments + overhead.
		$estimated_memory_for_posts = ( $total_posts * ( $avg_content_size + 50000 ) ); // Post + metadata.
		$estimated_memory_for_meta = $total_meta * 500;
		$estimated_memory_for_attachments = $total_attachments * 100000;
		$estimated_total_memory = $estimated_memory_for_posts + $estimated_memory_for_meta + $estimated_memory_for_attachments;

		// Add buffer for WordPress core.
		$estimated_total_memory += 50 * 1024 * 1024; // 50MB buffer.

		// Check if memory will be exceeded.
		$memory_issue_severity = 'low';
		$will_exceed_memory = false;

		if ( $estimated_total_memory > $memory_limit_bytes ) {
			$will_exceed_memory = true;

			if ( $estimated_total_memory > $memory_limit_bytes * 2 ) {
				$memory_issue_severity = 'critical';
			} else {
				$memory_issue_severity = 'high';
			}
		}

		// Check for low memory hosting plans.
		$is_budget_hosting = $memory_limit_bytes < 128 * 1024 * 1024; // Less than 128MB.

		// Get current memory usage if possible.
		$current_memory_usage = function_exists( 'memory_get_usage' ) ? memory_get_usage() : 0;
		$memory_usage_percent = $current_memory_usage > 0 ? round( ( $current_memory_usage / $memory_limit_bytes ) * 100 ) : 0;

		// Check for WP_MEMORY_LIMIT in wp-config.
		$wp_memory_limit = defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : $memory_limit_str;
		$wp_memory_limit_bytes = self::convert_to_bytes( $wp_memory_limit );

		// Check PHP version (memory efficiency varies).
		$php_version = phpversion();

		// Check for memory-heavy plugins.
		$heavy_plugins = array(
			'woocommerce/woocommerce.php' => 'WooCommerce',
			'elementor/elementor.php' => 'Elementor',
			'yoast-seo/wp-seo.php' => 'Yoast SEO',
			'acf-pro/acf.php' => 'ACF Pro',
		);

		$heavy_plugins_active = 0;
		foreach ( $heavy_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$heavy_plugins_active++;
			}
		}

		if ( $will_exceed_memory || $is_budget_hosting || $heavy_plugins_active > 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: memory available, %s: estimated needed */
					__( 'Export likely to exceed memory limit: %s available vs %s needed', 'wpshadow' ),
					size_format( $memory_limit_bytes ),
					size_format( $estimated_total_memory )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/memory-limit-exceeded-during-export',
				'details'      => array(
					'php_memory_limit'              => $memory_limit_str,
					'wp_memory_limit'               => $wp_memory_limit,
					'memory_limit_bytes'            => size_format( $memory_limit_bytes ),
					'current_memory_usage'          => size_format( $current_memory_usage ),
					'memory_usage_percent'          => $memory_usage_percent . '%',
					'total_posts'                   => $total_posts,
					'total_postmeta'                => $total_meta,
					'total_attachments'             => $total_attachments,
					'avg_post_content_size'         => size_format( $avg_content_size ),
					'estimated_memory_for_posts'    => size_format( $estimated_memory_for_posts ),
					'estimated_memory_for_meta'     => size_format( $estimated_memory_for_meta ),
					'estimated_memory_for_attachments' => size_format( $estimated_memory_for_attachments ),
					'estimated_total_memory_needed' => size_format( $estimated_total_memory ),
					'will_exceed_memory'            => $will_exceed_memory,
					'memory_issue_severity'         => $memory_issue_severity,
					'is_budget_hosting'             => $is_budget_hosting,
					'heavy_plugins_active'          => $heavy_plugins_active,
					'php_version'                   => $php_version,
					'backup_failure_risk'           => __( 'Export will crash with fatal memory error', 'wpshadow' ),
					'incomplete_backup'             => __( 'Backup file will be incomplete or corrupted', 'wpshadow' ),
					'data_loss_risk'                => __( 'No viable backup if export fails', 'wpshadow' ),
					'fix_methods'                   => array(
						__( 'Increase PHP memory_limit in wp-config.php (e.g., define( \'WP_MEMORY_LIMIT\', \'256M\' );)', 'wpshadow' ),
						__( 'Request hosting provider increase memory allocation', 'wpshadow' ),
						__( 'Upgrade to hosting plan with higher memory limit', 'wpshadow' ),
						__( 'Use export plugin with chunked/streaming export', 'wpshadow' ),
						__( 'Export in smaller batches instead of all at once', 'wpshadow' ),
					),
					'optimization'                  => array(
						__( 'Disable memory-heavy plugins during export', 'wpshadow' ),
						__( 'Clean up revisions and autosaves before export', 'wpshadow' ),
						__( 'Delete spam and trash before exporting', 'wpshadow' ),
						__( 'Export by content type (posts, pages separately)', 'wpshadow' ),
						__( 'Move attachments to CDN before export', 'wpshadow' ),
					),
					'verification'                  => array(
						__( 'Attempt export and monitor error logs', 'wpshadow' ),
						__( 'Check for fatal memory limit errors', 'wpshadow' ),
						__( 'Increase memory_limit and retry', 'wpshadow' ),
						__( 'Test smaller export batch', 'wpshadow' ),
						__( 'Use database backup as primary backup method', 'wpshadow' ),
					),
					'critical_note'                 => __( 'Sites exceeding memory limits cannot reliably export - must increase memory or use alternative backup method', 'wpshadow' ),
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
		if ( -1 === (int) $value ) {
			return PHP_INT_MAX; // Unlimited.
		}

		$value = trim( $value );
		$last = strtolower( $value[ strlen( $value ) - 1 ] ?? '' );

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
