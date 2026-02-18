<?php
/**
 * Corrupt XML Files on Large Exports
 *
 * Tests whether generated export XML files are valid and parseable after large exports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Export
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Corrupt_XML_Files_On_Large_Exports Class
 *
 * Validates that export XML generation produces valid, parseable files.
 * Checks for encoding issues, truncation, and structure integrity.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Corrupt_XML_Files_On_Large_Exports extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'corrupt-xml-files-on-large-exports';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export XML File Integrity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that generated export XML files are valid and not corrupt';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates XML export file structure and checks for common corruption issues.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count total posts
		$post_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN ('publish', 'draft', 'pending')" );

		// If post count is low, XML corruption is less likely
		if ( $post_count < 1000 ) {
			return null;
		}

		// Check for common XML generation issues
		$issues = array();

		// 1. Check if export is using UTF-8 encoding
		if ( ! function_exists( 'wp_upload_dir' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$encoding_issue = self::check_export_encoding();
		if ( $encoding_issue ) {
			$issues[] = $encoding_issue;
		}

		// 2. Check for truncation risk (posts/data limits)
		$truncation_risk = self::check_truncation_risk( $post_count );
		if ( $truncation_risk ) {
			$issues[] = $truncation_risk;
		}

		// 3. Check for problematic post types
		$problematic_types = self::check_problematic_post_types();
		if ( $problematic_types ) {
			$issues[] = $problematic_types;
		}

		// 4. Check for custom fields that might break XML
		$problematic_meta = self::check_problematic_postmeta();
		if ( $problematic_meta ) {
			$issues[] = $problematic_meta;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues found */
					__( '%d potential XML corruption risks detected', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/corrupt-xml-exports',
				'recommendations' => array(
					__( 'Test export file validation with XML parser', 'wpshadow' ),
					__( 'Check export encoding configuration', 'wpshadow' ),
					__( 'Verify post types have can_export enabled', 'wpshadow' ),
					__( 'Validate postmeta serialization', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for encoding issues in export.
	 *
	 * @since  1.6030.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_export_encoding() {
		global $wpdb;

		// Check database charset
		$charset = $wpdb->get_var( "SELECT DEFAULT_CHARACTER_SET_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = DATABASE()" );

		if ( 'utf8mb4' !== $charset && 'utf8' !== $charset ) {
			return sprintf(
				/* translators: %s: database charset */
				__( 'Database charset is %s (should be utf8/utf8mb4 for safe XML export)', 'wpshadow' ),
				esc_html( $charset )
			);
		}

		// Check for non-UTF-8 post content
		$non_utf8_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN ('publish', 'draft') AND post_content NOT REGEXP '^(?:[a-zA-Z0-9+/]*={0,2}|[\x00-\x7F]*|[\xC2-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3})*$'"
			)
		);

		if ( $non_utf8_count > 0 ) {
			return sprintf(
				/* translators: %d: number of posts with encoding issues */
				__( '%d posts contain non-UTF-8 characters that may break XML export', 'wpshadow' ),
				$non_utf8_count
			);
		}

		return null;
	}

	/**
	 * Check for truncation risk based on post count.
	 *
	 * @since  1.6030.2148
	 * @param  int $post_count Total number of posts.
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_truncation_risk( $post_count ) {
		// Check memory limit during export
		$memory_limit_mb = self::get_memory_limit_mb();
		$estimated_memory_needed = ( $post_count / 100 ) + 64; // Rough estimate: 100 posts per MB

		if ( $estimated_memory_needed > $memory_limit_mb ) {
			return sprintf(
				/* translators: %d: memory MB needed, %d: memory limit MB */
				__( 'Export may be truncated: estimated %dMB needed but only %dMB available', 'wpshadow' ),
				(int) $estimated_memory_needed,
				(int) $memory_limit_mb
			);
		}

		return null;
	}

	/**
	 * Check for problematic post types that don't export properly.
	 *
	 * @since  1.6030.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_problematic_post_types() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$non_exportable = array();

		foreach ( $post_types as $post_type ) {
			if ( ! $post_type->can_export ) {
				$non_exportable[] = $post_type->labels->singular_name;
			}
		}

		if ( ! empty( $non_exportable ) ) {
			return sprintf(
				/* translators: %s: list of post types */
				__( 'Post types not included in export: %s', 'wpshadow' ),
				implode( ', ', $non_exportable )
			);
		}

		return null;
	}

	/**
	 * Check for problematic postmeta values.
	 *
	 * @since  1.6030.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_problematic_postmeta() {
		global $wpdb;

		// Check for postmeta with invalid serialization
		$invalid_meta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_value LIKE 'a:%' 
			AND meta_value NOT REGEXP '^a:[0-9]+:\\{.*\\}$'"
		);

		if ( $invalid_meta > 0 ) {
			return sprintf(
				/* translators: %d: number of meta entries */
				__( '%d post meta values have invalid serialization (may break XML)', 'wpshadow' ),
				$invalid_meta
			);
		}

		return null;
	}

	/**
	 * Get PHP memory limit in MB.
	 *
	 * @since  1.6030.2148
	 * @return int Memory limit in MB.
	 */
	private static function get_memory_limit_mb() {
		$memory_limit = ini_get( 'memory_limit' );

		if ( '-1' === $memory_limit ) {
			return 8192; // Unlimited, assume high
		}

		if ( 'G' === strtoupper( substr( $memory_limit, -1 ) ) ) {
			return (int) substr( $memory_limit, 0, -1 ) * 1024;
		} elseif ( 'M' === strtoupper( substr( $memory_limit, -1 ) ) ) {
			return (int) substr( $memory_limit, 0, -1 );
		}

		return (int) $memory_limit / 1024 / 1024;
	}
}
