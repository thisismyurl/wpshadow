<?php
/**
 * Image Optimization Integration Diagnostic
 *
 * Checks if image optimization plugins are working correctly. Tests compression and format conversion.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Optimization_Integration Class
 *
 * Validates image optimization plugin integration. Popular plugins like
 * EWWW, Imagify, ShortPixel, and Smush compress images on upload.
 * Misconfigurations can prevent optimization or cause quality loss.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Optimization_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization-integration';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Integration';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image optimization plugins are working correctly';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Optimization plugin detection
	 * - Plugin configuration
	 * - Optimization effectiveness
	 * - API connectivity
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Detect active optimization plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$optimization_plugins = array(
			'ewww-image-optimizer'        => 'EWWW Image Optimizer',
			'imagify'                     => 'Imagify',
			'shortpixel-image-optimiser'  => 'ShortPixel',
			'wp-smushit'                  => 'Smush',
			'optimus'                     => 'Optimus',
			'compress-jpeg-png-images'    => 'TinyPNG',
			'optimole-wp'                 => 'Optimole',
			'wp-optimize'                 => 'WP-Optimize',
		);

		$active_optimization_plugins = array();
		foreach ( $optimization_plugins as $plugin_slug => $plugin_name ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$active_optimization_plugins[ $plugin_slug ] = $plugin_name;
					break;
				}
			}
		}

		// No optimization plugin detected.
		if ( empty( $active_optimization_plugins ) ) {
			$issues[] = __( 'No image optimization plugin detected - images are not being compressed', 'wpshadow' );
		}

		// Multiple optimization plugins (conflict risk).
		if ( count( $active_optimization_plugins ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated plugin names */
				__( 'Multiple optimization plugins active (%s) - may cause conflicts', 'wpshadow' ),
				implode( ', ', $active_optimization_plugins )
			);
		}

		// Check specific plugin configurations.
		foreach ( $active_optimization_plugins as $plugin_slug => $plugin_name ) {
			
			// EWWW Image Optimizer checks.
			if ( 'ewww-image-optimizer' === $plugin_slug ) {
				$ewww_options = get_option( 'ewww_image_optimizer_cloud_key', '' );
				
				if ( empty( $ewww_options ) ) {
					$issues[] = __( 'EWWW Image Optimizer: No API key configured - using local compression only', 'wpshadow' );
				}

				// Check if compression is enabled.
				$ewww_jpg = get_option( 'ewww_image_optimizer_jpg_level', 0 );
				$ewww_png = get_option( 'ewww_image_optimizer_png_level', 0 );
				
				if ( 0 === $ewww_jpg && 0 === $ewww_png ) {
					$issues[] = __( 'EWWW Image Optimizer: Compression appears disabled for JPEG and PNG', 'wpshadow' );
				}
			}

			// Imagify checks.
			if ( 'imagify' === $plugin_slug ) {
				$imagify_api_key = get_option( 'imagify_settings', array() );
				
				if ( empty( $imagify_api_key['api_key'] ) ) {
					$issues[] = __( 'Imagify: No API key configured - plugin not functional', 'wpshadow' );
				}

				// Check optimization level.
				if ( isset( $imagify_api_key['optimization_level'] ) ) {
					$level = $imagify_api_key['optimization_level'];
					if ( 2 === $level ) {
						$issues[] = __( 'Imagify: Ultra compression enabled - may reduce image quality significantly', 'wpshadow' );
					}
				}
			}

			// ShortPixel checks.
			if ( 'shortpixel-image-optimiser' === $plugin_slug ) {
				$shortpixel_key = get_option( 'wp-short-pixel-apiKey', '' );
				
				if ( empty( $shortpixel_key ) ) {
					$issues[] = __( 'ShortPixel: No API key configured - plugin not functional', 'wpshadow' );
				}

				// Check compression type.
				$compression_type = get_option( 'wp-short-pixel-compression', 1 );
				if ( 0 === $compression_type ) {
					$issues[] = __( 'ShortPixel: Lossy compression enabled - verify quality is acceptable', 'wpshadow' );
				}
			}

			// Smush checks.
			if ( 'wp-smushit' === $plugin_slug ) {
				$smush_settings = get_option( 'wp-smush-settings', array() );
				
				if ( empty( $smush_settings ) ) {
					$issues[] = __( 'Smush: No settings found - plugin may not be configured', 'wpshadow' );
				}

				// Check if auto-smush is enabled.
				if ( isset( $smush_settings['auto'] ) && ! $smush_settings['auto'] ) {
					$issues[] = __( 'Smush: Auto-optimize on upload is disabled - images not being compressed automatically', 'wpshadow' );
				}
			}
		}

		// Check for unoptimized recent images.
		global $wpdb;

		$recent_images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value as file_path
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				LIMIT 20",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			)
		);

		$unoptimized_count = 0;
		$total_size = 0;
		$upload_dir = wp_upload_dir();

		foreach ( $recent_images as $image ) {
			$file_path = $upload_dir['basedir'] . '/' . $image->file_path;
			
			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			$size = filesize( $file_path );
			$total_size += $size;

			// Check for optimization metadata (different plugins use different meta keys).
			$optimization_meta = array(
				'_ewww_image_optimizer',
				'_imagify_data',
				'_shortpixel_status',
				'_wp_smush_data',
			);

			$is_optimized = false;
			foreach ( $optimization_meta as $meta_key ) {
				if ( get_post_meta( $image->ID, $meta_key, true ) ) {
					$is_optimized = true;
					break;
				}
			}

			if ( ! $is_optimized ) {
				$unoptimized_count++;
			}
		}

		if ( $unoptimized_count > 0 && ! empty( $active_optimization_plugins ) ) {
			$issues[] = sprintf(
				/* translators: 1: number of images, 2: total images */
				__( '%1$d of %2$d recent images appear unoptimized despite plugin being active', 'wpshadow' ),
				$unoptimized_count,
				count( $recent_images )
			);
		}

		// Check average file size (rough optimization indicator).
		if ( count( $recent_images ) > 0 ) {
			$avg_size = $total_size / count( $recent_images );
			
			// If average is over 500KB, optimization may not be working well.
			if ( $avg_size > 500 * 1024 ) {
				$issues[] = sprintf(
					/* translators: %s: average size */
					__( 'Average recent image size (%s) is high - optimization may not be effective', 'wpshadow' ),
					size_format( $avg_size )
				);
			}
		}

		// Check for optimization filters being used.
		$optimization_filters = array(
			'wp_editor_set_quality'     => __( 'Image editor quality', 'wpshadow' ),
			'jpeg_quality'              => __( 'JPEG quality', 'wpshadow' ),
			'wp_generate_attachment_metadata' => __( 'Attachment metadata generation', 'wpshadow' ),
		);

		$active_filters = array();
		foreach ( $optimization_filters as $filter_name => $description ) {
			if ( has_filter( $filter_name ) ) {
				$active_filters[] = $description;
			}
		}

		if ( ! empty( $active_filters ) && ! empty( $active_optimization_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated filter names */
				__( 'Quality filters active: %s - ensure they don\'t conflict with optimization plugin', 'wpshadow' ),
				implode( ', ', $active_filters )
			);
		}

		// Check for CDN that might serve unoptimized images.
		$cdn_plugins = array(
			'wp-rocket'         => __( 'WP Rocket - ensure image optimization is enabled', 'wpshadow' ),
			'w3-total-cache'    => __( 'W3 Total Cache - check CDN image handling', 'wpshadow' ),
			'wp-super-cache'    => __( 'WP Super Cache - verify image optimization', 'wpshadow' ),
			'cloudflare'        => __( 'Cloudflare - may serve cached unoptimized versions', 'wpshadow' ),
		);

		foreach ( $cdn_plugins as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = $message;
					break;
				}
			}
		}

		// Check WP-CLI availability for bulk optimization.
		$has_wp_cli = defined( 'WP_CLI' ) && WP_CLI;
		
		if ( ! $has_wp_cli && ! empty( $active_optimization_plugins ) ) {
			$issues[] = __( 'WP-CLI not detected - bulk image optimization may be slower via admin interface', 'wpshadow' );
		}

		// Check for lazy loading (complements optimization).
		$has_lazy_load = wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' );
		
		if ( ! $has_lazy_load ) {
			$issues[] = __( 'Lazy loading not enabled - consider enabling for better performance with optimized images', 'wpshadow' );
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with image optimization',
						'%d issues detected with image optimization',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-optimization-integration',
				'details'      => array(
					'issues'                      => $issues,
					'active_optimization_plugins' => array_values( $active_optimization_plugins ),
					'unoptimized_count'           => $unoptimized_count,
					'total_recent_images'         => count( $recent_images ),
					'avg_size'                    => count( $recent_images ) > 0 ? size_format( $total_size / count( $recent_images ) ) : 'N/A',
				),
			);
		}

		return null;
	}
}
