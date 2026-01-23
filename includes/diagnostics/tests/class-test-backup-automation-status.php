<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Backup Automation Status
 *
 * Monitors backup plugin status and backup frequency.
 * Absence of regular automated backups puts entire site at critical risk.
 *
 * @since 1.2.0
 */
class Test_Backup_Automation_Status extends Diagnostic_Base
{

	/**
	 * Check backup automation status
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$backup_status = self::analyze_backup_automation();

		if ($backup_status['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $backup_status['threat_level'],
			'threat_color'    => $backup_status['threat_color'],
			'passed'          => false,
			'issue'           => $backup_status['issue'],
			'metadata'        => $backup_status,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-backup-strategy/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-disaster-recovery/',
		];
	}

	/**
	 * Guardian Sub-Test: Backup plugin detection
	 *
	 * @return array Test result
	 */
	public static function test_backup_plugin(): array
	{
		$active_plugins = get_plugins();

		$backup_plugins = [
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php' => 'BackWPup',
			'backupbuddy/backupbuddy.php' => 'BackupBuddy',
			'jetpack/jetpack.php' => 'Jetpack Backup',
		];

		$active_backup = null;
		foreach ($backup_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$active_backup = $plugin_name;
				break;
			}
		}

		return [
			'test_name'     => 'Backup Plugin',
			'active_plugin' => $active_backup,
			'passed'        => $active_backup !== null,
			'description'   => $active_backup ?? 'No backup plugin installed',
		];
	}

	/**
	 * Guardian Sub-Test: Last backup age
	 *
	 * @return array Test result
	 */
	public static function test_last_backup_age(): array
	{
		// Try to get last backup time from common backup plugins
		$last_backup = null;
		$backup_plugin = null;

		// UpdraftPlus
		$updraftplus_backup = get_transient('updraftplus_backup_complete');
		if ($updraftplus_backup) {
			$last_backup = intval($updraftplus_backup);
			$backup_plugin = 'UpdraftPlus';
		}

		// Generic check via wp-content backups folder
		if (! $last_backup && is_dir(WP_CONTENT_DIR . '/backups')) {
			$files = scandir(WP_CONTENT_DIR . '/backups', SCANDIR_SORT_DESCENDING);
			if ($files && isset($files[0]) && $files[0] !== '.' && $files[0] !== '..') {
				$latest = WP_CONTENT_DIR . '/backups/' . $files[0];
				$last_backup = filemtime($latest);
				$backup_plugin = 'Unknown';
			}
		}

		$hours_ago = 0;
		$status = 'unknown';

		if ($last_backup) {
			$hours_ago = round((time() - $last_backup) / 3600);

			if ($hours_ago < 24) {
				$status = 'current';
			} elseif ($hours_ago < 7 * 24) {
				$status = 'old';
			} else {
				$status = 'very_old';
			}
		}

		return [
			'test_name'       => 'Last Backup Age',
			'last_backup'     => $last_backup ? date('Y-m-d H:i:s', $last_backup) : 'Unknown',
			'hours_ago'       => $hours_ago,
			'status'          => $status,
			'backup_plugin'   => $backup_plugin,
			'passed'          => $status === 'current',
			'description'     => $status === 'unknown' ? 'Could not determine last backup' : sprintf('Last backup: %d hours ago (%s)', $hours_ago, $status),
		];
	}

	/**
	 * Guardian Sub-Test: Backup storage location
	 *
	 * @return array Test result
	 */
	public static function test_backup_storage(): array
	{
		$storage_options = [];

		// Check if offsite backup is configured
		$active_plugins = get_plugins();

		// Look for cloud storage integrations
		$cloud_plugins = [
			'amazon-s3-and-cloudfront/wordpress-s3.php' => 'AWS S3',
			'dropzone-premium/dropzone-wordpress-plugin.php' => 'Dropzone',
			'backwpup/backwpup.php' => 'BackWPup',
		];

		$has_cloud = false;
		foreach ($cloud_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$has_cloud = true;
				$storage_options[] = $plugin_name;
				break;
			}
		}

		return [
			'test_name'      => 'Backup Storage Location',
			'storage_options' => $storage_options,
			'offsite_backup' => $has_cloud,
			'passed'         => $has_cloud,
			'description'    => $has_cloud ? implode(', ', $storage_options) : 'Backups only stored on local server',
		];
	}

	/**
	 * Guardian Sub-Test: Backup schedule
	 *
	 * @return array Test result
	 */
	public static function test_backup_schedule(): array
	{
		$active_plugins = get_plugins();

		// Check if scheduled backup events exist
		$schedule_status = 'unknown';

		if (isset($active_plugins['updraftplus/updraftplus.php'])) {
			$updraft_options = get_option('updraftplus-options');
			if ($updraft_options && isset($updraft_options['interval'])) {
				$schedule_status = $updraft_options['interval'];
			}
		}

		$has_schedule = $schedule_status !== 'unknown';

		return [
			'test_name'      => 'Backup Schedule',
			'schedule_status' => $schedule_status,
			'passed'         => $has_schedule,
			'description'    => $has_schedule ? sprintf('Backup schedule: %s', $schedule_status) : 'No backup schedule configured',
		];
	}

	/**
	 * Analyze backup automation
	 *
	 * @return array Backup analysis
	 */
	private static function analyze_backup_automation(): array
	{
		$active_plugins = get_plugins();

		$threat_level = 0;
		$threat_color = 'green';
		$issues = [];

		// Check for backup plugin
		$backup_plugins = [
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'backupbuddy/backupbuddy.php',
			'jetpack/jetpack.php',
		];

		$has_backup = false;
		foreach ($backup_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_backup = true;
				break;
			}
		}

		if (! $has_backup) {
			$issues[] = 'No backup plugin installed';
			$threat_level = 95;
			$threat_color = 'red';
			$issue = 'NO BACKUP PLUGIN - Critical risk! Data loss is possible.';

			return [
				'threat_level'    => $threat_level,
				'threat_color'    => $threat_color,
				'issue'           => $issue,
				'has_backup'      => false,
			];
		}

		// Check last backup
		$last_backup = null;
		$updraftplus_backup = get_transient('updraftplus_backup_complete');
		if ($updraftplus_backup) {
			$last_backup = intval($updraftplus_backup);
		}

		if ($last_backup) {
			$hours_ago = round((time() - $last_backup) / 3600);

			if ($hours_ago > 7 * 24) { // More than a week old
				$issues[] = sprintf('Last backup %d hours ago', $hours_ago);
				$threat_level = 70;
				$threat_color = 'orange';
			}
		}

		// Check for offsite backup
		$cloud_plugins = [
			'amazon-s3-and-cloudfront/wordpress-s3.php',
			'backwpup/backwpup.php',
		];

		$has_offsite = false;
		foreach ($cloud_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_offsite = true;
				break;
			}
		}

		if (! $has_offsite) {
			$issues[] = 'Backups stored only on local server';
			$threat_level = max($threat_level, 40);
		}

		if (empty($issues)) {
			$issue = 'Backup automation is properly configured';
		} else {
			$issue = implode('; ', $issues);
		}

		return [
			'threat_level'    => $threat_level,
			'threat_color'    => $threat_color,
			'issue'           => $issue,
			'has_backup'      => $has_backup,
			'has_offsite'     => $has_offsite,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Backup Automation Status';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Monitors backup plugin status and backup frequency for disaster recovery readiness';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Disaster Recovery';
	}
}
