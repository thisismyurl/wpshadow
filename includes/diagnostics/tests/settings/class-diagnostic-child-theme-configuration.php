<?php
/**
 * Child Theme Configuration Diagnostic
 *
 * Validates that child themes are properly configured with correct
 * headers, parent references, and safe overrides.
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
 * Child Theme Configuration Diagnostic Class
 *
 * Checks child theme setup and configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Child_Theme_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'child-theme-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Child Theme Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates child theme configuration and parent linkage';

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
		$issues = array();

		$theme = wp_get_theme();

		// Check if active theme is a child theme.
		$stylesheet = $theme->get_stylesheet();
		$template   = $theme->get_template();

		if ( $stylesheet === $template ) {
			// Not a child theme. Check if child theme recommended.
			return null;
		}

		// Validate parent theme exists.
		$parent_theme = wp_get_theme( $template );
		if ( ! $parent_theme->exists() ) {
			$issues[] = __( 'Parent theme is missing or inactive', 'wpshadow' );
		}

		// Check style.css headers.
		$stylesheet_dir = get_stylesheet_directory();
		$style_file     = $stylesheet_dir . '/style.css';

		if ( ! file_exists( $style_file ) ) {
			$issues[] = __( 'Child theme is missing style.css file', 'wpshadow' );
		} else {
			$content = file_get_contents( $style_file );
			if ( false === stripos( $content, 'Template:' ) ) {
				$issues[] = __( 'Child theme style.css missing Template header (parent theme link)', 'wpshadow' );
			}

			if ( false === stripos( $content, 'Theme Name:' ) ) {
				$issues[] = __( 'Child theme style.css missing Theme Name header', 'wpshadow' );
			}
		}

		// Check functions.php presence.
		$functions_file = $stylesheet_dir . '/functions.php';
		if ( ! file_exists( $functions_file ) ) {
			$issues[] = __( 'Child theme missing functions.php (may fail to enqueue parent styles)', 'wpshadow' );
		} else {
			$functions_content = file_get_contents( $functions_file );

			// Check if parent styles are enqueued properly.
			if ( false === stripos( $functions_content, 'wp_enqueue_style' ) ) {
				$issues[] = __( 'Child theme functions.php does not enqueue parent styles', 'wpshadow' );
			}

			// Check if child theme uses after_setup_theme hook.
			if ( false === stripos( $functions_content, 'after_setup_theme' ) ) {
				$issues[] = __( 'Child theme functions.php missing after_setup_theme hook', 'wpshadow' );
			}
		}

		// Check for template overrides.
		$parent_dir = get_template_directory();
		$child_dir  = get_stylesheet_directory();

		$overrides = array();
		$parent_templates = glob( $parent_dir . '/*.php' );

		foreach ( $parent_templates as $template_file ) {
			$basename = basename( $template_file );
			if ( file_exists( $child_dir . '/' . $basename ) ) {
				$overrides[] = $basename;
			}
		}

		if ( ! empty( $overrides ) ) {
			// Check if overrides are up to date (manual check recommended).
			if ( count( $overrides ) > 15 ) {
				$issues[] = sprintf(
					/* translators: %d: number of overrides */
					__( 'Child theme overrides %d template files (review for compatibility after updates)', 'wpshadow' ),
					count( $overrides )
				);
			}
		}

		// Check child theme version.
		$child_version = $theme->get( 'Version' );
		if ( empty( $child_version ) ) {
			$issues[] = __( 'Child theme does not declare a version', 'wpshadow' );
		}

		// Check if parent theme has updates.
		$updates = get_transient( 'update_themes' );
		if ( ! empty( $updates ) && isset( $updates->response[ $template ] ) ) {
			$issues[] = __( 'Parent theme has updates available (test child theme after update)', 'wpshadow' );
		}

		// Check if child theme includes theme.json in block themes.
		$parent_theme_json = $parent_dir . '/theme.json';
		$child_theme_json  = $child_dir . '/theme.json';

		if ( file_exists( $parent_theme_json ) && ! file_exists( $child_theme_json ) ) {
			// Block theme parent has theme.json, child lacks it.
			$issues[] = __( 'Parent block theme uses theme.json but child theme does not (may miss block settings)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of child theme issues */
					__( 'Found %d child theme configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'      => array(
					'issues'          => $issues,
					'child_theme'     => $stylesheet,
					'parent_theme'    => $template,
					'overrides'       => $overrides,
					'recommendation'  => __( 'Ensure child theme has proper headers, enqueues parent styles, and test after parent updates.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
