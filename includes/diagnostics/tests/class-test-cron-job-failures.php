<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Cron Job Failures
 *
 * Detects when WordPress cron jobs fail or aren't running properly.
 * Failed cron jobs prevent scheduled tasks from executing (backups, updates, etc).
 *
 * @since 1.2.0
 */
class Test_Cron_Job_Failures extends Diagnostic_Base
{

	/**
	 * Check for cron job issues
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$cron_issues = self::detect_cron_issues();

		if (empty($cron_issues)) {
			return null;
		}

		$threat = count($cron_issues) * 12;
		$threat = min(80, $threat);

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'orange',
			'passed'          => false,
			'issue'           => sprintf(
				'Detected %d cron job issues',
				count($cron_issues)
			),
			'metadata'        => [
				'issues_count' => count($cron_issues),
				'issues'       => $cron_issues,
			],
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-cron-jobs/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-scheduled-tasks/',
		];
	}

	/**
	 * Guardian Sub-Test: Loopback request capability
	 *
	 * @return array Test result
	 */
	public static function test_loopback_request(): array
	{
		$can_make_loopback = self::can_make_loopback_request();

		return [
			'test_name'          => 'Loopback Request Capability',
			'can_make_requests'  => $can_make_loopback,
			'passed'             => $can_make_loopback,
			'description'        => $can_make_loopback ? 'Can make loopback requests (cron capable)' : 'Cannot make loopback requests (cron issues)',
		];
	}

	/**
	 * Guardian Sub-Test: Scheduled events
	 *
	 * @return array Test result
	 */
	public static function test_scheduled_events(): array
	{
		$events = _get_cron_array();
		$event_count = is_array($events) ? count($events) : 0;
		$next_event = self::get_next_scheduled_event();

		$events_list = [];
		if (is_array($events)) {
			foreach (array_slice($events, 0, 10) as $timestamp => $crons) {
				foreach ($crons as $hook => $hook_data) {
					$events_list[] = [
						'hook'     => $hook,
						'timestamp' => $timestamp,
						'time'     => date('Y-m-d H:i:s', $timestamp),
						'interval' => $hook_data[0]['interval'] ?? 'N/A',
					];
				}
			}
		}

		return [
			'test_name'      => 'Scheduled Events',
			'total_events'   => $event_count,
			'next_event'     => $next_event,
			'sample_events'  => array_slice($events_list, 0, 5),
			'description'    => sprintf('%d scheduled events', $event_count),
		];
	}

	/**
	 * Guardian Sub-Test: Last cron run time
	 *
	 * @return array Test result
	 */
	public static function test_last_cron_run(): array
	{
		$last_run = get_option('_transient_doing_cron');
		$last_time = get_option('wpshadow_last_cron_run');

		$time_since = $last_time ? (time() - $last_time) : 'Never';
		$status = $time_since !== 'Never' && $time_since < 3600 ? 'Recent' : ($time_since === 'Never' ? 'Never run' : 'Stale');

		return [
			'test_name'      => 'Last Cron Run',
			'last_run_time'  => $last_time ? date('Y-m-d H:i:s', $last_time) : 'Unknown',
			'time_since'     => $time_since === 'Never' ? 'Never' : sprintf('%d seconds ago', $time_since),
			'status'         => $status,
			'passed'         => $status !== 'Never run',
			'description'    => 'Cron status: ' . $status,
		];
	}

	/**
	 * Guardian Sub-Test: Critical hook integrity
	 *
	 * @return array Test result
	 */
	public static function test_critical_hooks(): array
	{
		$critical_hooks = [
			'wp_version_check',
			'wp_update_plugins',
			'wp_update_themes',
		];

		$hooks_status = [];
		foreach ($critical_hooks as $hook) {
			$scheduled = wp_next_scheduled($hook);
			$hooks_status[] = [
				'hook'       => $hook,
				'scheduled'  => (bool) $scheduled,
				'next_run'   => $scheduled ? date('Y-m-d H:i:s', $scheduled) : 'Not scheduled',
			];
		}

		$all_scheduled = array_reduce($hooks_status, fn($c, $h) => $c && $h['scheduled'], true);

		return [
			'test_name'      => 'Critical Hooks',
			'hooks'          => $hooks_status,
			'all_scheduled'  => $all_scheduled,
			'passed'         => $all_scheduled,
			'description'    => $all_scheduled ? 'All critical hooks scheduled' : 'Some critical hooks missing',
		];
	}

	/**
	 * Detect cron-related issues
	 *
	 * @return array List of issues
	 */
	private static function detect_cron_issues(): array
	{
		$issues = [];

		// Check if loopback requests work
		if (! self::can_make_loopback_request()) {
			$issues[] = 'Cannot make loopback requests (required for wp-cron)';
		}

		// Check if DISABLE_WP_CRON is set
		if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
			$issues[] = 'WP_CRON is disabled - external cron required';
		}

		// Check if critical hooks are scheduled
		$critical_hooks = ['wp_version_check', 'wp_update_plugins', 'wp_update_themes'];
		foreach ($critical_hooks as $hook) {
			if (! wp_next_scheduled($hook)) {
				$issues[] = sprintf('Critical hook "%s" not scheduled', $hook);
			}
		}

		return $issues;
	}

	/**
	 * Check if loopback requests are possible
	 *
	 * @return bool True if loopback requests work
	 */
	private static function can_make_loopback_request(): bool
	{
		$response = wp_remote_post(admin_url('admin-ajax.php'), [
			'blocking'  => false,
			'sslverify' => apply_filters('https_local_ssl_verify', false),
		]);

		return ! is_wp_error($response);
	}

	/**
	 * Get next scheduled event
	 *
	 * @return string Next event info
	 */
	private static function get_next_scheduled_event(): string
	{
		$events = _get_cron_array();

		if (! is_array($events) || empty($events)) {
			return 'No events scheduled';
		}

		$next_timestamp = min(array_keys($events));
		$time_until = $next_timestamp - time();

		if ($time_until < 0) {
			return 'Overdue';
		}

		return sprintf('%s (in %d minutes)', date('H:i:s', $next_timestamp), ceil($time_until / 60));
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Cron Job Failures';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Detects issues with WordPress scheduled tasks (wp-cron)';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'System';
	}
}
