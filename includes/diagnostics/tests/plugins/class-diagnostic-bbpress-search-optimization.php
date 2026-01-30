<?php
/**
 * bbPress Search Optimization Diagnostic
 *
 * bbPress search functionality not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.242.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Search Optimization Diagnostic Class
 *
 * @since 1.242.0000
 */
class Diagnostic_BbpressSearchOptimization extends Diagnostic_Base {

	protected static $slug = 'bbpress-search-optimization';
	protected static $title = 'bbPress Search Optimization';
	protected static $description = 'bbPress search functionality not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check forum/topic count for search optimization
		global $wpdb;
		$topic_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'topic'
			)
		);
		
		$reply_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'reply'
			)
		);
		
		$total_content = $topic_count + $reply_count;
		
		if ( $total_content > 5000 ) {
			// Check if search index exists
			$search_index = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_bbp_search_index'"
			);
			
			if ( $search_index < ( $total_content * 0.5 ) ) {
				$issues[] = 'search index not built for large forum database';
			}
		}
		
		// Check for default WordPress search
		$custom_search = get_option( '_bbp_use_wp_search', '1' );
		if ( '1' === $custom_search && $total_content > 1000 ) {
			$issues[] = 'using default WordPress search with large forum (slow queries)';
		}
		
		// Check for fulltext search indexes on database
		$fulltext_indexes = $wpdb->get_results(
			"SHOW INDEX FROM {$wpdb->posts} WHERE Index_type = 'FULLTEXT'"
		);
		
		if ( empty( $fulltext_indexes ) && $total_content > 2000 ) {
			$issues[] = 'no fulltext indexes on posts table (impacts search speed)';
		}
		
		// Check for search results per page setting
		$per_page = get_option( '_bbp_topics_per_page', 15 );
		if ( $per_page > 50 ) {
			$issues[] = "excessive search results per page ({$per_page}, slows rendering)";
		}
		
		// Check for closed/old topic exclusion
		$exclude_closed = get_option( '_bbp_exclude_closed_from_search', '0' );
		if ( '0' === $exclude_closed && $topic_count > 1000 ) {
			$issues[] = 'closed topics not excluded from search results';
		}
		
		// Check for spam filtering in search
		$spam_filtered = get_option( '_bbp_filter_spam_from_search', '1' );
		if ( '0' === $spam_filtered ) {
			$issues[] = 'spam content not filtered from search (quality/performance issue)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'bbPress forum search optimization issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-search-optimization',
			);
		}
		
		return null;
	}
}
