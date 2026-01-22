<?php
declare(strict_types=1);
/**
 * Plugin Repository Check Diagnostic
 *
 * Philosophy: Trust verification - detect nulled/pirated plugins
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Verify plugins are from trusted sources.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Plugin_Repository_Check extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$all_plugins        = get_plugins();
		$suspicious_plugins = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			// Check if plugin has update URL
			if ( ! empty( $plugin_data['UpdateURI'] ) ) {
				$update_uri = $plugin_data['UpdateURI'];
				// Check if update URL is not from WordPress.org or known marketplaces
				if ( stripos( $update_uri, 'wordpress.org' ) === false &&
					stripos( $update_uri, 'codecanyon.net' ) === false &&
					stripos( $update_uri, 'github.com' ) === false ) {
					$suspicious_plugins[] = $plugin_data['Name'];
				}
			}

			// Check for nulled plugin indicators
			if ( ! empty( $plugin_data['Description'] ) ) {
				$description = strtolower( $plugin_data['Description'] );
				if ( strpos( $description, 'nulled' ) !== false ||
					strpos( $description, 'cracked' ) !== false ||
					strpos( $description, 'pirated' ) !== false ) {
					$suspicious_plugins[] = $plugin_data['Name'];
				}
			}
		}

		if ( ! empty( $suspicious_plugins ) ) {
			return array(
				'id'            => 'plugin-repository-check',
				'title'         => 'Suspicious Plugin Sources Detected',
				'description'   => sprintf(
					'The following plugins may be from untrusted sources or nulled: %s. Nulled plugins often contain malware. Use only official sources.',
					implode( ', ', array_slice( array_unique( $suspicious_plugins ), 0, 3 ) )
				),
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/avoid-nulled-plugins/',
				'training_link' => 'https://wpshadow.com/training/plugin-sources/',
				'auto_fixable'  => false,
				'threat_level'  => 90,
			);
		}

		return null;
	}
}
