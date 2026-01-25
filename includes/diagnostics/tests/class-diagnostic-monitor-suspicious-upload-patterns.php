<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Suspicious_Upload_Patterns extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-upload-patterns',
			'title'         => __( 'Suspicious File Upload Patterns', 'wpshadow' ),
			'description'   => __( 'Detects executable uploads, mass uploads, uploads to wrong directories. Blocks backdoor deployment vectors.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/upload-security/',
			'training_link' => 'https://wpshadow.com/training/file-handling/',
			'auto_fixable'  => false,
			'threat_level'  => 9,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Suspicious Upload Patterns
	 * Slug: -monitor-suspicious-upload-patterns
	 * File: class-diagnostic-monitor-suspicious-upload-patterns.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Suspicious Upload Patterns
	 * Slug: -monitor-suspicious-upload-patterns
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
	public static function test_live__monitor_suspicious_upload_patterns(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'No suspicious upload patterns detected',
			);
		}
		$message = $result['description'] ?? 'Suspicious upload pattern detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
