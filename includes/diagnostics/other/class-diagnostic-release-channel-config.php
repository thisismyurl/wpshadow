<?php
declare(strict_types=1);
/**
 * WordPress Release Channel Configuration Diagnostic
 *
 * Philosophy: Update management - stable release selection
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if stable release channel is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Release_Channel_Config extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$updates_available = get_site_transient( 'update_core' );

		if ( ! empty( $updates_available ) && ! empty( $updates_available->updates ) ) {
			foreach ( $updates_available->updates as $update ) {
				if ( $update->response === 'development' && empty( get_option( 'wpshadow_allow_dev_updates' ) ) ) {
					return array(
						'id'            => 'release-channel-config',
						'title'         => 'WordPress Set to Development Updates',
						'description'   => 'WordPress is configured to receive development/beta updates. These may contain bugs. Use stable releases for production sites.',
						'severity'      => 'low',
						'category'      => 'security',
						'kb_link'       => 'https://wpshadow.com/kb/configure-release-channel/',
						'training_link' => 'https://wpshadow.com/training/update-channels/',
						'auto_fixable'  => false,
						'threat_level'  => 45,
					);
				}
			}
		}

		return null;
	}
}
