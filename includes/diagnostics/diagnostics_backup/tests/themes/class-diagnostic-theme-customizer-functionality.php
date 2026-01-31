<?php
/**
 * Theme Customizer Functionality Diagnostic
 *
 * Detects customizer options that don't work.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1715
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Customizer Functionality Class
 *
 * Validates theme customizer settings.
 *
 * @since 1.5029.1715
 */
class Diagnostic_Theme_Customizer_Functionality extends Diagnostic_Base {

	protected static $slug        = 'theme-customizer-functionality';
	protected static $title       = 'Theme Customizer Functionality';
	protected static $description = 'Detects broken customizer options';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_customizer_functionality';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wp_customize;

		if ( ! isset( $wp_customize ) ) {
			require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
			$wp_customize = new \WP_Customize_Manager();
		}

		$wp_customize->setup_theme();

		$settings = $wp_customize->settings();
		$issues   = array();

		foreach ( $settings as $setting_id => $setting ) {
			// Check if setting has valid type.
			if ( ! in_array( $setting->type, array( 'theme_mod', 'option' ), true ) ) {
				continue;
			}

			// Check if setting has sanitize callback.
			if ( empty( $setting->sanitize_callback ) ) {
				$issues[] = array(
					'setting_id' => $setting_id,
					'issue' => 'Missing sanitize callback',
					'severity' => 'medium',
				);
			}

			// Limit to 20 checks.
			if ( count( $issues ) >= 20 ) {
				break;
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d customizer settings have configuration issues. Fix to prevent data loss.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-customizer-functionality',
				'data'         => array(
					'issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
