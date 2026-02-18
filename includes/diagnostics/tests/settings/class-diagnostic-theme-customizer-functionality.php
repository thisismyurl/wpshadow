<?php
/**
 * Theme Customizer Functionality Diagnostic
 *
 * Detects issues with theme's Customizer implementation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1245
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Customizer Functionality Diagnostic Class
 *
 * Checks if theme properly implements Customizer features.
 *
 * @since 1.5049.1245
 */
class Diagnostic_Theme_Customizer_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-customizer-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Customizer Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme Customizer implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_customize;

		$theme = wp_get_theme();
		$issues = array();

		// Check if theme has customize_register action.
		$theme_dir = get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		$has_customize_register = false;
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			$has_customize_register = preg_match( '/customize_register|WP_Customize_Manager/i', $functions_content );
		}

		if ( ! $has_customize_register ) {
			$issues[] = __( 'Theme does not register any Customizer settings', 'wpshadow' );
		}

		// Check for selective refresh support.
		if ( ! current_theme_supports( 'customize-selective-refresh-widgets' ) ) {
			$issues[] = __( 'Theme lacks selective refresh support', 'wpshadow' );
		}

		// Check for custom logo support.
		if ( ! current_theme_supports( 'custom-logo' ) ) {
			$issues[] = __( 'Theme does not support custom logo', 'wpshadow' );
		}

		// Check for custom header support.
		if ( ! current_theme_supports( 'custom-header' ) ) {
			$issues[] = __( 'Theme does not support custom header', 'wpshadow' );
		}

		// Check for custom background support.
		if ( ! current_theme_supports( 'custom-background' ) ) {
			$issues[] = __( 'Theme does not support custom background', 'wpshadow' );
		}

		// Count issues - only report if multiple features missing.
		if ( count( $issues ) > 2 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of missing features */
					_n(
						'Theme Customizer missing %d feature',
						'Theme Customizer missing %d features',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'     => array(
					'theme'  => $theme->get( 'Name' ),
					'issues' => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-customizer-functionality',
			);
		}

		return null;
	}
}
