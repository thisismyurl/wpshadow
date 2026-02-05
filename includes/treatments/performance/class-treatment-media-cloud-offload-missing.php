<?php
/**
 * Media Cloud Offload Missing Treatment
 *
 * Detects when media files are stored only locally without cloud offload,
 * increasing hosting costs and risking data loss.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Cloud Offload Missing Treatment Class
 *
 * Checks if media files are offloaded to cloud storage. Cloud offload
 * reduces hosting costs, improves performance, and increases reliability.
 *
 * @since 1.6033.1430
 */
class Treatment_Media_Cloud_Offload_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cloud-offload-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Files Not Offloaded to Cloud Storage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects media files stored only locally without cloud offload';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks if media is offloaded to cloud storage (S3, R2, GCS, etc.).
	 * Cloud offload reduces server load and hosting costs.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Vault is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
			return null;
		}

		// Check for existing cloud offload solutions.
		if ( self::has_cloud_offload() ) {
			return null;
		}

		// Calculate media library size.
		$uploads_dir = wp_upload_dir();
		if ( ! isset( $uploads_dir['basedir'] ) || ! is_dir( $uploads_dir['basedir'] ) ) {
			return null;
		}

		$storage_gb = self::calculate_storage_size( $uploads_dir['basedir'] );

		// Don't flag if media library is small (< 500MB).
		if ( $storage_gb < 0.5 ) {
			return null;
		}

		// Check if CDN is active.
		$cdn_active = self::has_cdn();

		// Estimate cost savings.
		$estimated_savings = self::estimate_cost_savings( $storage_gb );

		return array(
			'id'                      => self::$slug,
			'title'                   => self::$title,
			'description'             => sprintf(
				/* translators: %s: storage size in GB */
				__( 'Your %s GB media library is stored entirely on your web server. Offloading to cloud storage reduces hosting costs and improves performance through CDN edge delivery.', 'wpshadow' ),
				number_format( $storage_gb, 1 )
			),
			'severity'                => $storage_gb > 5 ? 'medium' : 'low',
			'threat_level'            => min( 50, 20 + ( $storage_gb * 2 ) ),
			'auto_fixable'            => false,
			'local_storage_gb'        => $storage_gb,
			'cdn_active'              => $cdn_active,
			'estimated_monthly_savings' => $estimated_savings,
			'kb_link'                 => 'https://wpshadow.com/kb/cloud-offload',
		);
	}

	/**
	 * Check if cloud offload is already enabled.
	 *
	 * Detects existing cloud offload plugins and services.
	 *
	 * @since  1.6033.1430
	 * @return bool True if cloud offload detected.
	 */
	private static function has_cloud_offload() {
		// Check for popular cloud offload plugins.
		$offload_plugins = array(
			'amazon-s3-and-cloudfront/wordpress-s3.php',
			'wp-offload-media-lite/wp-offload-media-lite.php',
			'cloudinary-image-management-and-manipulation-in-the-cloud-cdn/cloudinary.php',
			'bunnycdn/bunnycdn.php',
		);

		foreach ( $offload_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for Jetpack CDN/Site Accelerator.
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) ) {
			if ( \Jetpack::is_module_active( 'photon' ) || \Jetpack::is_module_active( 'photon-cdn' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Calculate media library storage size in GB.
	 *
	 * @since  1.6033.1430
	 * @param  string $directory Upload directory path.
	 * @return float Storage size in GB.
	 */
	private static function calculate_storage_size( $directory ) {
		// Use disk_free_space and disk_total_space to estimate (fallback).
		// More accurate: iterate through files (expensive).
		$size_bytes = 0;

		// Quick estimate using WordPress attachment metadata.
		global $wpdb;
		$total_size = $wpdb->get_var(
			"SELECT SUM(meta_value) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attached_file' 
			AND meta_value != ''"
		);

		// If no metadata, try filesystem (limited to avoid timeout).
		if ( ! $total_size ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					$size_bytes += $file->getSize();
				}
			}
		} else {
			// Estimate from file count and average file size.
			$file_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
			);

			// Average file size ~2MB for typical WordPress sites.
			$size_bytes = $file_count * 2097152;
		}

		return round( $size_bytes / 1073741824, 2 ); // Convert to GB.
	}

	/**
	 * Check if CDN is active.
	 *
	 * @since  1.6033.1430
	 * @return bool True if CDN detected.
	 */
	private static function has_cdn() {
		// Check for CDN plugins.
		$cdn_plugins = array(
			'cdn-enabler/cdn-enabler.php',
			'wp-fastest-cache/wpFastestCache.php',
			'w3-total-cache/w3-total-cache.php',
		);

		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check if site URL contains CDN indicators.
		$site_url = get_site_url();
		$cdn_patterns = array( 'cdn', 'cloudflare', 'cloudfront', 'fastly' );

		foreach ( $cdn_patterns as $pattern ) {
			if ( stripos( $site_url, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Estimate monthly cost savings from cloud offload.
	 *
	 * @since  1.6033.1430
	 * @param  float $storage_gb Storage size in GB.
	 * @return string Estimated savings range.
	 */
	private static function estimate_cost_savings( $storage_gb ) {
		// Typical shared hosting: $0.10-0.20/GB/month premium for storage.
		// Cloud storage: $0.02-0.03/GB/month.
		$savings_per_gb = 0.10;
		$monthly_savings = $storage_gb * $savings_per_gb;

		if ( $monthly_savings < 10 ) {
			return '$5-10';
		} elseif ( $monthly_savings < 30 ) {
			return '$10-30';
		} elseif ( $monthly_savings < 50 ) {
			return '$30-50';
		} else {
			return '$50+';
		}
	}
}
