<?php
/**
 * Theme Update Noise Diagnostic
 *
 * Flags inactive themes that generate update notifications and offers cleanup.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Admin\Update_Notification_Manager;

class Diagnostic_Theme_Update_Noise {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$inactive = Update_Notification_Manager::get_inactive_theme_slugs();
		$update_count = self::count_inactive_theme_updates( $inactive );

		if ( 0 === $update_count && empty( $inactive ) ) {
			return null;
		}

		$count_label = $update_count > 0 ? $update_count : count( $inactive );
		return array(
			'id'           => 'theme-update-noise',
			'title'        => sprintf( _n( '%d unused theme needs attention', '%d unused themes need attention', $count_label, 'wpshadow' ), $count_label ),
			'description'  => __( 'Unused themes trigger update nags and add clutter. Delete what you do not need or hide their update notices.', 'wpshadow' ),
			'color'        => '#0288d1',
			'bg_color'     => '#e1f5fe',
			'kb_link'      => 'https://wpshadow.com/kb/manage-theme-update-notifications/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=theme-update-noise',
			'action_link'  => admin_url( 'themes.php' ),
			'action_text'  => __( 'Manage Themes', 'wpshadow' ),
			'auto_fixable' => true,
			'threat_level' => 25,
		);
	}

	/**
	 * Count updates affecting inactive themes.
	 *
	 * @param array $inactive Slugs of inactive themes.
	 * @return int
	 */
	private static function count_inactive_theme_updates( array $inactive ): int {
		if ( empty( $inactive ) ) {
			return 0;
		}

		$updates = get_site_transient( 'update_themes' );
		if ( empty( $updates->response ) || ! is_array( $updates->response ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $inactive as $slug ) {
			if ( isset( $updates->response[ $slug ] ) ) {
				$count++;
			}
		}

		return $count;
	}
}
