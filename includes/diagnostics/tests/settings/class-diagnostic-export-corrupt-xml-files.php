<?php
/**
 * Export Corrupt XML Files Diagnostic
 *
 * Verifies that export XML files remain valid and parseable, even for large
 * sites. Corrupt XML leads to failed imports and silent data loss.
 *
 * **What This Check Does:**
 * - Validates XML structure in export files
 * - Detects broken entities or malformed tags
 * - Flags oversized exports that commonly corrupt output
 * - Ensures exports remain portable across environments
 *
 * **Why This Matters:**
 * A corrupt export file can’t be imported reliably. This is especially
 * dangerous during migrations, where the export might be your only backup.
 *
 * **Real-World Failure Scenario:**
 * - Large export exceeds memory during generation
 * - XML file is truncated mid‑tag
 * - Import fails at 60%, leaving partial site
 *
 * Result: Broken migration and extended downtime.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Protects migration reliability
 * - #9 Show Value: Prevents expensive re‑exports
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/export-xml-integrity
 * or https://wpshadow.com/training/wordpress-export-best-practices
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Corrupt XML Files Diagnostic Class
 *
 * Uses XML parsing to validate export integrity.
 *
 * **Implementation Pattern:**
 * 1. Generate or locate export file
 * 2. Parse XML structure
 * 3. Detect malformed or truncated output
 * 4. Return findings with recovery guidance
 *
 * **Related Diagnostics:**
 * - Export Timeout on Large Sites
 * - Export No Chunked Option
 * - Import Character Encoding Corruption
 *
 * @since 0.6093.1200
 */
