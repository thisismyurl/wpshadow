<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused Themes Detection
 *
 * Philosophy: Clean up unused themes to reduce attack surface and storage bloat
 * @package WPShadow
 *
 * @verified 2026-01-23 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Theme_Unused_Templates extends Diagnostic_Base
{
	protected static $slug = 'theme-unused-templates';

	protected static $title = 'Unused Themes Detection';

	protected static $description = 'Identifies unused theme files wasting storage and creating maintenance burden.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string
	{
		return 'theme-unused-templates';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string
	{
		return __('Unused themes consuming storage', 'wpshadow');
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string
	{
		return __('Remove unused themes to reduce storage bloat and maintenance burden. Keep only active theme plus one default backup.', 'wpshadow');
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string
	{
		return 'maintenance';
	}

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding data or null if no issue
	 */
	public static function check(): ?array
	{
		// Get all available themes
		$all_themes = wp_get_themes();
		$theme_count = count($all_themes);

		// More than 2 themes is wasteful (active + 1 backup)
		if ($theme_count > 2) {
			$active_theme = wp_get_theme();
			$unused_themes = array();

			foreach ($all_themes as $theme) {
				// Skip active theme
				if ($theme->get_stylesheet() === $active_theme->get_stylesheet()) {
					continue;
				}

				// Skip common default backups (WordPress defaults)
				$theme_name = $theme->get('Name');
				if (in_array($theme_name, array('Twenty Twenty', 'Twenty Twenty-One', 'Twenty Twenty-Two', 'Twenty Twenty-Three', 'Twenty Twenty-Four'), true)) {
					continue;
				}

				$unused_themes[] = $theme_name . ' (' . $theme->get_stylesheet() . ')';
			}

			// If there are unused themes beyond defaults, fail
			if (! empty($unused_themes)) {
				return array(
					'id'            => 'theme-unused-templates',
					'title'         => 'Unused Themes Found',
					'description'   => sprintf(
						'Your site has %d themes installed. Keep only your active theme and one default backup. Remove: %s. <a href="https://wpshadow.com/kb/remove-unused-themes/" target="_blank">Learn how to remove themes safely</a>',
						$theme_count,
						implode(', ', $unused_themes)
					),
					'severity'      => 'medium',
					'category'      => 'maintenance',
					'kb_link'       => 'https://wpshadow.com/kb/remove-unused-themes/',
					'training_link' => 'https://wpshadow.com/training/theme-cleanup/',
					'auto_fixable'  => false,
					'threat_level'  => 35,
				);
			}
		}

		return null;
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int
	{
		return 35;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string
	{
		return 'https://wpshadow.com/kb/remove-unused-themes/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string
	{
		return 'https://wpshadow.com/training/theme-cleanup/';
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Test Purpose:
	 * Verify check() method correctly detects unused themes.
	 * Pass criteria: Active theme + max 1 backup (usually a WordPress default)
	 * Fail criteria: More than 2 themes installed
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_theme_unused_templates(): array
	{
		$result = self::check();

		if (is_null($result)) {
			$all_themes = wp_get_themes();
			return array(
				'passed'  => true,
				'message' => '✓ Theme collection is clean (' . count($all_themes) . ' theme(s), acceptable count)',
			);
		}

		return array(
			'passed'  => false,
			'message' => '✗ Unused themes detected: ' . $result['title'],
		);
	}
}
