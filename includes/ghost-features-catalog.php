<?php
/**
 * Module Ghost Features Catalog
 *
 * Defines ghost features for modules in the catalog.
 * This file contains the feature declarations that will be displayed
 * even when modules are not installed.
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get ghost features for Vault module.
 *
 * @return array Vault ghost features.
 */
function get_vault_ghost_features(): array {
	return array(
		array(
			'key'         => 'encrypted_backups',
			'title'       => __( 'Encrypted Backup Storage', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Store backups with AES-256 encryption for compliance with GDPR, HIPAA, and other privacy regulations.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-lock',
			'category'    => 'backup',
			'priority'    => 10,
			'benefits'    => array(
				__( 'Military-grade AES-256 encryption', 'plugin-wp-support-thisismyurl' ),
				__( 'GDPR and HIPAA compliance ready', 'plugin-wp-support-thisismyurl' ),
				__( 'Secure offsite storage', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Healthcare websites storing patient data', 'plugin-wp-support-thisismyurl' ),
				__( 'E-commerce sites with customer information', 'plugin-wp-support-thisismyurl' ),
				__( 'Legal firms with confidential documents', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'cloud_offload',
			'title'       => __( 'Automatic Cloud Offload', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Automatically sync backups to S3, Wasabi, Backblaze, or other cloud storage providers for disaster recovery.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-cloud-upload',
			'category'    => 'backup',
			'priority'    => 20,
			'benefits'    => array(
				__( 'Multiple cloud provider support', 'plugin-wp-support-thisismyurl' ),
				__( 'Automatic disaster recovery', 'plugin-wp-support-thisismyurl' ),
				__( 'Geographic redundancy', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Mission-critical websites requiring 99.9% uptime', 'plugin-wp-support-thisismyurl' ),
				__( 'Sites in disaster-prone regions', 'plugin-wp-support-thisismyurl' ),
				__( 'Enterprise clients with strict SLAs', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'file_versioning',
			'title'       => __( 'Intelligent File Versioning', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Keep multiple versions of every file with one-click rollback to any point in time.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-backup',
			'category'    => 'backup',
			'priority'    => 30,
			'benefits'    => array(
				__( 'Point-in-time recovery', 'plugin-wp-support-thisismyurl' ),
				__( 'One-click rollback', 'plugin-wp-support-thisismyurl' ),
				__( 'Track file change history', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Content sites with frequent media updates', 'plugin-wp-support-thisismyurl' ),
				__( 'Photography portfolios', 'plugin-wp-support-thisismyurl' ),
				__( 'Design agencies collaborating on assets', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'compression_dedup',
			'title'       => __( 'Smart Compression & Deduplication', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Reduce storage costs by up to 70% through intelligent compression and duplicate file elimination.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-media-archive',
			'category'    => 'storage',
			'priority'    => 40,
			'benefits'    => array(
				__( 'Save up to 70% on storage costs', 'plugin-wp-support-thisismyurl' ),
				__( 'Automatic duplicate detection', 'plugin-wp-support-thisismyurl' ),
				__( 'Lossless compression algorithms', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Large media libraries (1000+ files)', 'plugin-wp-support-thisismyurl' ),
				__( 'Sites with limited storage budgets', 'plugin-wp-support-thisismyurl' ),
				__( 'Multi-site networks with shared assets', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'broken_link_guardian',
			'title'       => __( 'Broken Link Guardian', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Never worry about 404 errors again. Vault preserves deleted media files and automatically restores them when needed.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-shield',
			'category'    => 'media',
			'priority'    => 50,
			'benefits'    => array(
				__( 'Eliminate 404 errors on media files', 'plugin-wp-support-thisismyurl' ),
				__( 'Automatic file restoration', 'plugin-wp-support-thisismyurl' ),
				__( 'Preserve SEO rankings', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'News sites with archived content', 'plugin-wp-support-thisismyurl' ),
				__( 'E-commerce with discontinued products', 'plugin-wp-support-thisismyurl' ),
				__( 'Blogs with accidental media deletions', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'activity_logging',
			'title'       => __( 'Comprehensive Activity Logging', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Track every backup, restore, and file operation with detailed audit logs for compliance and troubleshooting.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-list-view',
			'category'    => 'security',
			'priority'    => 60,
			'benefits'    => array(
				__( 'Complete audit trail', 'plugin-wp-support-thisismyurl' ),
				__( 'Compliance reporting', 'plugin-wp-support-thisismyurl' ),
				__( 'User action tracking', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Government and regulated industries', 'plugin-wp-support-thisismyurl' ),
				__( 'Multi-user environments', 'plugin-wp-support-thisismyurl' ),
				__( 'Security-conscious organizations', 'plugin-wp-support-thisismyurl' ),
			),
		),
	);
}

/**
 * Get ghost features for Media module.
 *
 * @return array Media ghost features.
 */
function get_media_ghost_features(): array {
	return array(
		array(
			'key'         => 'multi_engine_fallback',
			'title'       => __( 'Multi-Engine Fallback', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Automatically try multiple image processing engines (GD, Imagick, Gmagick) until one succeeds.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-images-alt2',
			'category'    => 'media',
			'priority'    => 10,
			'benefits'    => array(
				__( 'Never fail to process images', 'plugin-wp-support-thisismyurl' ),
				__( 'Automatic engine selection', 'plugin-wp-support-thisismyurl' ),
				__( 'Support for exotic formats', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Shared hosting with limited libraries', 'plugin-wp-support-thisismyurl' ),
				__( 'Sites processing RAW camera files', 'plugin-wp-support-thisismyurl' ),
				__( 'Professional photography portfolios', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'format_conversion',
			'title'       => __( 'Smart Format Conversion', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Automatically convert images to modern formats (WebP, AVIF) for faster loading without sacrificing quality.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-format-image',
			'category'    => 'media',
			'priority'    => 20,
			'benefits'    => array(
				__( 'Up to 80% smaller file sizes', 'plugin-wp-support-thisismyurl' ),
				__( 'Automatic browser detection', 'plugin-wp-support-thisismyurl' ),
				__( 'Lossless quality conversion', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Image-heavy blogs and portfolios', 'plugin-wp-support-thisismyurl' ),
				__( 'E-commerce with product galleries', 'plugin-wp-support-thisismyurl' ),
				__( 'Mobile-first responsive sites', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'cdn_integration',
			'title'       => __( 'Seamless CDN Integration', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Automatically offload media to Cloudflare, CloudFront, or other CDNs for lightning-fast global delivery.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-networking',
			'category'    => 'performance',
			'priority'    => 30,
			'benefits'    => array(
				__( 'Global edge caching', 'plugin-wp-support-thisismyurl' ),
				__( 'Reduce server bandwidth', 'plugin-wp-support-thisismyurl' ),
				__( '3x faster media loading', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Global audience websites', 'plugin-wp-support-thisismyurl' ),
				__( 'High-traffic news portals', 'plugin-wp-support-thisismyurl' ),
				__( 'Video streaming platforms', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'smart_cropping',
			'title'       => __( 'AI-Powered Smart Cropping', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Intelligently detect faces and focal points to create perfect crops for thumbnails and responsive images.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-screenoptions',
			'category'    => 'media',
			'priority'    => 40,
			'benefits'    => array(
				__( 'Automatic face detection', 'plugin-wp-support-thisismyurl' ),
				__( 'Preserve important image areas', 'plugin-wp-support-thisismyurl' ),
				__( 'Perfect responsive images', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Portrait photography sites', 'plugin-wp-support-thisismyurl' ),
				__( 'Team member directories', 'plugin-wp-support-thisismyurl' ),
				__( 'Product photography', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'lazy_loading',
			'title'       => __( 'Advanced Lazy Loading', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Defer image loading until they enter the viewport, dramatically improving page load times and Core Web Vitals.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-performance',
			'category'    => 'performance',
			'priority'    => 50,
			'benefits'    => array(
				__( 'Improve Core Web Vitals scores', 'plugin-wp-support-thisismyurl' ),
				__( 'Reduce initial page load by 60%', 'plugin-wp-support-thisismyurl' ),
				__( 'Better mobile performance', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Long-form articles with images', 'plugin-wp-support-thisismyurl' ),
				__( 'Infinite scroll galleries', 'plugin-wp-support-thisismyurl' ),
				__( 'Mobile-heavy traffic sites', 'plugin-wp-support-thisismyurl' ),
			),
		),
	);
}

/**
 * Get ghost features for Image module.
 *
 * @return array Image ghost features.
 */
function get_image_ghost_features(): array {
	return array(
		array(
			'key'         => 'avif_support',
			'title'       => __( 'AVIF Format Support', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Next-generation AVIF format with 50% better compression than WebP and superior quality.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-format-gallery',
			'category'    => 'media',
			'priority'    => 10,
			'benefits'    => array(
				__( '50% smaller than WebP', 'plugin-wp-support-thisismyurl' ),
				__( 'Superior image quality', 'plugin-wp-support-thisismyurl' ),
				__( 'HDR and wide color gamut', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Photography portfolios', 'plugin-wp-support-thisismyurl' ),
				__( 'High-quality product images', 'plugin-wp-support-thisismyurl' ),
				__( 'Art galleries and museums', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'raw_processing',
			'title'       => __( 'Professional RAW Processing', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Import and process RAW camera files (CR2, NEF, ARW) directly into WordPress with full metadata preservation.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-camera',
			'category'    => 'media',
			'priority'    => 20,
			'benefits'    => array(
				__( 'Support 500+ camera RAW formats', 'plugin-wp-support-thisismyurl' ),
				__( 'Preserve EXIF and XMP metadata', 'plugin-wp-support-thisismyurl' ),
				__( 'Professional color accuracy', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Professional photographers', 'plugin-wp-support-thisismyurl' ),
				__( 'Photography agencies', 'plugin-wp-support-thisismyurl' ),
				__( 'Photo contest platforms', 'plugin-wp-support-thisismyurl' ),
			),
		),
		array(
			'key'         => 'svg_sanitization',
			'title'       => __( 'Secure SVG Sanitization', 'plugin-wp-support-thisismyurl' ),
			'description' => __( 'Safely upload SVG files with automatic XSS vulnerability scanning and malicious code removal.', 'plugin-wp-support-thisismyurl' ),
			'icon'        => 'dashicons-shield-alt',
			'category'    => 'security',
			'priority'    => 30,
			'benefits'    => array(
				__( 'Prevent XSS attacks', 'plugin-wp-support-thisismyurl' ),
				__( 'Automatic script removal', 'plugin-wp-support-thisismyurl' ),
				__( 'Safe for client uploads', 'plugin-wp-support-thisismyurl' ),
			),
			'use_cases'   => array(
				__( 'Sites allowing client uploads', 'plugin-wp-support-thisismyurl' ),
				__( 'Design agencies sharing assets', 'plugin-wp-support-thisismyurl' ),
				__( 'Logo and icon libraries', 'plugin-wp-support-thisismyurl' ),
			),
		),
	);
}

/**
 * Get all ghost features catalog.
 *
 * @return array Complete ghost features catalog.
 */
function get_ghost_features_catalog(): array {
	return array(
		'vault-support-thisismyurl' => get_vault_ghost_features(),
		'media-support-thisismyurl' => get_media_ghost_features(),
		'image-support-thisismyurl' => get_image_ghost_features(),
	);
}
