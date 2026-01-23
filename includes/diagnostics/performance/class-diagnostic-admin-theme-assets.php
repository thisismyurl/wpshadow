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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
