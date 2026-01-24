<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are plugin files directly edited?
 *
 * Category: Security - File Editing
 * Priority: 1
 * Philosophy: Security hardening - reduce attack surface
 *
 * Test Description:
 * Plugin files can be edited via dashboard, creating security risk if admin account is compromised.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-23 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Plugin_Direct_Edits extends Diagnostic_Base
{
	protected static $slug = 'plugin-direct-edits';

	protected static $title = 'Plugin Direct Edits';

	protected static $description = 'Checks if plugin files can be directly edited from WordPress dashboard.';

	protected static $family = 'security';

	protected static $family_label = 'Security';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string
	{
		return 'plugin-direct-edits';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string
	{
		return __('Can plugin files be directly edited?', 'wpshadow');
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string
	{
		return __('Plugin files should not be editable from the dashboard. Disable to prevent code injection attacks.', 'wpshadow');
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string
	{
		return 'security';
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int
	{
		return 75;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string
	{
		return 'https://wpshadow.com/kb/disable-plugin-file-editing/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string
	{
		return 'https://wpshadow.com/training/plugin-file-editing-security/';
	}

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding data or null if no issue
	 */
	public static function check(): ?array
	{
		// Check if DISALLOW_FILE_EDIT is set to disable plugin/theme editing
		// If it's NOT set or set to false, file editing is allowed (security issue)
		$file_edit_allowed = ! (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT);

		if (! $file_edit_allowed) {
			// Good - file editing is disabled
			return null;
		}

		// Bad - file editing is allowed
		return array(
			'id'            => 'plugin-direct-edits',
			'title'         => 'Plugin Files Can Be Edited in Dashboard',
			'description'   => 'Direct editing of plugin files from the WordPress dashboard is enabled. This allows attackers to inject malicious code if your admin account is compromised. Disable with: define( "DISALLOW_FILE_EDIT", true );',
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/disable-plugin-file-editing/',
			'training_link' => 'https://wpshadow.com/training/plugin-file-editing-security/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests whether direct plugin file editing is allowed in WordPress dashboard.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_plugin_direct_edits(): array
	{
		$file_edit_allowed = ! (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT);
		$result = self::check();

		if (! $file_edit_allowed) {
			// File editing is disabled (good)
			return array(
				'passed'  => true,
				'message' => '✓ Direct plugin file editing is disabled (secure)',
			);
		}

		// File editing is allowed (bad)
		return array(
			'passed'  => false,
			'message' => '✗ Direct plugin file editing is allowed - security risk',
		);
	}
}
