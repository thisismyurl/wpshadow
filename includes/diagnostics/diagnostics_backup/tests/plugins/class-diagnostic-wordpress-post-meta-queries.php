<?php
/**
 * Wordpress Post Meta Queries Diagnostic
 *
 * Wordpress Post Meta Queries issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1281.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Post Meta Queries Diagnostic Class
 *
 * @since 1.1281.0000
 */
class Diagnostic_WordpressPostMetaQueries extends Diagnostic_Base {

	protected static $slug = 'wordpress-post-meta-queries';
	protected static $title = 'Wordpress Post Meta Queries';
	protected static $description = 'Wordpress Post Meta Queries issue detected';
	protected static $family = 'functionality';

	public static function check() {
		global $wpdb;
		$issues = array();
		
		// Check 1: Orphaned postmeta
		$orphaned = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 WHERE p.ID IS NULL"
		);
		
		if ( $orphaned > 100 ) {
			$issues[] = sprintf( __( '%d orphaned postmeta entries (no matching posts)', 'wpshadow' ), $orphaned );
		}
		
		// Check 2: Postmeta table size
		$table_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((data_length + index_length) / 1024 / 1024, 2)
				 FROM information_schema.tables
				 WHERE table_schema = %s AND table_name = %s",
				DB_NAME,
				$wpdb->postmeta
			)
		);
		
		if ( $table_size > 1000 ) {
			$issues[] = sprintf( __( 'Postmeta table: %.2f MB (optimization needed)', 'wpshadow' ), $table_size );
		}
		
		// Check 3: Meta key indexes
		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->postmeta} WHERE Column_name = 'meta_key'" );
		if ( empty( $indexes ) ) {
			$issues[] = __( 'Missing meta_key index (slow meta queries)', 'wpshadow' );
		}
		
		// Check 4: Large serialized meta values
		$large_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE LENGTH(meta_value) > 10000"
		);
		
		if ( $large_meta > 50 ) {
			$issues[] = sprintf( __( '%d postmeta entries with large values (>10KB)', 'wpshadow' ), $large_meta );
		}
		
		// Check 5: Duplicate meta entries
		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				 SELECT post_id, meta_key, COUNT(*) as cnt
				 FROM {$wpdb->postmeta}
				 WHERE meta_key NOT LIKE '\\_%%'
				 GROUP BY post_id, meta_key
				 HAVING cnt > 1
			 ) as dupes"
		);
		
		if ( $duplicates > 20 ) {
			$issues[] = sprintf( __( '%d duplicate meta key entries found', 'wpshadow' ), $duplicates );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of meta query issues */
				__( 'WordPress postmeta queries have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-post-meta-queries',
		);
	}
}
