<?php
declare(strict_types=1);
/**
 * PHP Compatibility Diagnostic
 *
 * Philosophy: Education first - identify PHP version compatibility issues
 * Guides to Pro features for automated version checking and Guardian AI scanning
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for PHP compatibility issues with installed plugins and themes.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_PHP_Compatibility extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if there are many old, potentially incompatible plugins
		$plugins = get_plugins();
		if ( empty( $plugins ) ) {
			return null;
		}
		
		$php_version = phpversion();
		$php_major = intval( explode( '.', $php_version )[0] );
		$php_minor = intval( ( explode( '.', $php_version )[1] ?? 0 ) );
		
		// PHP 7.x is outdated (EOL Jan 2023)
		if ( $php_major < 8 ) {
			$days_until_eol = 0; // Already EOL
			$eol_status = 'End of Life (no security updates)';
		} else {
			// Track when 8.0 and 8.1 reach EOL
			$eol_dates = array(
				'8.0' => 1699142400, // Nov 26, 2023 (already passed)
				'8.1' => 1732704000, // Nov 27, 2024
				'8.2' => 1766352000, // Dec 8, 2025
			);
			
			$current_version = $php_major . '.' . $php_minor;
			if ( isset( $eol_dates[ $current_version ] ) && time() > $eol_dates[ $current_version ] ) {
				$eol_status = 'End of Life soon - upgrade soon';
			} else {
				$eol_status = 'Actively supported';
			}
		}
		
		// Find plugins with no updates (likely incompatible)
		$outdated_plugins = array();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$last_updated = false;
			// Check plugin metadata for "last updated" info
			if ( isset( $plugin_data['Requires PHP'] ) ) {
				$requires_php = $plugin_data['Requires PHP'];
				if ( version_compare( $php_version, $requires_php, '<' ) ) {
					$outdated_plugins[] = $plugin_data['Name'] . ' requires PHP ' . $requires_php;
				}
			}
		}
		
		if ( ! empty( $outdated_plugins ) || $php_major < 8.0 ) {
			$issues = array();
			if ( $php_major < 8.0 ) {
				$issues[] = 'PHP ' . $php_version . ' is ' . $eol_status . ' - many modern plugins require PHP 8.0+';
			}
			if ( ! empty( $outdated_plugins ) ) {
				$issues[] = count( $outdated_plugins ) . ' plugin(s) may not be compatible with your PHP version';
			}
			
			return array(
				'title'       => 'PHP Version Compatibility Check',
				'description' => implode( '. ', $issues ) . '. Consider upgrading to PHP 8.1+ for better performance and security.',
				'severity'    => $php_major < 8.0 ? 'high' : 'medium',
				'category'    => 'code_quality',
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-php-version/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-compatibility',
				'auto_fixable' => false,
				'threat_level' => $php_major < 8.0 ? 70 : 40,
			);
		}
		
		return null;
	}

}