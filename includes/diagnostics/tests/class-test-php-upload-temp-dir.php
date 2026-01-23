<?php

/**
 * WPShadow System Diagnostic Test: PHP Upload Temp Dir Hygiene
 *
 * Detects excessive old temporary upload files that may indicate cleanup issues
 * or lingering malware payloads.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2301
 * @category    System
 * @philosophy  #7 Ridiculously Good - keep temp clean; #9 Show Value - highlight wasted disk usage
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

class Test_PHP_Upload_Temp_Dir extends Diagnostic_Base
{
	protected static $slug = 'php-upload-temp-dir';
	protected static $title = 'PHP Upload Temp Dir Hygiene';
	protected static $description = 'Checks for old temporary upload files left behind in the PHP upload temp directory.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$upload_tmp_dir = ini_get('upload_tmp_dir');
		$upload_tmp_dir = $upload_tmp_dir !== '' ? $upload_tmp_dir : sys_get_temp_dir();

		if ($upload_tmp_dir === '' || ! is_dir($upload_tmp_dir) || ! is_readable($upload_tmp_dir)) {
			return null;
		}

		$temp_files = glob(rtrim($upload_tmp_dir, '/\\') . '/php*');

		if (empty($temp_files)) {
			return null;
		}

		$old_files = 0;
		$total_size = 0;

		foreach ($temp_files as $file) {
			if (! is_file($file)) {
				continue;
			}

			$total_size += filesize($file) ?: 0;

			$age = time() - filemtime($file);
			if ($age > 3600) { // older than 1 hour
				$old_files++;
			}
		}

		if ($old_files <= 50) {
			return null;
		}

		return array(
			'id'           => static::$slug,
			'title'        => static::$title,
			'description'  => sprintf(
				'Found %d temporary upload files older than 1 hour in %s (total size: %s). Configure temp file cleanup to reduce risk and disk usage.',
				$old_files,
				$upload_tmp_dir,
				$this->format_bytes($total_size)
			),
			'kb_link'      => 'https://wpshadow.com/kb/clean-temp-files/',
			'training_link' => 'https://wpshadow.com/training/temp-file-security/',
			'category'     => 'system',
			'severity'     => 'medium',
			'auto_fixable' => true,
			'threat_level' => 70,
			'priority'     => 12,
			'module'       => 'system',
			'meta'         => array(
				'old_files'   => $old_files,
				'total_size'  => $total_size,
				'path'        => $upload_tmp_dir,
			),
		);
	}

	private function format_bytes(int $bytes): string
	{
		if ($bytes >= 1073741824) {
			return round($bytes / 1073741824, 2) . 'GB';
		}
		if ($bytes >= 1048576) {
			return round($bytes / 1048576, 2) . 'MB';
		}
		if ($bytes >= 1024) {
			return round($bytes / 1024, 2) . 'KB';
		}
		return $bytes . ' bytes';
	}

	public static function get_info(): array
	{
		return array(
			'name'        => 'PHP Upload Temp Dir Hygiene',
			'category'    => 'system',
			'priority'    => 12,
			'severity'    => 'medium',
			'description' => 'Detects accumulation of old temporary upload files in the PHP upload tmp directory.',
		);
	}
}
