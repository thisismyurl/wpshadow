<?php declare(strict_types=1);
/**
 * Dangerous PHP Functions Enabled Diagnostic
 *
 * Philosophy: Server hardening - disable dangerous functions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if dangerous PHP functions are enabled.
 */
class Diagnostic_Dangerous_PHP_Functions {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$dangerous = array( 'eval', 'exec', 'system', 'passthru', 'shell_exec', 'proc_open', 'popen' );
		$disabled = ini_get( 'disable_functions' );
		$disabled_list = array_map( 'trim', explode( ',', $disabled ) );
		
		$enabled_dangerous = array();
		
		foreach ( $dangerous as $func ) {
			if ( ! in_array( $func, $disabled_list, true ) && function_exists( $func ) ) {
				$enabled_dangerous[] = $func;
			}
		}
		
		if ( ! empty( $enabled_dangerous ) ) {
			return array(
				'id'          => 'dangerous-php-functions',
				'title'       => 'Dangerous PHP Functions Enabled',
				'description' => sprintf(
					'Dangerous functions enabled: %s. These allow remote code execution. Disable via php.ini: disable_functions = %s',
					implode( ', ', $enabled_dangerous ),
					implode( ', ', $enabled_dangerous )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-dangerous-php-functions/',
				'training_link' => 'https://wpshadow.com/training/php-hardening/',
				'auto_fixable' => false,
				'threat_level' => 95,
			);
		}
		
		return null;
	}
}
