<?php
/**
 * Theme Plugin Dependency Diagnostic
 *
 * Detects themes with hard dependencies on specific plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Plugin Dependency Diagnostic Class
 *
 * Checks if theme has undeclared or problematic plugin dependencies.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Plugin_Dependency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-plugin-dependency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Plugin Dependency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for theme plugin dependencies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();
		$missing_plugins = array();

		// Check for TGM Plugin Activation.
		$has_tgm = class_exists( 'TGM_Plugin_Activation' );

		// Common plugin dependencies to check.
		$common_dependencies = array(
			'elementor/elementor.php'           => 'Elementor',
			'js_composer/js_composer.php'       => 'WPBakery Page Builder',
			'woocommerce/woocommerce.php'       => 'WooCommerce',
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'advanced-custom-fields/acf.php'    => 'Advanced Custom Fields',
			'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields Pro',
		);

		// Check theme files for plugin-specific code.
		$theme_dir = get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );

			foreach ( $common_dependencies as $plugin_file => $plugin_name ) {
				// Check if plugin is referenced but not active.
				$plugin_slug = dirname( $plugin_file );
				if ( preg_match( '/' . preg_quote( $plugin_slug, '/' ) . '/i', $functions_content ) &&
					 ! is_plugin_active( $plugin_file ) ) {
					$missing_plugins[] = $plugin_name;
				}
			}
		}

		if ( ! empty( $missing_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugin names */
				__( 'Theme references inactive plugins: %s', 'wpshadow' ),
				implode( ', ', $missing_plugins )
			);
		}

		// Check if theme has bundled plugins (bad practice).
		$plugins_dir = $theme_dir . '/plugins';
		$inc_plugins_dir = $theme_dir . '/inc/plugins';
		$has_bundled = file_exists( $plugins_dir ) || file_exists( $inc_plugins_dir );

		if ( $has_bundled ) {
			$issues[] = __( 'Theme bundles plugins (violates theme guidelines)', 'wpshadow' );
		}

		// Check for admin notices about missing plugins.
		if ( ! empty( $missing_plugins ) && ! $has_tgm ) {
			$issues[] = __( 'No plugin installation helper (TGM) found', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme has plugin dependency issues', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'     => array(
					'theme'           => $theme->get( 'Name' ),
					'missing_plugins' => $missing_plugins,
					'has_tgm'         => $has_tgm,
					'has_bundled'     => $has_bundled,
					'issues'          => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-plugin-dependency',
			);
		}

		return null;
	}
}
