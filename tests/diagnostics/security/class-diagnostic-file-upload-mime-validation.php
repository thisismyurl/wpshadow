<?php

declare(strict_types=1);
/**
 * File Upload MIME Type Validation Diagnostic
 *
 * Philosophy: Upload security - validate actual file content
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if file uploads validate MIME types properly.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_File_Upload_MIME_Validation extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check if fileinfo extension is available
		if (! function_exists('finfo_open')) {
			return array(
				'id'          => 'file-upload-mime-validation',
				'title'       => 'Missing File Type Detection',
				'description' => 'PHP fileinfo extension is not installed. WordPress cannot properly validate file types, allowing malicious files to be uploaded with fake extensions (e.g., malware.php.jpg). Install php-fileinfo extension.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-fileinfo-extension/',
				'training_link' => 'https://wpshadow.com/training/upload-security/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}

		// Check WordPress upload settings
		$upload_filetypes = get_option('upload_filetypes');

		// If multisite and dangerous file types are allowed
		if (is_multisite() && ! empty($upload_filetypes)) {
			$dangerous_types = array('php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'cgi', 'exe', 'sh');
			$allowed_array = array_map('trim', explode(' ', strtolower($upload_filetypes)));

			$found_dangerous = array_intersect($dangerous_types, $allowed_array);

			if (! empty($found_dangerous)) {
				return array(
					'id'          => 'file-upload-mime-validation',
					'title'       => 'Dangerous File Types Allowed',
					'description' => sprintf(
						'Your multisite allows uploading executable files: %s. This enables remote code execution. Remove these file types from allowed uploads.',
						implode(', ', $found_dangerous)
					),
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/restrict-file-uploads/',
					'training_link' => 'https://wpshadow.com/training/upload-security/',
					'auto_fixable' => true,
					'threat_level' => 85,
				);
			}
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: File Upload MIME Validation
	 * Slug: -file-upload-mime-validation
	 * File: class-diagnostic-file-upload-mime-validation.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: File Upload MIME Validation
	 * Slug: -file-upload-mime-validation
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
	public static function test_live__file_upload_mime_validation(): array
	{
		$missing_fileinfo = ! function_exists('finfo_open');
		$upload_filetypes = get_option('upload_filetypes');
		$dangerous_allowed = false;

		if (is_multisite() && ! empty($upload_filetypes)) {
			$dangerous_types = array('php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'cgi', 'exe', 'sh');
			$allowed_array = array_map('trim', explode(' ', strtolower($upload_filetypes)));
			$dangerous_allowed = ! empty(array_intersect($dangerous_types, $allowed_array));
		}

		$expected_issue = $missing_fileinfo || $dangerous_allowed;

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned for insecure upload configuration.' : 'No finding returned for safe upload configuration.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding for insecure upload configuration, but got none.'
			: 'Expected no finding for safe upload configuration, but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
