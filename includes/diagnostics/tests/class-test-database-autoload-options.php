<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Autoload Options Size
 * Checks total size of autoloaded options
 */
class Test_Database_Autoload_Options extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$autoload_size = $wpdb->get_var(
			"SELECT SUM(CHAR_LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);

		if ($autoload_size && $autoload_size > 1000000) {
			return array(
				'id'            => 'database-autoload-options',
				'title'         => 'Large Autoload Options',
				'threat_level'  => 40,
				'description'   => sprintf(
					'Autoloaded options consume %.2f MB. Set non-critical options to autoload=no.',
					$autoload_size / 1048576
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
	public static function test_live_autoload_options(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Autoload options size is normal' : 'Large autoload options detected',
		);
	}
}
