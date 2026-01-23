<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Cache Configuration
 *
 * Analyzes caching setup and identifies optimization opportunities.
 * Proper caching can reduce page load times by 50-80%.
 *
 * @since 1.2.0
 */
class Test_Cache_Configuration extends Diagnostic_Base
{

	/**
	 * Check cache configuration
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$cache_analysis = self::analyze_cache_setup();

		if ($cache_analysis['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $cache_analysis['threat_level'],
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => $cache_analysis['issue'],
			'metadata'        => $cache_analysis,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-caching-setup/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-caching-performance/',
		];
	}

	/**
	 * Guardian Sub-Test: Object cache
	 *
	 * @return array Test result
	 */
	public static function test_object_cache(): array
	{
		$cache_enabled = wp_using_ext_object_cache();

		$cache_type = 'None (Database)';
		if ($cache_enabled) {
			global $wp_object_cache;
			$cache_type = get_class($wp_object_cache) ?? 'Custom';
		}

		return [
			'test_name'     => 'Object Cache',
			'enabled'       => $cache_enabled,
			'cache_type'    => $cache_type,
			'passed'        => $cache_enabled,
			'description'   => $cache_enabled ? sprintf('Object cache enabled: %s', $cache_type) : 'Object cache not enabled (database used for caching)',
		];
	}

	/**
	 * Guardian Sub-Test: Page cache plugin
	 *
	 * @return array Test result
	 */
	public static function test_page_cache_plugin(): array
	{
		$active_plugins = get_plugins();

		$cache_plugins = [
			'wp-super-cache/wp-cache.php' => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
			'cache-enabler/cache-enabler.php' => 'Cache Enabler',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
		];

		$active_cache_plugin = null;
		foreach ($cache_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$active_cache_plugin = $plugin_name;
				break;
			}
		}

		return [
			'test_name'         => 'Page Cache Plugin',
			'has_cache_plugin'  => $active_cache_plugin !== null,
			'cache_plugin'      => $active_cache_plugin ?? 'None',
			'passed'            => $active_cache_plugin !== null,
			'description'       => $active_cache_plugin ?? 'No page cache plugin installed',
		];
	}

	/**
	 * Guardian Sub-Test: Transient usage
	 *
	 * @return array Test result
	 */
	public static function test_transient_usage(): array
	{
		global $wpdb;

		$transient_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'"
		);

		$expired_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < " . time()
		);

		$status = 'normal';
		if ($expired_transients > 100) {
			$status = 'needs_cleanup';
		}

		return [
			'test_name'           => 'Transient Usage',
			'total_transients'    => intval($transient_count),
			'expired_transients'  => intval($expired_transients),
			'status'              => $status,
			'passed'              => $status === 'normal',
			'description'         => sprintf('Total: %d transients, Expired: %d', intval($transient_count), intval($expired_transients)),
		];
	}

	/**
	 * Guardian Sub-Test: Cron stability
	 *
	 * @return array Test result
	 */
	public static function test_cron_stability(): array
	{
		$cron_events = _get_cron_array();

		if (! $cron_events) {
			return [
				'test_name'    => 'Cron Stability',
				'event_count'  => 0,
				'passed'       => true,
				'description'  => 'No scheduled cron events',
			];
		}

		$upcoming_count = 0;
		$overdue_count = 0;

		foreach ($cron_events as $timestamp => $crons) {
			if ($timestamp < time()) {
				$overdue_count += count($crons);
			} else {
				$upcoming_count += count($crons);
			}
		}

		$status = $overdue_count > 5 ? 'warning' : 'normal';

		return [
			'test_name'       => 'Cron Stability',
			'upcoming_events' => $upcoming_count,
			'overdue_events'  => $overdue_count,
			'status'          => $status,
			'passed'          => $status === 'normal',
			'description'     => sprintf('Upcoming: %d, Overdue: %d', $upcoming_count, $overdue_count),
		];
	}

	/**
	 * Analyze cache setup
	 *
	 * @return array Cache analysis
	 */
	private static function analyze_cache_setup(): array
	{
		$has_object_cache = wp_using_ext_object_cache();

		$active_plugins = get_plugins();
		$has_page_cache = false;

		$cache_plugins = [
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'cache-enabler/cache-enabler.php',
			'litespeed-cache/litespeed-cache.php',
		];

		foreach ($cache_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_page_cache = true;
				break;
			}
		}

		$threat_level = 0;
		$issue = 'Caching is properly configured';

		if (! $has_object_cache && ! $has_page_cache) {
			$threat_level = 50;
			$issue = 'No caching system detected - performance may suffer';
		} elseif ($has_page_cache && ! $has_object_cache) {
			$threat_level = 20;
			$issue = 'Page cache active, but no object cache - consider enabling Redis or Memcached';
		}

		return [
			'threat_level'      => $threat_level,
			'issue'             => $issue,
			'has_object_cache'  => $has_object_cache,
			'has_page_cache'    => $has_page_cache,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Cache Configuration';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Analyzes caching setup and identifies optimization opportunities';
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
