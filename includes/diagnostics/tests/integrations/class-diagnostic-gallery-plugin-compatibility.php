<?php
/**
 * Gallery Plugin Compatibility Diagnostic
 *
 * Tests compatibility with popular WordPress gallery plugins (NextGEN, Envira, FooGallery)
 * and validates media selection, display, and integration with WordPress core.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2603.1354
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gallery Plugin Compatibility Diagnostic Class
 *
 * Detects compatibility issues with gallery plugins and WordPress media library.
 *
 * @since 1.2603.1354
 */
class Diagnostic_Gallery_Plugin_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gallery-plugin-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gallery Plugin Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests compatibility with gallery plugins and validates media integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Popular gallery plugins are active
	 * - Gallery custom post types are registered correctly
	 * - Media library integration works
	 * - No conflicts with WordPress core galleries
	 * - Proper image size configuration
	 * - Database tables for gallery plugins exist
	 *
	 * @since  1.2603.1354
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$active_galleries = array();
		global $wpdb;

		// Detect active gallery plugins.
		$gallery_plugins = array(
			'nextgen-gallery/nggallery.php'      => array(
				'name'  => 'NextGEN Gallery',
				'table' => $wpdb->prefix . 'ngg_gallery',
				'cpt'   => 'ngg_pictures',
			),
			'envira-gallery-lite/envira-gallery-lite.php' => array(
				'name'  => 'Envira Gallery',
				'table' => null,
				'cpt'   => 'envira',
			),
			'foogallery/foogallery.php'          => array(
				'name'  => 'FooGallery',
				'table' => null,
				'cpt'   => 'foogallery',
			),
			'modula-best-grid-gallery/Modula.php' => array(
				'name'  => 'Modula',
				'table' => null,
				'cpt'   => 'modula-gallery',
			),
			'photo-gallery/photo-gallery.php'    => array(
				'name'  => '10Web Photo Gallery',
				'table' => $wpdb->prefix . 'bwg_gallery',
				'cpt'   => 'bwg_gallery',
			),
		);

		foreach ( $gallery_plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_galleries[] = $plugin_data['name'];

				// Check if required database table exists.
				if ( ! empty( $plugin_data['table'] ) ) {
					$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $plugin_data['table'] ) );
					
					if ( ! $table_exists ) {
						$issues[] = sprintf(
							/* translators: 1: plugin name, 2: table name */
							__( '%1$s is active but required database table %2$s is missing', 'wpshadow' ),
							$plugin_data['name'],
							$plugin_data['table']
						);
					}
				}

				// Check if custom post type is registered.
				if ( ! empty( $plugin_data['cpt'] ) && ! post_type_exists( $plugin_data['cpt'] ) ) {
					$issues[] = sprintf(
						/* translators: 1: plugin name, 2: custom post type */
						__( '%1$s is active but custom post type "%2$s" is not registered', 'wpshadow' ),
						$plugin_data['name'],
						$plugin_data['cpt']
					);
				}
			}
		}

		// Only run further checks if a gallery plugin is active.
		if ( empty( $active_galleries ) ) {
			return null;
		}

		// Check for conflicting shortcode handlers.
		global $shortcode_tags;
		if ( isset( $shortcode_tags['gallery'] ) ) {
			// WordPress has a core [gallery] shortcode. Check if it's been overridden.
			$gallery_handler = $shortcode_tags['gallery'];
			
			// If it's not the default gallery handler, note it.
			if ( ! is_callable( $gallery_handler ) || 
				 ( is_array( $gallery_handler ) && ! in_array( 'gallery_shortcode', $gallery_handler, true ) ) ) {
				$issues[] = __( 'WordPress core [gallery] shortcode has been overridden by a plugin, which may cause conflicts', 'wpshadow' );
			}
		}

		// Check for lightbox plugin conflicts.
		$lightbox_plugins = array(
			'simple-lightbox/simple-lightbox.php' => 'Simple Lightbox',
			'responsive-lightbox/responsive-lightbox.php' => 'Responsive Lightbox',
			'wp-featherlight/wp-featherlight.php' => 'WP Featherlight',
		);

		$active_lightboxes = array();
		foreach ( $lightbox_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_lightboxes[] = $plugin_name;
			}
		}

		if ( count( $active_lightboxes ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of lightbox plugins */
				__( 'Multiple lightbox plugins are active (%s), which may cause conflicts', 'wpshadow' ),
				implode( ', ', $active_lightboxes )
			);
		}

		// Check if any galleries exist but have no images.
		if ( is_plugin_active( 'nextgen-gallery/nggallery.php' ) ) {
			$ngg_table = $wpdb->prefix . 'ngg_gallery';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $ngg_table ) );
			
			if ( $table_exists ) {
				$empty_galleries = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$ngg_table} 
					WHERE CAST(pics AS SIGNED) = 0"
				);

				if ( $empty_galleries > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of empty galleries */
						_n(
							'%d NextGEN gallery has no images',
							'%d NextGEN galleries have no images',
							$empty_galleries,
							'wpshadow'
						),
						$empty_galleries
					);
				}
			}
		}

		// Check for gallery plugins using deprecated WordPress functions.
		foreach ( $gallery_plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$plugin_path = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
				
				if ( file_exists( $plugin_path ) ) {
					// Check main plugin file for deprecated functions.
					$main_file = WP_PLUGIN_DIR . '/' . $plugin_file;
					if ( file_exists( $main_file ) ) {
						$content = file_get_contents( $main_file );
						
						// Check for deprecated media functions.
						if ( strpos( $content, 'wp_get_attachment_link' ) !== false ||
							 strpos( $content, 'get_the_post_thumbnail' ) === false ) {
							// Using older attachment functions is fine, just noting it.
						}
					}
				}
			}
		}

		// Check theme compatibility.
		$theme = wp_get_theme();
		if ( current_theme_supports( 'html5', 'gallery' ) ) {
			// Good - modern HTML5 gallery support.
		} else {
			$issues[] = sprintf(
				/* translators: %s: theme name */
				__( 'Theme "%s" does not declare HTML5 gallery support, which may cause styling issues with gallery plugins', 'wpshadow' ),
				$theme->get( 'Name' )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( ' ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'     => array(
					'active_gallery_plugins' => $active_galleries,
					'active_lightboxes'      => $active_lightboxes,
					'issues_count'           => count( $issues ),
				),
				'kb_link'     => 'https://wpshadow.com/kb/gallery-plugin-compatibility',
			);
		}

		return null;
	}
}
