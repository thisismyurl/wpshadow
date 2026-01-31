<?php
/**
 * Media Library Assistant Query Performance Diagnostic
 *
 * Media Library Assistant Query Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.774.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Library Assistant Query Performance Diagnostic Class
 *
 * @since 1.774.0000
 */
class Diagnostic_MediaLibraryAssistantQueryPerformance extends Diagnostic_Base {

	protected static $slug = 'media-library-assistant-query-performance';
	protected static $title = 'Media Library Assistant Query Performance';
	protected static $description = 'Media Library Assistant Query Performance detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'MLACore' ) && ! defined( 'MLA_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify query caching is enabled
		$query_cache = get_option( 'mla_query_cache', 0 );
		if ( ! $query_cache ) {
			$issues[] = 'Query caching not enabled';
		}

		// Check 2: Check for thumbnail generation optimization
		$thumb_optimization = get_option( 'mla_thumbnail_generation', '' );
		if ( $thumb_optimization !== 'on_demand' ) {
			$issues[] = 'Thumbnail generation not optimized (use on-demand)';
		}

		// Check 3: Verify database index optimization
		$db_indexes = get_option( 'mla_custom_field_indexes', 0 );
		if ( ! $db_indexes ) {
			$issues[] = 'Database indexes not optimized for custom fields';
		}

		// Check 4: Check for IPTC/EXIF processing load
		$metadata_processing = get_option( 'mla_iptc_exif_standard_mapping', '' );
		if ( $metadata_processing === 'on_upload' ) {
			$issues[] = 'IPTC/EXIF processing on upload (causes delays)';
		}

		// Check 5: Verify attachment count caching
		$count_cache = get_option( 'mla_attachment_count_cache', 0 );
		if ( ! $count_cache ) {
			$issues[] = 'Attachment count caching not enabled';
		}

		// Check 6: Check for bulk processing limits
		$bulk_limit = get_option( 'mla_bulk_action_chunk_size', 0 );
		if ( $bulk_limit <= 0 || $bulk_limit > 100 ) {
			$issues[] = 'Bulk action chunk size not optimized (recommend 50-100)';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Media Library Assistant performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/media-library-assistant-query-performance',
			);
		}

		return null;
	}
}
