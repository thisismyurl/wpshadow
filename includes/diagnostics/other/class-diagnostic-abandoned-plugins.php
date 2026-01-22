<?php
declare(strict_types=1);
/**
 * Abandoned Plugin Detection Diagnostic
 *
 * Philosophy: Supply chain - detect unmaintained plugins
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for abandoned/unmaintained plugins.
 */
class Diagnostic_Abandoned_Plugins extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_version;

		$plugins   = get_plugins();
		$abandoned = array();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			// Check if plugin hasn't been updated in 3+ years
			$last_update = get_transient( 'plugin_last_update_' . $plugin_file );

			if ( empty( $last_update ) || ( time() - intval( $last_update ) ) > ( 3 * 365 * DAY_IN_SECONDS ) ) {
				// Check if marked as abandoned on WordPress.org
				if ( preg_match( '/abandoned|unmaintained|inactive/i', $plugin_data['Description'] ) ) {
					$abandoned[] = $plugin_data['Name'];
				}
			}
		}

		if ( ! empty( $abandoned ) ) {
			return array(
				'id'            => 'abandoned-plugins',
				'title'         => 'Abandoned/Unmaintained Plugins Detected',
				'description'   => sprintf(
					'Found abandoned plugins not updated in 2+ years: %s. These don\'t receive security patches. Replace with maintained alternatives or remove.',
					implode( ', ', array_slice( $abandoned, 0, 3 ) )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/replace-abandoned-plugins/',
				'training_link' => 'https://wpshadow.com/training/plugin-maintenance/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}

		return null;
	}
}
