<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Performance
 *
 * Monitors database query performance and identifies slow queries.
 * Slow database queries significantly impact page load times.
 *
 * @since 1.2.0
 */
class Test_Database_Performance extends Diagnostic_Base
{

	/**
	 * Check database performance
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$db_performance = self::analyze_database_performance();

		if ($db_performance['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $db_performance['threat_level'],
			'threat_color'    => 'orange',
			'passed'          => false,
			'issue'           => $db_performance['issue'],
			'metadata'        => $db_performance,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-database-optimization/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-database-performance/',
		];
	}

	/**
	 * Guardian Sub-Test: Database size
	 *
	 * @return array Test result
	 */
	public static function test_database_size(): array
	{
		global $wpdb;

		$db_size_bytes = 0;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = %s",
				DB_NAME
			)
		);

		if ($results && isset($results[0]->size)) {
			$db_size_bytes = intval($results[0]->size);
		}

		$db_size_mb = round($db_size_bytes / (1024 * 1024), 2);

		$status = 'normal';
		if ($db_size_mb > 1000) {
			$status = 'large';
		} elseif ($db_size_mb > 500) {
			$status = 'moderate';
		}

		return [
			'test_name'    => 'Database Size',
			'size_bytes'   => $db_size_bytes,
			'size_mb'      => $db_size_mb,
			'status'       => $status,
			'passed'       => $status === 'normal',
			'description'  => sprintf('Database size: %.2f MB', $db_size_mb),
		];
	}

	/**
	 * Guardian Sub-Test: Table analysis
	 *
	 * @return array Test result
	 */
	public static function test_table_analysis(): array
	{
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT table_name, (data_length + index_length) as size, table_rows FROM information_schema.TABLES WHERE table_schema = %s ORDER BY size DESC LIMIT 5",
				DB_NAME
			)
		);

		$large_tables = [];
		foreach ($results as $table) {
			$size_mb = round($table->size / (1024 * 1024), 2);
			if ($size_mb > 10) {
				$large_tables[] = [
					'table'  => $table->table_name,
					'size'   => $size_mb,
					'rows'   => $table->table_rows,
				];
			}
		}

		return [
			'test_name'      => 'Table Analysis',
			'large_tables'   => $large_tables,
			'table_count'    => count($results),
			'passed'         => count($large_tables) < 2,
			'description'    => sprintf('%d tables, %d over 10MB', count($results), count($large_tables)),
		];
	}

	/**
	 * Guardian Sub-Test: Query performance (if SAVEQUERIES enabled)
	 *
	 * @return array Test result
	 */
	public static function test_query_performance(): array
	{
		if (! defined('SAVEQUERIES') || ! SAVEQUERIES) {
			return [
				'test_name'    => 'Query Performance',
				'enabled'      => false,
				'passed'       => true,
				'description'  => 'SAVEQUERIES not enabled for analysis',
			];
		}

		global $wpdb;

		$slow_queries = [];
		foreach ($wpdb->queries as $query) {
			if ($query[1] > 1) { // Queries taking > 1 second
				$slow_queries[] = [
					'query' => substr($query[0], 0, 100),
					'time'  => round($query[1], 4),
				];
			}
		}

		return [
			'test_name'     => 'Query Performance',
			'enabled'       => true,
			'total_queries' => count($wpdb->queries),
			'slow_queries'  => count($slow_queries),
			'passed'        => count($slow_queries) === 0,
			'description'   => sprintf('%d queries, %d slow (>1s)', count($wpdb->queries), count($slow_queries)),
		];
	}

	/**
	 * Guardian Sub-Test: Index coverage
	 *
	 * @return array Test result
	 */
	public static function test_index_coverage(): array
	{
		global $wpdb;

		// Get tables and check for primary keys
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT table_name FROM information_schema.TABLES WHERE table_schema = %s AND table_type = 'BASE TABLE'",
				DB_NAME
			)
		);

		$tables_without_pk = [];

		foreach ($results as $table) {
			$has_pk = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM information_schema.STATISTICS WHERE table_schema = %s AND table_name = %s AND index_name = 'PRIMARY'",
					DB_NAME,
					$table->table_name
				)
			);

			if (! $has_pk) {
				$tables_without_pk[] = $table->table_name;
			}
		}

		return [
			'test_name'          => 'Index Coverage',
			'total_tables'       => count($results),
			'tables_without_pk'  => $tables_without_pk,
			'passed'             => empty($tables_without_pk),
			'description'        => empty($tables_without_pk) ? 'All tables have primary keys' : sprintf('%d tables missing primary keys', count($tables_without_pk)),
		];
	}

	/**
	 * Analyze database performance
	 *
	 * @return array Performance analysis
	 */
	private static function analyze_database_performance(): array
	{
		global $wpdb;

		$db_size_bytes = 0;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = %s",
				DB_NAME
			)
		);

		if ($results && isset($results[0]->size)) {
			$db_size_bytes = intval($results[0]->size);
		}

		$db_size_mb = round($db_size_bytes / (1024 * 1024), 2);

		$threat_level = 0;
		$issue = 'Database performance is acceptable';

		if ($db_size_mb > 1000) {
			$threat_level = 40;
			$issue = sprintf('Large database (%.2f MB) - consider optimization', $db_size_mb);
		} elseif ($db_size_mb > 500) {
			$threat_level = 20;
			$issue = sprintf('Medium database (%.2f MB)', $db_size_mb);
		}

		return [
			'threat_level' => $threat_level,
			'issue'        => $issue,
			'db_size_mb'   => $db_size_mb,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Database Performance';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Monitors database query performance and identifies optimization opportunities';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Performance';
	}
}
