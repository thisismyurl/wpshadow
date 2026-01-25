<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Child Theme Not Found
 * Checks if current theme is a child theme with missing parent
 */
class Test_Theme_Child_Theme_Parent_Missing extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		$current_theme     = wp_get_theme();
		$parent_theme_slug = $current_theme->get_template();

		if ( $current_theme->get_stylesheet() !== $parent_theme_slug ) {
			$parent_theme = wp_get_theme( $parent_theme_slug );

			if ( $parent_theme->errors() ) {
				return array(
					'id'           => 'theme-child-parent-missing',
					'title'        => 'Child Theme Parent Not Found',
					'threat_level' => 60,
					'description'  => sprintf(
						'Active child theme "%s" is missing parent theme "%s". Install parent theme immediately.',
						$current_theme->get_name(),
						$parent_theme_slug
					),
				);
			}
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_child_theme_parent(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Theme hierarchy is valid' : 'Missing parent theme detected',
		);
	}
}
