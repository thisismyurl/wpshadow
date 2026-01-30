<?php
/**
 * bbPress Topic Freshness Caching Diagnostic
 *
 * bbPress topic freshness calculations slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.241.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Topic Freshness Caching Diagnostic Class
 *
 * @since 1.241.0000
 */
class Diagnostic_BbpressTopicFreshness extends Diagnostic_Base {

	protected static $slug = 'bbpress-topic-freshness';
	protected static $title = 'bbPress Topic Freshness Caching';
	protected static $description = 'bbPress topic freshness calculations slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}
		
		// Check if bbPress is active
		if ( ! class_exists( 'bbPress' ) && ! function_exists( 'bbp_get_version' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check topic count
		$topic_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'topic'"
		);

		if ( $topic_count > 1000 ) {
			// Check freshness caching
			$cache_enabled = get_option( '_bbp_enable_topic_cache', 1 );
			if ( ! $cache_enabled ) {
				$issues[] = 'topic_cache_disabled';
				$threat_level += 25;
			}

			// Check freshness meta queries
			$freshness_keys = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				 WHERE meta_key = '_bbp_last_active_time'"
			);
			if ( $freshness_keys < $topic_count * 0.8 ) {
				$issues[] = 'missing_freshness_metadata';
				$threat_level += 20;
			}
		}

		// Check reply counting
		$reply_count_cache = get_option( '_bbp_enable_reply_count', 1 );
		if ( ! $reply_count_cache ) {
			$issues[] = 'reply_count_cache_disabled';
			$threat_level += 15;
		}

		// Check database indexes
		$indexes = $wpdb->get_results(
			"SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name = 'meta_key'"
		);
		if ( empty( $indexes ) ) {
			$issues[] = 'postmeta_indexes_missing';
			$threat_level += 20;
		}

		// Check topic status queries
		$status_queries = get_option( '_bbp_optimize_status_queries', 0 );
		if ( ! $status_queries && $topic_count > 5000 ) {
			$issues[] = 'status_query_optimization_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of performance issues */
				__( 'bbPress topic freshness has performance issues: %s. This causes slow forum page loads and database overhead.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-topic-freshness',
			);
		}
		
		return null;
	}
}
