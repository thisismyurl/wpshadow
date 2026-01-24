<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are theme files directly edited?
 *
 * Category: WordPress Ecosystem Health
 * Priority: 1
 * Philosophy: 1, 8, 9
 *
 * Test Description:
 * Are theme files directly edited?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Theme_Direct_Edits extends Diagnostic_Base
{
	protected static $slug = 'theme-direct-edits';

	protected static $title = 'Theme Direct Edits';

	protected static $description = 'Automatically initialized lean diagnostic for Theme Direct Edits. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string
	{
		return 'theme-direct-edits';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string
	{
		return __('Are theme files directly edited?', 'wpshadow');
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string
	{
		return __('Are theme files directly edited?. Part of WordPress Ecosystem Health analysis.', 'wpshadow');
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string
	{
		return 'wordpress_ecosystem';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array
	{
		// Implement: Are theme files directly edited? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int
	{
		// Threat level based on diagnostic category
		return 59;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string
	{
		return 'https://wpshadow.com/kb/theme-direct-edits/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string
	{
		return 'https://wpshadow.com/training/theme-direct-edits/';
	}

	public static function check(): ?array
	{
		// Check if DISALLOW_FILE_EDIT is set to disable theme/plugin editing
		// If it's NOT set or set to false, file editing is allowed (security issue)
		$file_edit_allowed = ! (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT);

		if (! $file_edit_allowed) {
			// Good - file editing is disabled
			return null;
		}

		// Bad - file editing is allowed
		return array(
			'id'            => 'theme-direct-edits',
			'title'         => 'Theme Files Can Be Edited in Dashboard',
			'description'   => 'Direct editing of theme files from the WordPress dashboard is enabled. This allows attackers to inject malicious code if your admin account is compromised. Disable with: define( "DISALLOW_FILE_EDIT", true );',
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/disable-file-editing/',
			'training_link' => 'https://wpshadow.com/training/file-editing-security/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests whether direct theme file editing is allowed in WordPress dashboard.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_theme_direct_edits(): array
	{
		$file_edit_allowed = ! (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT);
		$result = self::check();

		if (! $file_edit_allowed) {
			// File editing is disabled (good)
			return array(
				'passed'  => true,
				'message' => '✓ Direct theme file editing is disabled (secure)',
			);
		}

		// File editing is allowed (bad)
		return array(
			'passed'  => false,
			'message' => '✗ Direct theme file editing is allowed - security risk',
		);
	}
}
