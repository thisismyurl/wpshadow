<?php
/**
 * VR Experience Diagnostic
 *
 * Tests whether the site offers virtual reality content or tours for immersive engagement.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VR Experience Diagnostic Class
 *
 * Virtual Reality experiences provide immersive 3D environments for product showcases,
 * virtual tours, training, and entertainment, enhancing user engagement.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Vr_Experience extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'vr-experience';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Virtual Reality Experience';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site offers virtual reality content or tours for immersive engagement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'emerging-technology';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$vr_score = 0;
		$max_score = 6;

		// Check for VR plugins.
		$vr_plugins = array(
			'wp-vr/wp-vr.php' => 'WP VR',
			'vr-views/vr-views.php' => 'VR Views',
			'virtual-tour-builder/virtual-tour-builder.php' => 'Virtual Tour Builder',
			'panorama-360-viewer/panorama-360-viewer.php' => '360 Viewer',
		);

		$has_vr_plugin = false;
		$active_plugin = '';
		foreach ( $vr_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_vr_plugin = true;
				$active_plugin = $plugin_name;
				$vr_score++;
				break;
			}
		}

		if ( ! $has_vr_plugin ) {
			$issues[] = __( 'No VR plugin detected', 'wpshadow' );
		}

		// Check for 360-degree images or videos.
		$has_360_content = self::check_360_content();
		if ( $has_360_content ) {
			$vr_score++;
		} else {
			$issues[] = __( 'No 360-degree images or videos found', 'wpshadow' );
		}

		// Check for WebXR support.
		$webxr_support = self::check_webxr_support();
		if ( $webxr_support ) {
			$vr_score++;
		} else {
			$issues[] = __( 'No WebXR API support for VR experiences', 'wpshadow' );
		}

		// Check for VR headset compatibility indicators.
		$vr_headset_support = self::check_vr_headset_support();
		if ( $vr_headset_support ) {
			$vr_score++;
		} else {
			$issues[] = __( 'No VR headset compatibility indicators (Oculus, HTC Vive, etc.)', 'wpshadow' );
		}

		// Check for virtual tours or 3D spaces.
		$virtual_tours = self::check_virtual_tours();
		if ( $virtual_tours ) {
			$vr_score++;
		} else {
			$issues[] = __( 'No virtual tours or 3D spaces implemented', 'wpshadow' );
		}

		// Check for VR-related content.
		$vr_content = self::check_vr_content();
		if ( $vr_content ) {
			$vr_score++;
		} else {
			$issues[] = __( 'No VR-related content or documentation', 'wpshadow' );
		}

		// Determine severity based on VR implementation.
		$vr_percentage = ( $vr_score / $max_score ) * 100;

		if ( $vr_percentage < 20 ) {
			// Minimal or no VR implementation.
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $vr_percentage < 50 ) {
			// Basic VR implementation.
			$severity = 'low';
			$threat_level = 15;
		} else {
			// Good VR implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: VR implementation percentage */
				__( 'VR implementation at %d%%. ', 'wpshadow' ),
				(int) $vr_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'VR experiences enhance engagement for real estate, tourism, education, and product visualization', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/vr-experience?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check for 360-degree content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if 360 content exists, false otherwise.
	 */
	private static function check_360_content() {
		// Check for 360 images in media library.
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => 100,
			'post_status'    => 'inherit',
		);

		$attachments = get_posts( $args );
		foreach ( $attachments as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			if ( isset( $metadata['image_meta'] ) ) {
				// Check for 360 indicators in filename or metadata.
				$filename = basename( get_attached_file( $attachment->ID ) );
				if ( stripos( $filename, '360' ) !== false || stripos( $filename, 'panorama' ) !== false ) {
					return true;
				}
			}
		}

		// Check for 360 video.
		$video_args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'video',
			'posts_per_page' => 50,
			'post_status'    => 'inherit',
		);

		$videos = get_posts( $video_args );
		foreach ( $videos as $video ) {
			$filename = basename( get_attached_file( $video->ID ) );
			if ( stripos( $filename, '360' ) !== false || stripos( $filename, 'vr' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_360_content', false );
	}

	/**
	 * Check for WebXR support.
	 *
	 * @since 0.6093.1200
	 * @return bool True if WebXR support exists, false otherwise.
	 */
	private static function check_webxr_support() {
		// Check for WebXR libraries in enqueued scripts.
		global $wp_scripts;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				$script_src = '';
				if ( isset( $script->src ) && is_string( $script->src ) ) {
					$script_src = $script->src;
				}

				if ( strpos( $handle, 'webxr' ) !== false ||
					 strpos( $script_src, 'webxr' ) !== false ||
					 strpos( $handle, 'three' ) !== false ||
					 strpos( $script_src, 'three.js' ) !== false ||
					 strpos( $handle, 'a-frame' ) !== false ||
					 strpos( $script_src, 'aframe' ) !== false ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_has_webxr_support', false );
	}

	/**
	 * Check for VR headset support indicators.
	 *
	 * @since 0.6093.1200
	 * @return bool True if VR headset support exists, false otherwise.
	 */
	private static function check_vr_headset_support() {
		// Check for content mentioning VR headsets.
		$headset_keywords = array( 'oculus', 'vive', 'quest', 'valve index', 'psvr', 'vr headset' );

		foreach ( $headset_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_vr_headset_support', false );
	}

	/**
	 * Check for virtual tours.
	 *
	 * @since 0.6093.1200
	 * @return bool True if virtual tours exist, false otherwise.
	 */
	private static function check_virtual_tours() {
		// Check for pages or posts with tour-related content.
		$tour_keywords = array( 'virtual tour', '3d tour', 'vr tour', '360 tour' );

		foreach ( $tour_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		// Check for custom post types related to tours.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $post_types as $post_type ) {
			if ( strpos( strtolower( $post_type ), 'tour' ) !== false ||
				 strpos( strtolower( $post_type ), 'vr' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_virtual_tours', false );
	}

	/**
	 * Check for VR-related content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if VR content exists, false otherwise.
	 */
	private static function check_vr_content() {
		// Check for posts/pages with VR keywords.
		$vr_keywords = array( 'virtual reality', 'vr experience', 'immersive' );

		foreach ( $vr_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_vr_content', false );
	}
}
