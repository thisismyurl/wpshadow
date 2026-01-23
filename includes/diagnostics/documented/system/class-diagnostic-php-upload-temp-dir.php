<?php

declare(strict_types=1);
/**
 * PHP Upload Temp Directory Diagnostic
 *
 * Philosophy: File security - clean temporary files
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP upload temp directory for old files.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Upload_Temp_Dir extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$upload_tmp_dir = ini_get('upload_tmp_dir');

		if (empty($upload_tmp_dir)) {
			$upload_tmp_dir = sys_get_temp_dir();
		}

		if (! is_dir($upload_tmp_dir) || ! is_readable($upload_tmp_dir)) {
			return null;
		}

		// Count PHP temp files
		$pattern = $upload_tmp_dir . '/php*';
		$temp_files = glob($pattern);

		if (empty($temp_files)) {
			return null;
		}

		$old_files = 0;
		$total_size = 0;

		foreach ($temp_files as $file) {
			if (is_file($file)) {
				$age = time() - filemtime($file);
				$total_size += filesize($file);

				// Files older than 1 hour
				if ($age > 3600) {
					$old_files++;
				}
			}
		}

		if ($old_files > 50) {
			return array(
				'id'          => 'php-upload-temp-dir',
				'title'       => 'Excessive Old Temporary Upload Files',
				'description' => sprintf(
					'Found %d temporary upload files older than 1 hour in %s (total: %s). Temp files should auto-delete but aren\'t. Old malware uploads may persist. Configure proper temp file cleanup.',
					$old_files,
					$upload_tmp_dir,
					size_format($total_size)
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/clean-temp-files/',
				'training_link' => 'https://wpshadow.com/training/temp-file-security/',
				'auto_fixable' => true,
				'threat_level' => 70,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: PHP Upload Temp Dir
	 * Slug: -php-upload-temp-dir
	 * File: class-diagnostic-php-upload-temp-dir.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: PHP Upload Temp Dir
	 * Slug: -php-upload-temp-dir
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__php_upload_temp_dir(): array
	{
		$upload_tmp_dir = ini_get('upload_tmp_dir');

		if (empty($upload_tmp_dir)) {
			$upload_tmp_dir = sys_get_temp_dir();
		}

		$old_files   = 0;
		$total_size  = 0;
		$dir_valid   = is_dir($upload_tmp_dir) && is_readable($upload_tmp_dir);
		$pattern     = $dir_valid ? ($upload_tmp_dir . '/php*') : '';
		$temp_files  = $dir_valid ? glob($pattern) : array();

		if (! empty($temp_files)) {
			foreach ($temp_files as $file) {
				if (is_file($file)) {
					$age        = time() - filemtime($file);
					$total_size += filesize($file);
					if ($age > 3600) {
						$old_files++;
					}
				}
			}
		}

		$expected_issue    = ($old_files > 50);
		$diagnostic_result = self::check();
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes = ($expected_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Upload temp dir: %s. Old temp files (>1h): %d. Total temp files: %d. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$upload_tmp_dir,
			$old_files,
			is_array($temp_files) ? count($temp_files) : 0,
			$expected_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
