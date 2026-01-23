<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Theme Assets Loading
 * Philosophy: Inspire confidence (#8) by keeping wp-admin clean and fast.
 *
 * Detects when front-end theme styles bleed into wp-admin, pulling large font files
 * and design assets that slow down the dashboard.
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Admin_Theme_Assets extends Diagnostic_Base {
	/**
	 * Detect theme CSS leaking into wp-admin requests.
	 *
	 * @return array|null Finding data or null if healthy.
	 */
	public static function check(): ?array {
		if ( ! is_admin() ) {
			return null;
		}

		global $wp_styles;
		if ( ! isset( $wp_styles ) || empty( $wp_styles->queue ) ) {
			return null;
		}

		$theme_handles = array();
		$non_core      = array();

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
				continue;
			}

			$style = $wp_styles->registered[ $handle ];
			$src   = is_string( $style->src ) ? $style->src : '';

			if ( '' === $src ) {
				continue;
			}

			$src_lower = strtolower( $src );
			$is_core   = ( false !== strpos( $src_lower, '/wp-admin/' ) || false !== strpos( $src_lower, '/wp-includes/' ) );

			if ( $is_core ) {
				continue;
			}

			$non_core[] = $handle;

			if ( false !== strpos( $src_lower, '/wp-content/themes/' ) ) {
				$theme_handles[] = $handle;
			}
		}

		$theme_handles   = array_values( array_unique( $theme_handles ) );
		$non_core        = array_values( array_unique( $non_core ) );
		$theme_count     = count( $theme_handles );
		$non_core_count  = count( $non_core );

		// Allow a few plugin/admin styles; flag only when theme assets bleed into wp-admin.
		if ( $theme_count <= 1 ) {
			return null;
		}

		$handle_list = implode( ', ', array_slice( $theme_handles, 0, 5 ) );
		if ( $theme_count > 5 ) {
			$handle_list .= ', ...';
		}

		return array(
			'id'           => 'admin-theme-assets',
			'title'        => sprintf( __( 'Theme CSS Loaded in Admin (%d handles)', 'wpshadow' ), $theme_count ),
			'description'  => sprintf(
				__( 'wp-admin is loading %1$d theme styles (%2$s) alongside %3$d other non-core styles. Theme fonts and design assets slow the dashboard; limit admin to minimal styles.', 'wpshadow' ),
				$theme_count,
				$handle_list ?: __( 'unknown handles', 'wpshadow' ),
				$non_core_count
			),
			'severity'     => 'medium',
			'category'     => 'performance',
			'kb_link'      => 'https://wpshadow.com/kb/admin-theme-assets',
			'training_link'=> 'https://wpshadow.com/training/admin-performance',
			'auto_fixable' => false,
			'threat_level' => 35,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Admin Theme Assets
	 * Slug: -admin-theme-assets
	 * File: class-diagnostic-admin-theme-assets.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Admin Theme Assets
	 * Slug: -admin-theme-assets
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__admin_theme_assets(): array {
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
			'message' => 'Test not yet implemented',
		);
	}

}
