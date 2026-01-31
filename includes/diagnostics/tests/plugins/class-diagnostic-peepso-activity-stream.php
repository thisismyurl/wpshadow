<?php
/**
 * PeepSo Activity Stream Diagnostic
 *
 * PeepSo activity queries inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.520.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PeepSo Activity Stream Diagnostic Class
 *
 * @since 1.520.0000
 */
class Diagnostic_PeepsoActivityStream extends Diagnostic_Base {

	protected static $slug = 'peepso-activity-stream';
	protected static $title = 'PeepSo Activity Stream';
	protected static $description = 'PeepSo activity queries inefficient';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'PeepSo' ) ) {
			return null;
		}
		
		// Check if PeepSo is active
		if ( ! class_exists( 'PeepSo' ) && ! defined( 'PEEPSO_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check activity posts volume
		$activity_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'peepso-post'"
		);

		if ( $activity_posts > 10000 ) {
			// Check query caching
			$query_cache = get_option( 'peepso_activity_cache_enabled', 1 );
			if ( ! $query_cache ) {
				$issues[] = 'activity_cache_disabled';
				$threat_level += 30;
			}

			// Check pagination
			$posts_per_page = get_option( 'peepso_activity_posts_per_page', 10 );
			if ( $posts_per_page > 25 ) {
				$issues[] = 'excessive_posts_per_page';
				$threat_level += 25;
			}
		}

		// Check database indexes
		$table_name = $wpdb->prefix . 'peepso_activities';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
		if ( $table_exists ) {
			$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
			$has_act_index = false;
			foreach ( $indexes as $index ) {
				if ( $index->Key_name === 'act_external_id' ) {
					$has_act_index = true;
					break;
				}
			}
			if ( ! $has_act_index ) {
				$issues[] = 'missing_activity_indexes';
				$threat_level += 30;
			}
		}

		// Check old activity cleanup
		$cleanup_enabled = get_option( 'peepso_activity_cleanup_enabled', 0 );
		if ( ! $cleanup_enabled && $activity_posts > 50000 ) {
			$issues[] = 'activity_cleanup_disabled';
			$threat_level += 20;
		}

		// Check lazy loading
		$lazy_load = get_option( 'peepso_activity_lazy_load', 1 );
		if ( ! $lazy_load ) {
			$issues[] = 'lazy_loading_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of performance issues */
				__( 'PeepSo activity stream has performance problems: %s. This causes slow page loads and poor user experience.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/peepso-activity-stream',
			);
		}
		
		return null;
	}
}
