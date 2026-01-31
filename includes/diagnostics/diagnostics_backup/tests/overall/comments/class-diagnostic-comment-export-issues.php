<?php
/**
 * Comment Export Issues Diagnostic
 *
 * Checks if comments can be exported for backup and verifies export
 * functionality is working properly.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2206
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Export Issues Diagnostic Class
 *
 * Checks for:
 * - WordPress export tool functionality
 * - Large comment volumes that may fail export
 * - Comment attachments/metadata export
 * - Special characters that may break export
 *
 * @since 1.2601.2206
 */
class Diagnostic_Comment_Export_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-export-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Export Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comments can be exported for backup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2206
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		$issues = array();

		// Check total comment count.
		$total_comments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments}" );

		if ( $total_comments > 100000 ) {
			$issues[] = sprintf(
				__( 'Very large comment volume (%s comments) may cause export timeouts', 'wpshadow' ),
				number_format_i18n( $total_comments )
			);
		}

		// Check for comments with special characters.
		$special_char_comments = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->comments}
			WHERE comment_content REGEXP '[^\x20-\x7E\x0A\x0D]'"
		);

		if ( $special_char_comments > 0 ) {
			$issues[] = sprintf(
				__( '%s comments contain special characters that may need encoding', 'wpshadow' ),
				number_format_i18n( $special_char_comments )
			);
		}

		// Check for very long comments.
		$long_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->comments}
				WHERE LENGTH(comment_content) > %d",
				10000
			)
		);

		if ( $long_comments > 0 ) {
			$issues[] = sprintf(
				__( '%s comments exceed 10KB (may cause export issues)', 'wpshadow' ),
				number_format_i18n( $long_comments )
			);
		}

		// Check comment meta size.
		$meta_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(meta_value))
			FROM {$wpdb->commentmeta}"
		);

		if ( $meta_size > 10 * 1024 * 1024 ) {
			$issues[] = sprintf(
				__( 'Comment metadata is %s MB (large size may slow exports)', 'wpshadow' ),
				number_format( $meta_size / ( 1024 * 1024 ), 2 )
			);
		}

		// Check if export file exists.
		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/exports';
		if ( ! file_exists( $export_dir ) || ! is_writable( $export_dir ) ) {
			// Check if uploads directory is writable.
			if ( ! is_writable( $upload_dir['basedir'] ) ) {
				$issues[] = __( 'Uploads directory not writable (exports will fail)', 'wpshadow' );
			}
		}

		// Check PHP memory limit for exports.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		// Estimate memory needed (rough: 1MB per 1000 comments).
		$estimated_memory = ( $total_comments / 1000 ) * 1024 * 1024;

		if ( $memory_bytes < $estimated_memory ) {
			$issues[] = sprintf(
				__( 'PHP memory limit (%s) may be insufficient for comment export (estimated need: %s)', 'wpshadow' ),
				$memory_limit,
				size_format( $estimated_memory )
			);
		}

		// Check max_execution_time.
		$max_execution_time = ini_get( 'max_execution_time' );
		if ( $max_execution_time > 0 && $max_execution_time < 300 && $total_comments > 10000 ) {
			$issues[] = sprintf(
				__( 'max_execution_time (%ds) may be too low for large comment export', 'wpshadow' ),
				$max_execution_time
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-export-issues',
		);
	}
}
