<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Inactive Themes
 * Checks for installed but inactive themes that can be deleted
 */
class Test_Theme_Inactive_Themes extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		$all_themes = wp_get_themes();
		$current_theme = wp_get_theme();
		$parent_theme = $current_theme->get_parent_theme();

		$required_themes = array($current_theme->get_stylesheet());
		if ($parent_theme) {
			$required_themes[] = $parent_theme->get_stylesheet();
		}

		$inactive_count = count($all_themes) - count($required_themes);

		if ($inactive_count > 5) {
			return array(
				'id'            => 'theme-inactive-themes',
				'title'         => 'Many Inactive Themes',
				'threat_level'  => 20,
				'description'   => sprintf(
					'%d unused themes installed. Delete to reduce clutter.',
					$inactive_count
				),
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_inactive_themes(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Inactive theme count is normal' : 'Many inactive themes found',
		);
	}
}
