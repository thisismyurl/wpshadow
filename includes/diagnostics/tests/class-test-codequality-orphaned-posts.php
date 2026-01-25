<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_CodeQuality_Orphaned_Posts extends Diagnostic_Base {


	public static function check(): ?array {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_parent > 0 AND post_parent NOT IN (SELECT ID FROM {$wpdb->posts})"
		);

		if ( $orphaned > 10 ) {
			return array(
				'id'           => 'orphaned-posts',
				'title'        => sprintf( '%d Orphaned Posts', $orphaned ),
				'description'  => 'Posts with missing parent pages should be cleaned up.',
				'threat_level' => 25,
			);
		}
		return null;
	}

	public static function test_live_orphaned_posts(): array {
		global $wpdb;
		$result   = self::check();
		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_parent > 0 AND post_parent NOT IN (SELECT ID FROM {$wpdb->posts})"
		);

		if ( $orphaned > 10 ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Orphaned posts exist, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Orphaned post count OK, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Orphaned posts check passed.',
		);
	}
}
