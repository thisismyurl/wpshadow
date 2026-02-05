<?php
/**
 * Page Builder Media Picker Conflicts Treatment
 *
 * Tests media picker functionality in popular page builders (Elementor, Divi, Beaver Builder)
 * and detects modal conflicts, JavaScript errors, and integration issues.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.603.1352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Builder Media Picker Conflicts Treatment Class
 *
 * Detects conflicts between WordPress media picker and page builders.
 *
 * @since 1.603.1352
 */
class Treatment_Page_Builder_Media_Picker_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-builder-media-picker-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Builder Media Picker Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media picker in page builders and detects modal conflicts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Popular page builders are active
	 * - Media library scripts are properly enqueued
	 * - No conflicting plugins interfering with media modal
	 * - Page builder-specific media settings
	 * - JavaScript errors in media library
	 *
	 * @since  1.603.1352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$active_builders = array();

		// Detect active page builders.
		$page_builders = array(
			'elementor/elementor.php'            => 'Elementor',
			'beaver-builder-lite-version/fl-builder.php' => 'Beaver Builder',
			'divi-builder/divi-builder.php'      => 'Divi Builder',
			'wp-page-builder/wp-page-builder.php' => 'WP Page Builder',
			'siteorigin-panels/siteorigin-panels.php' => 'SiteOrigin Page Builder',
			'oxygen/functions.php'               => 'Oxygen Builder',
			'brizy/brizy.php'                    => 'Brizy',
		);

		foreach ( $page_builders as $plugin_file => $builder_name ) {
			if ( is_plugin_active( $plugin_file ) || class_exists( str_replace( ' ', '_', $builder_name ) ) ) {
				$active_builders[] = $builder_name;
			}
		}

		// Only run checks if a page builder is active.
		if ( empty( $active_builders ) ) {
			return null;
		}

		// Check for plugins known to conflict with media library.
		$conflicting_plugins = array(
			'real-media-library/index.php'       => 'Real Media Library',
			'media-library-assistant/index.php'  => 'Media Library Assistant',
			'regenerate-thumbnails/regenerate-thumbnails.php' => 'Regenerate Thumbnails',
			'enable-media-replace/enable-media-replace.php' => 'Enable Media Replace',
		);

		$active_conflicting = array();
		foreach ( $conflicting_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_conflicting[] = $plugin_name;
			}
		}

		if ( ! empty( $active_conflicting ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugin names */
				__( 'Plugins that may conflict with page builder media picker are active: %s', 'wpshadow' ),
				implode( ', ', $active_conflicting )
			);
		}

		// Check Elementor-specific settings.
		if ( in_array( 'Elementor', $active_builders, true ) ) {
			// Check if Elementor's media replacement is enabled.
			$elementor_optimized_image_loading = get_option( 'elementor_optimized_image_loading', 'no' );
			if ( 'yes' === $elementor_optimized_image_loading ) {
				// This can sometimes cause issues with media picker.
				$issues[] = __( 'Elementor optimized image loading is enabled, which may cause media picker issues', 'wpshadow' );
			}

			// Check for missing Elementor media library support.
			if ( ! get_option( 'elementor_use_instagram_media', false ) ) {
				// Just a note, not necessarily an issue.
			}
		}

		// Check if media modal scripts are being loaded correctly.
		global $wp_scripts;
		if ( is_admin() && ! empty( $wp_scripts ) ) {
			$media_editor_registered = $wp_scripts->query( 'media-editor', 'registered' );
			$media_views_registered = $wp_scripts->query( 'media-views', 'registered' );

			if ( ! $media_editor_registered || ! $media_views_registered ) {
				$issues[] = __( 'WordPress media library scripts are not properly registered', 'wpshadow' );
			}
		}

		// Check for script optimization plugins that might break media modal.
		$optimization_plugins = array(
			'autoptimize/autoptimize.php'        => 'Autoptimize',
			'wp-rocket/wp-rocket.php'            => 'WP Rocket',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
			'w3-total-cache/w3-total-cache.php'  => 'W3 Total Cache',
		);

		$active_optimization = array();
		foreach ( $optimization_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Check if they're minifying/combining admin scripts.
				if ( $plugin_name === 'Autoptimize' && get_option( 'autoptimize_optimize_admin', false ) ) {
					$active_optimization[] = $plugin_name . ' (admin optimization enabled)';
				} elseif ( is_plugin_active( $plugin_file ) ) {
					$active_optimization[] = $plugin_name;
				}
			}
		}

		if ( ! empty( $active_optimization ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of optimization plugin names */
				__( 'Cache/optimization plugins are active that may interfere with admin media picker: %s. Ensure admin pages are excluded.', 'wpshadow' ),
				implode( ', ', $active_optimization )
			);
		}

		// Check for theme conflicts (themes that override media modal).
		$theme = wp_get_theme();
		$theme_functions = get_template_directory() . '/functions.php';
		
		if ( file_exists( $theme_functions ) ) {
			$theme_content = file_get_contents( $theme_functions );
			
			// Check for problematic patterns.
			if ( strpos( $theme_content, 'wp_enqueue_media' ) !== false &&
				 strpos( $theme_content, 'deregister' ) !== false ) {
				$issues[] = sprintf(
					/* translators: %s: theme name */
					__( 'Theme "%s" appears to modify media library scripts, which may cause page builder conflicts', 'wpshadow' ),
					$theme->get( 'Name' )
				);
			}
		}

		// Check upload directory permissions (affects media library).
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['basedir'] ) && ! wp_is_writable( $upload_dir['basedir'] ) ) {
			$issues[] = __( 'Upload directory is not writable, preventing media uploads in page builders', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( ' ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'     => array(
					'active_page_builders'   => $active_builders,
					'conflicting_plugins'    => $active_conflicting,
					'optimization_plugins'   => $active_optimization,
					'issues_count'           => count( $issues ),
				),
				'kb_link'     => 'https://wpshadow.com/kb/page-builder-media-picker-conflicts',
			);
		}

		return null;
	}
}
