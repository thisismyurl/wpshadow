<?php
/**
 * Bulk Edit Reliability Diagnostic
 *
 * Validates bulk edit operations on multiple posts.
 * Tests for data loss or corruption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bulk Edit Reliability Diagnostic Class
 *
 * Checks for issues that could cause bulk edit operations
 * to fail or corrupt data.
 *
 * @since 1.6033.1345
 */
class Diagnostic_Bulk_Edit_Reliability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'bulk-edit-reliability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Bulk Edit Reliability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates bulk edit operations and tests for data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check PHP max_execution_time (bulk edits can be slow).
		$max_execution_time = ini_get( 'max_execution_time' );
		if ( $max_execution_time && $max_execution_time > 0 && $max_execution_time < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: time in seconds */
				__( 'PHP execution time limit (%d seconds) may be too short for bulk operations', 'wpshadow' ),
				$max_execution_time
			);
		}

		// Check max_input_vars for bulk operations (each post = many variables).
		$max_input_vars = ini_get( 'max_input_vars' );
		if ( $max_input_vars && $max_input_vars < 2000 ) {
			$issues[] = sprintf(
				/* translators: %d: current value */
				__( 'PHP max_input_vars (%d) may be too low for bulk editing many posts', 'wpshadow' ),
				$max_input_vars
			);
		}

		// Check for excessive save_post hooks that could timeout bulk operations.
		global $wp_filter;
		$save_post_hooks = isset( $wp_filter['save_post'] ) ? count( $wp_filter['save_post']->callbacks ) : 0;
		if ( $save_post_hooks > 25 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d hooks on save_post action (bulk operations may timeout)', 'wpshadow' ),
				$save_post_hooks
			);
		}

		// Check memory limit (bulk operations can be memory intensive).
		$memory_limit = ini_get( 'memory_limit' );
		if ( $memory_limit ) {
			$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
			if ( $memory_bytes < 134217728 ) { // 128MB.
				$issues[] = sprintf(
					/* translators: %s: memory limit */
					__( 'PHP memory limit (%s) may be too low for bulk operations', 'wpshadow' ),
					$memory_limit
				);
			}
		}

		// Check for posts with invalid data that would fail bulk update.
		$posts_invalid_date = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_date = '0000-00-00 00:00:00'
			AND post_status IN ('publish', 'draft', 'pending')"
		);

		if ( $posts_invalid_date > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have invalid dates (bulk edit may fail)', 'wpshadow' ),
				$posts_invalid_date
			);
		}

		// Check for locked posts that can't be bulk edited.
		$locked_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_edit_lock'
				AND meta_value > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 MINUTE))"
			)
		);

		if ( $locked_posts > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts currently locked (unavailable for bulk edit)', 'wpshadow' ),
				$locked_posts
			);
		}

		// Check for very large number of posts (bulk operations may struggle).
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status IN ('publish', 'draft', 'pending', 'private')
			AND post_type IN ('post', 'page')"
		);

		if ( $total_posts > 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( 'Site has %d posts - bulk operations may be slow', 'wpshadow' ),
				number_format( $total_posts )
			);
		}

		// Check admin AJAX availability.
		$admin_ajax_url = admin_url( 'admin-ajax.php' );
		$ajax_test      = Diagnostic_Request_Helper::post_result(
			$admin_ajax_url,
			array(
				'timeout' => 5,
				'body'    => array( 'action' => 'heartbeat' ),
			)
		);

		if ( ! $ajax_test['success'] || (int) $ajax_test['code'] >= 400 ) {
			$issues[] = __( 'Admin AJAX endpoint issues detected (bulk edit may fail)', 'wpshadow' );
		}

		// Check for database connection issues that could cause partial updates.
		if ( ! $wpdb->check_connection( false ) ) {
			$issues[] = __( 'Unstable database connection (risk of partial bulk updates)', 'wpshadow' );
		}

		// Check for posts with very long content that could cause timeouts.
		$posts_long_content = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE LENGTH(post_content) > 100000
			AND post_status IN ('publish', 'draft', 'pending')"
		);

		if ( $posts_long_content > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have very long content (>100KB, may slow bulk operations)', 'wpshadow' ),
				$posts_long_content
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/bulk-edit-reliability',
			);
		}

		return null;
	}
}
