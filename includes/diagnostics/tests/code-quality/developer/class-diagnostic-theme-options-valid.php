<?php
/**
 * Theme Options Valid Diagnostic
 *
 * Checks if theme customizer options are valid and not generating errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Options Valid Diagnostic Class
 *
 * Verifies that theme customizer options (theme mods) are valid,
 * accessible, and not generating errors or conflicts.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Theme_Options_Valid extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-options-valid';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Options Valid';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme customizer options are valid and not generating errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the theme options valid diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if option issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$stats    = array();
		$theme    = wp_get_theme();

		// Get theme mods (customizer options).
		$theme_mods          = get_option( 'theme_mods_' . get_stylesheet() );
		$stats['total_mods'] = is_array( $theme_mods ) ? count( $theme_mods ) : 0;

		if ( empty( $theme_mods ) || ! is_array( $theme_mods ) ) {
			$warnings[] = __( 'No theme mods stored (customizer not used or no customizations)', 'wpshadow' );
		}

		// Check for corrupted theme mods.
		if ( is_array( $theme_mods ) ) {
			foreach ( $theme_mods as $mod_name => $mod_value ) {
				// Check for serialization issues.
				if ( is_string( $mod_value ) && 0 === strpos( $mod_value, 'O:' ) ) {
					// Might be serialized object.
					$unserialized = @unserialize( $mod_value );
					if ( false === $unserialized ) {
						$issues[] = sprintf(
							/* translators: %s: mod name */
							__( 'Theme mod "%s" has corrupted data', 'wpshadow' ),
							$mod_name
						);
					}
				}

				// Check for excessively large values.
				if ( is_string( $mod_value ) && strlen( $mod_value ) > 1024 * 1024 ) {
					$warnings[] = sprintf(
						/* translators: %s: mod name */
						__( 'Theme mod "%s" exceeds 1MB - consider optimization', 'wpshadow' ),
						$mod_name
					);
				}
			}
		}

		// Check customizer.php exists and is accessible.
		$theme_dir       = $theme->get_stylesheet_directory();
		$customizer_file = $theme_dir . '/inc/customizer.php';

		if ( ! file_exists( $customizer_file ) ) {
			$warnings[] = __( 'Theme customizer.php not found - customizer may not be properly configured', 'wpshadow' );
		} else {
			// Check for customizer hooks.
			$customizer_content = file_get_contents( $customizer_file );

			if ( strpos( $customizer_content, 'customize_register' ) === false ) {
				$warnings[] = __( 'Customizer file exists but does not register settings', 'wpshadow' );
			}
		}

		// Check for theme setting conflicts.
		$db_theme_settings     = get_option( 'theme_settings_' . get_stylesheet() );
		$wp_customize_settings = get_option( 'wp_customize_settings' );

		if ( is_array( $db_theme_settings ) && is_array( $wp_customize_settings ) ) {
			$conflicts = array_intersect( array_keys( $db_theme_settings ), array_keys( $wp_customize_settings ) );

			if ( ! empty( $conflicts ) ) {
				$warnings[] = sprintf(
					/* translators: %d: number */
					__( '%d theme setting name conflicts detected', 'wpshadow' ),
					count( $conflicts )
				);
			}
		}

		// Check if theme uses deprecated customizer API.
		if ( file_exists( $customizer_file ) ) {
			$content = file_get_contents( $customizer_file );

			// Check for deprecated add_setting.
			if ( preg_match( '/\$wp_customize->add_setting\s*\(/', $content ) ) {
				$warnings[] = __( 'Theme using older add_setting() - ensure proper sanitization callbacks', 'wpshadow' );
			}

			// Check for missing sanitize_callback.
			if ( strpos( $content, 'sanitize_callback' ) === false ) {
				$issues[] = __( 'Theme customizer settings missing sanitize_callback - security risk', 'wpshadow' );
			}
		}

		// Get all customizer sections.
		global $wp_customize;

		if ( isset( $wp_customize ) && is_object( $wp_customize ) ) {
			$sections = $wp_customize->sections();
			$controls = $wp_customize->controls();
			$settings = $wp_customize->settings();

			$stats['customizer_sections'] = count( $sections );
			$stats['customizer_controls'] = count( $controls );
			$stats['customizer_settings'] = count( $settings );

			// Check for orphaned controls (no section).
			$orphaned_controls = 0;
			foreach ( $controls as $control ) {
				if ( empty( $control->section ) || ! isset( $sections[ $control->section ] ) ) {
					++$orphaned_controls;
				}
			}

			if ( $orphaned_controls > 0 ) {
				$warnings[] = sprintf(
					/* translators: %d: number */
					__( '%d customizer controls without valid section', 'wpshadow' ),
					$orphaned_controls
				);
			}

			// Check for orphaned settings (no control).
			$orphaned_settings = 0;
			foreach ( $settings as $setting ) {
				$has_control = false;
				foreach ( $controls as $control ) {
					if ( isset( $control->settings ) &&
						( $control->settings === $setting->id || in_array( $setting->id, (array) $control->settings, true ) ) ) {
						$has_control = true;
						break;
					}
				}

				if ( ! $has_control ) {
					++$orphaned_settings;
				}
			}

			if ( $orphaned_settings > 0 ) {
				$warnings[] = sprintf(
					/* translators: %d: number */
					__( '%d customizer settings without control', 'wpshadow' ),
					$orphaned_settings
				);
			}
		}

		// Check for theme options in deprecated locations.
		$deprecated_option_names = array(
			'theme_options_' . get_stylesheet(),
			'theme_settings_' . get_stylesheet(),
		);

		foreach ( $deprecated_option_names as $option_name ) {
			$old_option = get_option( $option_name );
			if ( ! empty( $old_option ) ) {
				$warnings[] = sprintf(
					/* translators: %s: option name */
					__( 'Deprecated theme option found: %s - consider migrating to customizer', 'wpshadow' ),
					$option_name
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme options have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-options-valid',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme options have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-options-valid',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Theme options are valid.
	}
}