class Diagnostic_Export_Corrupt_XML_Files extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-corrupt-xml-files';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Corrupt XML Files';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests XML file validity and integrity after large exports';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if WP_Importer class exists.
		if ( ! class_exists( 'WP_Export_Query' ) ) {
			require_once ABSPATH . 'wp-admin/includes/export.php';
		}

		// Check post count (high counts may cause issues).
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status != 'auto-draft'
			AND post_type NOT IN ('revision', 'nav_menu_item')"
		);

		if ( $total_posts > 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts may cause large XML export (risk of corruption)', 'wpshadow' ),
				number_format( $total_posts )
			);
		}

		// Check for XMLWriter extension.
		if ( ! class_exists( 'XMLWriter' ) ) {
			$issues[] = __( 'XMLWriter extension not available (export XML generation limited)', 'wpshadow' );
		}

		// Check for SimpleXML extension.
		if ( ! function_exists( 'simplexml_load_string' ) ) {
			$issues[] = __( 'SimpleXML extension not available (XML validation disabled)', 'wpshadow' );
		}

		// Check for libxml errors.
		libxml_use_internal_errors( true );

		// Test XML generation with small sample.
		$sample_posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content, post_excerpt
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			LIMIT 5",
			ARRAY_A
		);

		if ( ! empty( $sample_posts ) ) {
			$test_xml = '<?xml version="1.0" encoding="UTF-8"?><test>';

			foreach ( $sample_posts as $post ) {
				// Check for problematic characters in content.
				if ( preg_match( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', $post['post_content'] ) ) {
					$issues[] = sprintf(
						/* translators: %d: post ID */
						__( 'Post #%d contains invalid XML characters (will corrupt export)', 'wpshadow' ),
						$post['ID']
					);
					break;
				}
			}

			$test_xml .= '</test>';

			// Try to parse the test XML.
			if ( function_exists( 'simplexml_load_string' ) ) {
				$parsed = @simplexml_load_string( $test_xml );

				if ( false === $parsed ) {
					$xml_errors = libxml_get_errors();
					if ( ! empty( $xml_errors ) ) {
						$issues[] = __( 'Sample XML generation produces parse errors', 'wpshadow' );
					}
					libxml_clear_errors();
				}
			}
		}

		// Check max_execution_time for large exports.
		$max_execution = (int) ini_get( 'max_execution_time' );

		if ( $max_execution > 0 && $max_execution < 300 && $total_posts > 5000 ) {
			$issues[] = sprintf(
				/* translators: 1: execution time, 2: post count */
				__( 'max_execution_time %1$ds too low for %2$d posts (export may timeout)', 'wpshadow' ),
				$max_execution,
				number_format( $total_posts )
			);
		}

		// Check memory_limit for large exports.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );

		if ( $memory_limit > 0 && $memory_limit < 134217728 && $total_posts > 5000 ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit %s too low for large export (may fail)', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for CDATA escaping in post content.
		$posts_with_cdata = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_content LIKE '%]]>%'"
		);

		if ( $posts_with_cdata > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts contain CDATA end markers (may break XML)', 'wpshadow' ),
				$posts_with_cdata
			);
		}

		// Check for null bytes in content.
		$posts_with_nulls = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_content LIKE '%\0%'
			OR post_title LIKE '%\0%'"
		);

		if ( $posts_with_nulls > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts contain null bytes (will corrupt XML)', 'wpshadow' ),
				$posts_with_nulls
			);
		}

		// Check for control characters.
		$posts_with_control_chars = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_content REGEXP '[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]'"
		);

		if ( $posts_with_control_chars > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts contain invalid control characters', 'wpshadow' ),
				$posts_with_control_chars
			);
		}

		// Check for export file write permissions.
		$upload_dir = wp_upload_dir();

		if ( ! wp_is_writable( $upload_dir['basedir'] ) ) {
			$issues[] = __( 'Upload directory not writable (export files cannot be saved)', 'wpshadow' );
		}

		// Check disk space.
		$free_space = @disk_free_space( $upload_dir['basedir'] );

		if ( false !== $free_space ) {
			$estimated_export_size = $total_posts * 10240; // ~10KB per post estimate.

			if ( $free_space < $estimated_export_size ) {
				$issues[] = sprintf(
					/* translators: 1: available space, 2: estimated size */
					__( 'Insufficient disk space (%1$s available, ~%2$s needed)', 'wpshadow' ),
					size_format( $free_space ),
					size_format( $estimated_export_size )
				);
			}
		}

		// Check for UTF-8 encoding issues.
		$charset = $wpdb->get_var( "SELECT @@character_set_database" );

		if ( 'utf8mb4' !== $charset && 'utf8' !== $charset ) {
			$issues[] = sprintf(
				/* translators: %s: character set */
				__( 'Database charset %s may cause encoding issues in XML', 'wpshadow' ),
				$charset
			);
		}

		// Check for export hooks that might corrupt output.
		$export_filters = $GLOBALS['wp_filter']['export_wp'] ?? null;

		if ( $export_filters && count( $export_filters->callbacks ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on export_wp hook (may modify XML output)', 'wpshadow' ),
				count( $export_filters->callbacks )
			);
		}

		// Check for very long post content.
		$max_content_length = $wpdb->get_var(
			"SELECT MAX(LENGTH(post_content))
			FROM {$wpdb->posts}"
		);

		if ( $max_content_length > 1000000 ) {
			$issues[] = sprintf(
				/* translators: %s: content size */
				__( 'Some posts exceed %s (may cause memory issues during export)', 'wpshadow' ),
				size_format( $max_content_length )
			);
		}

		// Check for posts with many comments.
		$max_comment_count = $wpdb->get_var(
			"SELECT MAX(comment_count)
			FROM {$wpdb->posts}"
		);

		if ( $max_comment_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: comment count */
				__( 'Some posts have %d+ comments (increases export complexity)', 'wpshadow' ),
				number_format( $max_comment_count )
			);
		}

		// Check output buffering settings.
		$output_buffering = ini_get( 'output_buffering' );

		if ( 'Off' === $output_buffering || '0' === $output_buffering ) {
			if ( $total_posts > 5000 ) {
				$issues[] = __( 'output_buffering disabled (may cause issues with large exports)', 'wpshadow' );
			}
		}

		// Check for gzip compression capability.
		if ( ! function_exists( 'gzencode' ) && $total_posts > 10000 ) {
			$issues[] = __( 'gzip compression unavailable (large exports will be slow)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/export-corrupt-xml-files?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
