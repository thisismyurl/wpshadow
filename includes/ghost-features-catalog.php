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
			'title'       => __( 'Encrypted Backup Storage', 'plugin-wpshadow' ),
			'description' => __( 'Store backups with AES-256 encryption for compliance with GDPR, HIPAA, and other privacy regulations.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-lock',
			'category'    => 'backup',
			'priority'    => 10,
			'benefits'    => array(
				__( 'Military-grade AES-256 encryption', 'plugin-wpshadow' ),
				__( 'GDPR and HIPAA compliance ready', 'plugin-wpshadow' ),
				__( 'Secure offsite storage', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Healthcare websites storing patient data', 'plugin-wpshadow' ),
				__( 'E-commerce sites with customer information', 'plugin-wpshadow' ),
				__( 'Legal firms with confidential documents', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'cloud_offload',
			'title'       => __( 'Automatic Cloud Offload', 'plugin-wpshadow' ),
			'description' => __( 'Automatically sync backups to S3, Wasabi, Backblaze, or other cloud storage providers for disaster recovery.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-cloud-upload',
			'category'    => 'backup',
			'priority'    => 20,
			'benefits'    => array(
				__( 'Multiple cloud provider support', 'plugin-wpshadow' ),
				__( 'Automatic disaster recovery', 'plugin-wpshadow' ),
				__( 'Geographic redundancy', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Mission-critical websites requiring 99.9% uptime', 'plugin-wpshadow' ),
				__( 'Sites in disaster-prone regions', 'plugin-wpshadow' ),
				__( 'Enterprise clients with strict SLAs', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'file_versioning',
			'title'       => __( 'Intelligent File Versioning', 'plugin-wpshadow' ),
			'description' => __( 'Keep multiple versions of every file with one-click rollback to any point in time.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-backup',
			'category'    => 'backup',
			'priority'    => 30,
			'benefits'    => array(
				__( 'Point-in-time recovery', 'plugin-wpshadow' ),
				__( 'One-click rollback', 'plugin-wpshadow' ),
				__( 'Track file change history', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Content sites with frequent media updates', 'plugin-wpshadow' ),
				__( 'Photography portfolios', 'plugin-wpshadow' ),
				__( 'Design agencies collaborating on assets', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'compression_dedup',
			'title'       => __( 'Smart Compression & Deduplication', 'plugin-wpshadow' ),
			'description' => __( 'Reduce storage costs by up to 70% through intelligent compression and duplicate file elimination.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-media-archive',
			'category'    => 'storage',
			'priority'    => 40,
			'benefits'    => array(
				__( 'Save up to 70% on storage costs', 'plugin-wpshadow' ),
				__( 'Automatic duplicate detection', 'plugin-wpshadow' ),
				__( 'Lossless compression algorithms', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Large media libraries (1000+ files)', 'plugin-wpshadow' ),
				__( 'Sites with limited storage budgets', 'plugin-wpshadow' ),
				__( 'Multi-site networks with shared assets', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'broken_link_guardian',
			'title'       => __( 'Broken Link Guardian', 'plugin-wpshadow' ),
			'description' => __( 'Never worry about 404 errors again. Vault preserves deleted media files and automatically restores them when needed.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-shield',
			'category'    => 'media',
			'priority'    => 50,
			'benefits'    => array(
				__( 'Eliminate 404 errors on media files', 'plugin-wpshadow' ),
				__( 'Automatic file restoration', 'plugin-wpshadow' ),
				__( 'Preserve SEO rankings', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'News sites with archived content', 'plugin-wpshadow' ),
				__( 'E-commerce with discontinued products', 'plugin-wpshadow' ),
				__( 'Blogs with accidental media deletions', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'activity_logging',
			'title'       => __( 'Comprehensive Activity Logging', 'plugin-wpshadow' ),
			'description' => __( 'Track every backup, restore, and file operation with detailed audit logs for compliance and troubleshooting.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-list-view',
			'category'    => 'security',
			'priority'    => 60,
			'benefits'    => array(
				__( 'Complete audit trail', 'plugin-wpshadow' ),
				__( 'Compliance reporting', 'plugin-wpshadow' ),
				__( 'User action tracking', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Government and regulated industries', 'plugin-wpshadow' ),
				__( 'Multi-user environments', 'plugin-wpshadow' ),
				__( 'Security-conscious organizations', 'plugin-wpshadow' ),
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
			'title'       => __( 'Multi-Engine Fallback', 'plugin-wpshadow' ),
			'description' => __( 'Automatically try multiple image processing engines (GD, Imagick, Gmagick) until one succeeds.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-images-alt2',
			'category'    => 'media',
			'priority'    => 10,
			'benefits'    => array(
				__( 'Never fail to process images', 'plugin-wpshadow' ),
				__( 'Automatic engine selection', 'plugin-wpshadow' ),
				__( 'Support for exotic formats', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Shared hosting with limited libraries', 'plugin-wpshadow' ),
				__( 'Sites processing RAW camera files', 'plugin-wpshadow' ),
				__( 'Professional photography portfolios', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'format_conversion',
			'title'       => __( 'Smart Format Conversion', 'plugin-wpshadow' ),
			'description' => __( 'Automatically convert images to modern formats (WebP, AVIF) for faster loading without sacrificing quality.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-format-image',
			'category'    => 'media',
			'priority'    => 20,
			'benefits'    => array(
				__( 'Up to 80% smaller file sizes', 'plugin-wpshadow' ),
				__( 'Automatic browser detection', 'plugin-wpshadow' ),
				__( 'Lossless quality conversion', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Image-heavy blogs and portfolios', 'plugin-wpshadow' ),
				__( 'E-commerce with product galleries', 'plugin-wpshadow' ),
				__( 'Mobile-first responsive sites', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'cdn_integration',
			'title'       => __( 'Seamless CDN Integration', 'plugin-wpshadow' ),
			'description' => __( 'Automatically offload media to Cloudflare, CloudFront, or other CDNs for lightning-fast global delivery.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-networking',
			'category'    => 'performance',
			'priority'    => 30,
			'benefits'    => array(
				__( 'Global edge caching', 'plugin-wpshadow' ),
				__( 'Reduce server bandwidth', 'plugin-wpshadow' ),
				__( '3x faster media loading', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Global audience websites', 'plugin-wpshadow' ),
				__( 'High-traffic news portals', 'plugin-wpshadow' ),
				__( 'Video streaming platforms', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'smart_cropping',
			'title'       => __( 'AI-Powered Smart Cropping', 'plugin-wpshadow' ),
			'description' => __( 'Intelligently detect faces and focal points to create perfect crops for thumbnails and responsive images.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-screenoptions',
			'category'    => 'media',
			'priority'    => 40,
			'benefits'    => array(
				__( 'Automatic face detection', 'plugin-wpshadow' ),
				__( 'Preserve important image areas', 'plugin-wpshadow' ),
				__( 'Perfect responsive images', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Portrait photography sites', 'plugin-wpshadow' ),
				__( 'Team member directories', 'plugin-wpshadow' ),
				__( 'Product photography', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'lazy_loading',
			'title'       => __( 'Advanced Lazy Loading', 'plugin-wpshadow' ),
			'description' => __( 'Defer image loading until they enter the viewport, dramatically improving page load times and Core Web Vitals.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-performance',
			'category'    => 'performance',
			'priority'    => 50,
			'benefits'    => array(
				__( 'Improve Core Web Vitals scores', 'plugin-wpshadow' ),
				__( 'Reduce initial page load by 60%', 'plugin-wpshadow' ),
				__( 'Better mobile performance', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Long-form articles with images', 'plugin-wpshadow' ),
				__( 'Infinite scroll galleries', 'plugin-wpshadow' ),
				__( 'Mobile-heavy traffic sites', 'plugin-wpshadow' ),
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
			'title'       => __( 'AVIF Format Support', 'plugin-wpshadow' ),
			'description' => __( 'Next-generation AVIF format with 50% better compression than WebP and superior quality.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-format-gallery',
			'category'    => 'media',
			'priority'    => 10,
			'benefits'    => array(
				__( '50% smaller than WebP', 'plugin-wpshadow' ),
				__( 'Superior image quality', 'plugin-wpshadow' ),
				__( 'HDR and wide color gamut', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Photography portfolios', 'plugin-wpshadow' ),
				__( 'High-quality product images', 'plugin-wpshadow' ),
				__( 'Art galleries and museums', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'raw_processing',
			'title'       => __( 'Professional RAW Processing', 'plugin-wpshadow' ),
			'description' => __( 'Import and process RAW camera files (CR2, NEF, ARW) directly into WordPress with full metadata preservation.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-camera',
			'category'    => 'media',
			'priority'    => 20,
			'benefits'    => array(
				__( 'Support 500+ camera RAW formats', 'plugin-wpshadow' ),
				__( 'Preserve EXIF and XMP metadata', 'plugin-wpshadow' ),
				__( 'Professional color accuracy', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Professional photographers', 'plugin-wpshadow' ),
				__( 'Photography agencies', 'plugin-wpshadow' ),
				__( 'Photo contest platforms', 'plugin-wpshadow' ),
			),
		),
		array(
			'key'         => 'svg_sanitization',
			'title'       => __( 'Secure SVG Sanitization', 'plugin-wpshadow' ),
			'description' => __( 'Safely upload SVG files with automatic XSS vulnerability scanning and malicious code removal.', 'plugin-wpshadow' ),
			'icon'        => 'dashicons-shield-alt',
			'category'    => 'security',
			'priority'    => 30,
			'benefits'    => array(
				__( 'Prevent XSS attacks', 'plugin-wpshadow' ),
				__( 'Automatic script removal', 'plugin-wpshadow' ),
				__( 'Safe for client uploads', 'plugin-wpshadow' ),
			),
			'use_cases'   => array(
				__( 'Sites allowing client uploads', 'plugin-wpshadow' ),
				__( 'Design agencies sharing assets', 'plugin-wpshadow' ),
				__( 'Logo and icon libraries', 'plugin-wpshadow' ),
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
		'vault-wpshadow' => get_vault_ghost_features(),
		'media-wpshadow' => get_media_ghost_features(),
		'image-wpshadow' => get_image_ghost_features(),
	);
}
