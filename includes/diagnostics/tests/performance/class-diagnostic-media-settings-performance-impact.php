<?php
/**
 * Media Settings Performance Impact Diagnostic
 *
 * Analyzes media library settings and identifies configurations that may
 * negatively impact site performance and storage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Settings Performance Impact Diagnostic Class
 *
 * Evaluates media settings for performance optimization opportunities.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Settings_Performance_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-settings-performance-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Settings Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates media settings for performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check media library organization.
		$uploads_use_yearmonth = get_option( 'uploads_use_yearmonth_folders', 1 );
		if ( ! $uploads_use_yearmonth ) {
			$issues[] = __( 'Date-based media organization is disabled which can impact performance', 'wpshadow' );
		}

		// Count total media files.
		$media_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment'"
		);

		if ( $media_count > 10000 ) {
			$issues[] = sprintf(
				/* translators: %s: formatted number of media files */
				__( 'Large media library (%s items) may benefit from optimization', 'wpshadow' ),
				number_format_i18n( (int) $media_count )
			);
		}

		// Check for unattached media.
		$unattached_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_parent = 0"
		);

		if ( $unattached_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %s: formatted number */
				__( '%s unattached media files consuming storage space', 'wpshadow' ),
				number_format_i18n( (int) $unattached_count )
			);
		}

		// Check for old WordPress versions that generate extra image sizes.
		if ( version_compare( get_bloginfo( 'version' ), '5.3', '<' ) ) {
			$issues[] = __( 'Update WordPress to benefit from WebP and responsive image improvements', 'wpshadow' );
		}

		// Check for big_image_size_threshold (added in WP 5.3).
		$big_image_threshold = apply_filters( 'big_image_size_threshold', 2560 );
		if ( $big_image_threshold === false ) {
			$issues[] = __( 'Big image threshold is disabled - very large images may impact performance', 'wpshadow' );
		} elseif ( $big_image_threshold > 4096 ) {
			$issues[] = sprintf(
				/* translators: %d: pixel dimension */
				__( 'Big image threshold is set very high (%dpx) which may generate large files', 'wpshadow' ),
				$big_image_threshold
			);
		}

		// Check if image optimization plugins are active.
		$optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'imagify/imagify.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'optimus/optimus.php',
			'wp-smushit/wp-smush.php',
		);

		$has_optimizer = false;
		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_optimizer = true;
				break;
			}
		}

		if ( ! $has_optimizer && $media_count > 100 ) {
			$issues[] = __( 'No image optimization plugin detected - images may be larger than necessary', 'wpshadow' );
		}

		// Check for lazy loading support.
		if ( version_compare( get_bloginfo( 'version' ), '5.5', '<' ) ) {
			$issues[] = __( 'Update WordPress to benefit from native lazy loading', 'wpshadow' );
		}

		// Check uploads directory size.
		$upload_dir = wp_upload_dir();
		if ( function_exists( 'disk_free_space' ) ) {
			$free_space = disk_free_space( $upload_dir['basedir'] );
			if ( $free_space !== false && $free_space < 1073741824 ) { // Less than 1GB.
				$issues[] = sprintf(
					/* translators: %s: formatted disk space */
					__( 'Low disk space remaining: %s', 'wpshadow' ),
					size_format( $free_space )
				);
			}
		}

		// Check for CDN integration.
		$cdn_plugins = array(
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'autoptimize/autoptimize.php',
		);

		$has_cdn = false;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cdn = true;
				break;
			}
		}

		if ( ! $has_cdn && $media_count > 500 ) {
			$issues[] = __( 'Consider using a CDN for media files to improve performance', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-settings-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
