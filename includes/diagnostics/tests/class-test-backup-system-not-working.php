<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Backup System Not Working
 *
 * Detects when backup systems are not functioning properly.
 * Non-working backups leave site vulnerable to data loss in emergencies.
 *
 * @since 1.2.0
 */
class Test_Backup_System_Not_Working extends Diagnostic_Base
{

	/**
	 * Check for backup system issues
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$backup_issues = self::detect_backup_issues();

		if (empty($backup_issues)) {
			return null;
		}

		$threat = 95; // Critical - no backups available

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'red',
			'passed'          => false,
			'issue'           => 'Backup system not properly configured or failing',
			'metadata'        => [
				'issues_count' => count($backup_issues),
				'issues'       => $backup_issues,
			],
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-backup-strategy/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-backup-recovery/',
		];
	}

	/**
	 * Guardian Sub-Test: Backup plugin status
	 *
	 * @return array Test result
	 */
	public static function test_backup_plugin_status(): array
	{
		$backup_plugins = self::get_backup_plugins();
		$active_backup = [];

		$active_plugins = get_option('active_plugins', []);
		foreach ($backup_plugins as $plugin => $name) {
			if (in_array($plugin, $active_plugins, true)) {
				$active_backup[] = $name;
			}
		}

		return [
			'test_name'        => 'Backup Plugin Status',
			'available_plugins' => $backup_plugins,
			'active_backups'   => $active_backup,
			'has_backup'       => ! empty($active_backup),
			'passed'           => ! empty($active_backup),
			'description'      => empty($active_backup) ? 'No backup plugin active' : sprintf('Active: %s', implode(', ', $active_backup)),
		];
	}

	/**
	 * Guardian Sub-Test: Last backup timestamp
	 *
	 * @return array Test result
	 */
	public static function test_last_backup(): array
	{
		$last_backup = get_option('wpshadow_last_backup_time');

		if (! $last_backup) {
			return [
				'test_name'     => 'Last Backup Time',
				'last_backup'   => 'Unknown',
				'time_since'    => 'Never backed up',
				'passed'        => false,
				'description'   => 'No backup recorded',
			];
		}

		$time_since = time() - $last_backup;
		$days_since = ceil($time_since / 86400);
		$is_recent = $days_since < 7; // Within 1 week is good

		return [
			'test_name'     => 'Last Backup Time',
			'last_backup'   => date('Y-m-d H:i:s', $last_backup),
			'time_since'    => sprintf('%d days ago', $days_since),
			'is_recent'     => $is_recent,
			'passed'        => $is_recent,
			'description'   => $is_recent ? 'Recent backup available' : sprintf('Last backup was %d days ago', $days_since),
		];
	}

	/**
	 * Guardian Sub-Test: Database backup capability
	 *
	 * @return array Test result
	 */
	public static function test_database_backup_capability(): array
	{
		$can_export = function_exists('wp_export_posts') || function_exists('wp_export_wp');
		$has_wp_cli = defined('WP_CLI') && WP_CLI;

		return [
			'test_name'            => 'Database Export Capability',
			'wp_export_available'  => $can_export,
			'wp_cli_available'     => $has_wp_cli,
			'backup_capable'       => $can_export || $has_wp_cli,
			'passed'               => $can_export || $has_wp_cli,
			'description'          => ($can_export || $has_wp_cli) ? 'Database can be exported' : 'Limited export options',
		];
	}

	/**
	 * Guardian Sub-Test: Backup file storage
	 *
	 * @return array Test result
	 */
	public static function test_backup_storage(): array
	{
		$backup_dir = WP_CONTENT_DIR . '/backups';
		$backup_dir_exists = is_dir($backup_dir);
		$backup_writable = $backup_dir_exists && is_writable($backup_dir);
		$available_space = disk_free_space(WP_CONTENT_DIR);
		$available_mb = $available_space / (1024 * 1024);

		return [
			'test_name'          => 'Backup Storage',
			'backup_directory'   => $backup_dir,
			'directory_exists'   => $backup_dir_exists,
			'directory_writable' => $backup_writable,
			'available_space_mb' => round($available_mb, 2),
			'sufficient_space'   => $available_mb > 500, // At least 500MB
			'passed'             => $backup_writable && $available_mb > 500,
			'description'        => sprintf('Available: %s MB', round($available_mb, 2)),
		];
	}

	/**
	 * Guardian Sub-Test: Off-site backup strategy
	 *
	 * @return array Test result
	 */
	public static function test_offsite_backup(): array
	{
		$has_aws = function_exists('aws_s3_backup');
		$has_dropbox = function_exists('dropbox_backup');
		$has_google_drive = function_exists('gdrive_backup');
		$has_offsite = $has_aws || $has_dropbox || $has_google_drive;

		$services = [];
		if ($has_aws) {
			$services[] = 'Amazon S3';
		}
		if ($has_dropbox) {
			$services[] = 'Dropbox';
		}
		if ($has_google_drive) {
			$services[] = 'Google Drive';
		}

		return [
			'test_name'           => 'Off-site Backup Strategy',
			'has_offsite_backup'  => $has_offsite,
			'configured_services' => $services,
			'passed'              => $has_offsite,
			'description'         => $has_offsite ? sprintf('Configured: %s', implode(', ', $services)) : 'No off-site backup configured',
		];
	}

	/**
	 * Detect backup system issues
	 *
	 * @return array List of issues
	 */
	private static function detect_backup_issues(): array
	{
		$issues = [];

		// Check if any backup plugin is active
		$backup_plugins = self::get_backup_plugins();
		$active_plugins = get_option('active_plugins', []);
		$has_backup_plugin = false;

		foreach ($backup_plugins as $plugin) {
			if (in_array($plugin, $active_plugins, true)) {
				$has_backup_plugin = true;
				break;
			}
		}

		if (! $has_backup_plugin) {
			$issues[] = 'No backup plugin detected - no automatic backups running';
		}

		// Check last backup time
		$last_backup = get_option('wpshadow_last_backup_time');
		if (! $last_backup) {
			$issues[] = 'No recent backup recorded';
		} elseif ((time() - $last_backup) > (30 * 86400)) { // 30 days
			$issues[] = 'Last backup is older than 30 days';
		}

		// Check backup storage
		$available_space = disk_free_space(WP_CONTENT_DIR);
		if ($available_space < (100 * 1024 * 1024)) { // Less than 100MB
			$issues[] = 'Insufficient disk space for backups';
		}

		return $issues;
	}

	/**
	 * Get available backup plugins
	 *
	 * @return array Backup plugin slugs and names
	 */
	private static function get_backup_plugins(): array
	{
		return [
			'updraftplus/updraftplus.php'       => 'UpdraftPlus',
			'backupbuddy/backupbuddy.php'       => 'BackupBuddy',
			'duplicator/duplicator.php'         => 'Duplicator',
			'jetpack/jetpack.php'               => 'Jetpack',
			'vaultpress/vaultpress.php'        => 'VaultPress',
			'wp-backitup/wp-backitup.php'      => 'BackItUp',
			'backup-guard/backup-guard.php'    => 'Backup Guard',
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Backup System Not Working';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if WordPress backup system is properly configured and running';
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
