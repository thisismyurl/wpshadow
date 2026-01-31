<?php
/**
 * Theme Plugin Dependency Diagnostic
 *
 * Checks if the active theme requires specific plugins to function properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
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
 * Checks theme plugin dependencies.
 *
 * @since 1.2601.2309
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
	protected static $description = 'Checks if active theme requires specific plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get theme support declarations
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		// Themes that have known plugin dependencies
		$theme_dependencies = array(
			'Divi'         => 'divi-extras/divi-extras.php',
			'Avada'        => 'fusion-builder/fusion-builder.php',
			'Enfold'       => 'enfold-extra/enfold-extra.php',
			'TheGem'       => 'thegem-elements/thegem-elements.php',
			'Themify'      => 'themify-builder/themify-builder.php',
		);

		foreach ( $theme_dependencies as $dep_theme => $required_plugin ) {
			if ( strpos( $theme_name, $dep_theme ) !== false ) {
				if ( ! is_plugin_active( $required_plugin ) ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							/* translators: %1$s: theme name, %2$s: required plugin */
							__( '%1$s theme requires %2$s plugin for full functionality', 'wpshadow' ),
							$theme_name,
							basename( dirname( $required_plugin ) )
						),
						'severity'      => 'high',
						'threat_level'  => 55,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/theme-plugin-dependency',
					);
				}
			}
		}

		// Check for theme_support items that might be missing
		$recommended_features = array( 'post-thumbnails', 'html5', 'custom-logo' );
		$missing_features = array();

		foreach ( $recommended_features as $feature ) {
			if ( ! current_theme_supports( $feature ) ) {
				$missing_features[] = $feature;
			}
		}

		if ( count( $missing_features ) > 1 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %s: missing features */
					__( 'Theme missing recommended WordPress features: %s', 'wpshadow' ),
					implode( ', ', $missing_features )
				),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/theme-plugin-dependency',
			);
		}

		return null;
	}
}
