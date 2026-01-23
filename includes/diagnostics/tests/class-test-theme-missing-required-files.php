<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Theme Missing Required Files
 * Checks if active theme has essential template files
 */
class Test_Theme_Missing_Required_Files extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		$current_theme = wp_get_theme();
		$theme_root = $current_theme->get_theme_root();
		$stylesheet = $current_theme->get_stylesheet();

		$required_files = array('index.php', 'style.css');
		$missing_files = array();

		foreach ($required_files as $file) {
			if (!file_exists($theme_root . '/' . $stylesheet . '/' . $file)) {
				$missing_files[] = $file;
			}
		}

		if (!empty($missing_files)) {
			return array(
				'id'            => 'theme-missing-files',
				'title'         => 'Theme Missing Required Files',
				'threat_level'  => 70,
				'description'   => sprintf(
					'Active theme is missing: %s. Theme may not function correctly.',
					implode(', ', $missing_files)
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
	public static function test_live_theme_required_files(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Theme structure is valid' : 'Theme files missing',
		);
	}
}
