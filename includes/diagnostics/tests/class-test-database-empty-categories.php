<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Empty Categories
 * Checks for categories with no posts
 */
class Test_Database_Empty_Categories extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$empty_categories = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->terms} t
			 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			 LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			 WHERE tt.taxonomy = 'category' AND tr.object_id IS NULL"
		);

		if ($empty_categories > 20) {
			return array(
				'id'            => 'database-empty-categories',
				'title'         => 'Many Empty Categories',
				'threat_level'  => 15,
				'description'   => sprintf(
					'Found %d empty categories with no associated posts.',
					$empty_categories
				),
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_empty_categories(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Category cleanup is current' : 'Empty categories detected',
		);
	}
}
