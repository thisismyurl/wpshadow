<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Unoptimized Database Queries (Performance)
 *
 * Checks if database query optimization is needed
 * Philosophy: Show value (#9) - optimized DB speeds up pages
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_UnoptimizedQueries extends Diagnostic_Base {


	public static function check(): ?array {
		global $wpdb;

		// Check for posts with missing metadata which causes repeated queries
		$orphaned_meta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
            WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})"
		);

		if ( $orphaned_meta > 50 ) {
			return array(
				'id'           => 'unoptimized-queries',
				'title'        => sprintf( __( '%d orphaned post metadata records found', 'wpshadow' ), $orphaned_meta ),
				'description'  => __( 'Orphaned metadata causes extra database queries. Clean up unused data to improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
			);
		}

		return null;
	}

	public static function test_live_unoptimized_queries(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Database is optimized', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
