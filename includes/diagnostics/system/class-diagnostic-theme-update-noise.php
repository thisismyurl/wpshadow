<?php
declare(strict_types=1);
/**
 * Theme Update Noise Diagnostic
 *
 * Flags inactive themes that generate update notifications and offers cleanup.
 *
 * Family: update-notifications
 * Related: plugin-update-noise
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Admin\Update_Notification_Manager;

class Diagnostic_Theme_Update_Noise extends Diagnostic_Base {

	protected static $slug         = 'theme-update-noise';
	protected static $title        = 'Theme Update Notifications';
	protected static $description  = 'Flags inactive themes that generate update notifications.';
	protected static $family       = 'update-notifications';
	protected static $family_label = 'Update Notification Management';
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$inactive     = Update_Notification_Manager::get_inactive_theme_slugs();
		$update_count = self::count_inactive_theme_updates( $inactive );

		if ( 0 === $update_count && empty( $inactive ) ) {
			return null;
		}

		$count_label = $update_count > 0 ? $update_count : count( $inactive );
		return array(
			'id'   => self::$slug,
			'title'        => sprintf( _n( '%d unused theme needs attention', '%d unused themes need attention', $count_label, 'wpshadow' ), $count_label ),
			'description'  => __( 'Unused themes trigger update nags and add clutter. Delete what you do not need or hide their update notices.', 'wpshadow' ),
			'category'     => 'maintenance',
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
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
				++$count;
			}
		}

		return $count;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Theme Update Notifications
	 * Slug: theme-update-noise
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Flags inactive themes that generate update notifications.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_theme_update_noise(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
