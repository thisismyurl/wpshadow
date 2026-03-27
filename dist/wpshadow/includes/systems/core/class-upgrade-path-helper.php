<?php
/**
 * Diagnostic Base Extension for Pro/Cloud Upgrade Paths
 *
 * Adds support for upgrade path recommendations in diagnostic findings.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upgrade Path Helper Class
 *
 * Manages upgrade path recommendations for diagnostics that have
 * pro/cloud solutions available.
 *
 * @since 1.6093.1200
 */
class Upgrade_Path_Helper {

	/**
	 * Available pro products
	 *
	 * @var array
	 */
	private static $products = array(
		'vault'       => array(
			'name'        => 'WPShadow Vault',
			'description' => 'Secure backup storage with encryption and cloud offload',
			'url'         => 'https://wpshadow.com/vault',
			'features'    => array(
				'automatic-encryption',
				'immutable-storage',
				'cloud-offload',
				'scheduled-backups',
			),
		),
		'integration' => array(
			'name'        => 'WPShadow Pro Integration',
			'description' => 'Connect Canva, Figma, Adobe Express and other design tools',
			'url'         => 'https://wpshadow.com/integration',
			'features'    => array(
				'design-tool-sync',
				'auto-optimization',
				'version-control',
				'webhook-automation',
			),
		),
		'media-image' => array(
			'name'        => 'WPShadow Pro Media (Image)',
			'description' => 'Advanced image processing with filters, social optimization, and branding',
			'url'         => 'https://wpshadow.com/pro-media-image',
			'features'    => array(
				'social-optimization',
				'brand-overlays',
				'filter-presets',
				'bulk-processing',
			),
		),
		'media-video' => array(
			'name'        => 'WPShadow Pro Media (Video)',
			'description' => 'Video editing, transcoding, streaming, and analytics',
			'url'         => 'https://wpshadow.com/pro-media-video',
			'features'    => array(
				'video-transcoding',
				'streaming-optimization',
				'thumbnail-generation',
				'analytics-tracking',
			),
		),
		'media-doc'   => array(
			'name'        => 'WPShadow Pro Media (Document)',
			'description' => 'Document preview, versioning, and collaboration',
			'url'         => 'https://wpshadow.com/pro-media-document',
			'features'    => array(
				'preview-generation',
				'version-tracking',
				'collaborative-editing',
				'search-indexing',
			),
		),
	);

	/**
	 * Add upgrade path to diagnostic finding
	 *
	 * This method adds a tasteful, educational upgrade path suggestion
	 * to a diagnostic finding when a pro/cloud solution is available.
	 *
	 * Philosophy: Commandment #1 (Helpful Neighbor) + #4 (Advice, Not Sales)
	 *
	 * @since 1.6093.1200
	 * @param  array  $finding      Diagnostic finding array.
	 * @param  string $product      Pro product slug (vault, integration, media-image, etc.).
	 * @param  string $feature      Specific feature that solves this problem.
	 * @param  string $manual_guide Optional. Link to manual solution guide. Default empty.
	 * @return array Modified finding with upgrade path.
	 */
	public static function add_upgrade_path( $finding, $product, $feature, $manual_guide = '' ) {
		// Validate product exists.
		if ( ! isset( self::$products[ $product ] ) ) {
			return $finding;
		}

		$product_info = self::$products[ $product ];

		// Build upgrade path recommendation.
		$upgrade_path = array(
			'product'      => $product,
			'product_name' => $product_info['name'],
			'feature'      => $feature,
			'learn_more'   => $product_info['url'] . '?utm_source=diagnostic&utm_medium=finding&utm_campaign=' . $finding['id'],
			'benefits'     => self::get_feature_benefits( $product, $feature ),
		);

		// Add manual guide if provided (Philosophy #6: Drive to Knowledge Base).
		if ( ! empty( $manual_guide ) ) {
			$upgrade_path['manual_guide'] = $manual_guide;
		}

		// Add to finding.
		$finding['upgrade_path'] = $upgrade_path;

		// Track that we showed upgrade path (for analytics).
		self::track_upgrade_path_shown( $finding['id'], $product, $feature );

		return $finding;
	}

	/**
	 * Get feature benefits for a specific product/feature combination
	 *
	 * @since 1.6093.1200
	 * @param  string $product Product slug.
	 * @param  string $feature Feature slug.
	 * @return array Benefits list.
	 */
	private static function get_feature_benefits( $product, $feature ) {
		$benefits = array(
			'vault'       => array(
				'automatic-encryption'  => array(
					__( 'AES-256 military-grade encryption', 'wpshadow' ),
					__( 'Automatic encryption of all backups', 'wpshadow' ),
					__( 'Zero configuration required', 'wpshadow' ),
					__( 'Protects sensitive customer data', 'wpshadow' ),
				),
				'immutable-storage'     => array(
					__( 'Write-once, read-many (WORM) storage', 'wpshadow' ),
					__( 'Prevents accidental deletion', 'wpshadow' ),
					__( 'Ransomware protection', 'wpshadow' ),
					__( 'Compliance-ready (HIPAA, PCI)', 'wpshadow' ),
				),
				'cloud-offload'         => array(
					__( 'Frees up local server storage', 'wpshadow' ),
					__( 'Automatic cloud synchronization', 'wpshadow' ),
					__( 'Access backups from anywhere', 'wpshadow' ),
					__( 'Redundant storage across regions', 'wpshadow' ),
				),
				'scheduled-backups'     => array(
					__( 'Automatic daily/hourly backups', 'wpshadow' ),
					__( 'Customizable retention policies', 'wpshadow' ),
					__( 'Email notifications on completion', 'wpshadow' ),
					__( 'One-click restore from any date', 'wpshadow' ),
				),
			),
			'integration' => array(
				'design-tool-sync'    => array(
					__( 'Direct publish from Canva/Figma', 'wpshadow' ),
					__( 'No manual download/upload', 'wpshadow' ),
					__( 'Automatic version tracking', 'wpshadow' ),
					__( 'Webhook-based automation', 'wpshadow' ),
				),
				'auto-optimization'   => array(
					__( 'Automatic image compression', 'wpshadow' ),
					__( 'Format conversion (WebP, AVIF)', 'wpshadow' ),
					__( 'Responsive image variants', 'wpshadow' ),
					__( 'CDN integration', 'wpshadow' ),
				),
				'version-control'     => array(
					__( 'Track all design iterations', 'wpshadow' ),
					__( 'Compare versions side-by-side', 'wpshadow' ),
					__( 'Rollback to previous versions', 'wpshadow' ),
					__( 'Audit trail for compliance', 'wpshadow' ),
				),
				'webhook-automation'  => array(
					__( 'Real-time updates from design tools', 'wpshadow' ),
					__( 'Automated publishing workflows', 'wpshadow' ),
					__( 'Custom approval processes', 'wpshadow' ),
					__( 'Team collaboration features', 'wpshadow' ),
				),
			),
			'media-image' => array(
				'social-optimization' => array(
					__( 'Automatic sizing for Facebook/Twitter/Instagram', 'wpshadow' ),
					__( 'Correct aspect ratios per platform', 'wpshadow' ),
					__( 'Open Graph metadata generation', 'wpshadow' ),
					__( 'Preview before posting', 'wpshadow' ),
				),
				'brand-overlays'      => array(
					__( 'Automatic logo/watermark application', 'wpshadow' ),
					__( 'Consistent brand colors', 'wpshadow' ),
					__( 'Custom overlay templates', 'wpshadow' ),
					__( 'Bulk processing support', 'wpshadow' ),
				),
				'filter-presets'      => array(
					__( '50+ professional filter presets', 'wpshadow' ),
					__( 'Custom filter creation', 'wpshadow' ),
					__( 'Batch apply to multiple images', 'wpshadow' ),
					__( 'Save favorite combinations', 'wpshadow' ),
				),
				'bulk-processing'     => array(
					__( 'Process 100s of images at once', 'wpshadow' ),
					__( 'Background queue processing', 'wpshadow' ),
					__( 'Progress tracking', 'wpshadow' ),
					__( 'Automatic retry on failure', 'wpshadow' ),
				),
			),
			'media-video' => array(
				'video-transcoding'       => array(
					__( 'Automatic format conversion', 'wpshadow' ),
					__( 'Multiple quality variants (SD/HD/4K)', 'wpshadow' ),
					__( 'Codec optimization (H.264, H.265)', 'wpshadow' ),
					__( 'GPU-accelerated processing', 'wpshadow' ),
				),
				'streaming-optimization'  => array(
					__( 'Adaptive bitrate streaming', 'wpshadow' ),
					__( 'HLS/DASH protocol support', 'wpshadow' ),
					__( 'CDN integration', 'wpshadow' ),
					__( 'Reduces bandwidth by 70%', 'wpshadow' ),
				),
				'thumbnail-generation'    => array(
					__( 'Automatic thumbnail extraction', 'wpshadow' ),
					__( 'Smart frame selection (avoids black frames)', 'wpshadow' ),
					__( 'Custom thumbnail upload', 'wpshadow' ),
					__( 'Animated preview generation', 'wpshadow' ),
				),
				'analytics-tracking'      => array(
					__( 'Play count and engagement metrics', 'wpshadow' ),
					__( 'Heatmap of watch patterns', 'wpshadow' ),
					__( 'Drop-off point identification', 'wpshadow' ),
					__( 'Audience retention reports', 'wpshadow' ),
				),
			),
			'media-doc'   => array(
				'preview-generation'    => array(
					__( 'PDF/DOC preview without plugins', 'wpshadow' ),
					__( 'Thumbnail generation for documents', 'wpshadow' ),
					__( 'Text extraction for search', 'wpshadow' ),
					__( 'Page-by-page navigation', 'wpshadow' ),
				),
				'version-tracking'      => array(
					__( 'Track all document revisions', 'wpshadow' ),
					__( 'Compare versions side-by-side', 'wpshadow' ),
					__( 'Automatic version numbering', 'wpshadow' ),
					__( 'Audit trail for compliance', 'wpshadow' ),
				),
				'collaborative-editing' => array(
					__( 'Multi-user editing support', 'wpshadow' ),
					__( 'Real-time collaboration', 'wpshadow' ),
					__( 'Comment and annotation tools', 'wpshadow' ),
					__( 'Conflict resolution', 'wpshadow' ),
				),
				'search-indexing'       => array(
					__( 'Full-text search inside documents', 'wpshadow' ),
					__( 'OCR for scanned documents', 'wpshadow' ),
					__( 'Metadata extraction', 'wpshadow' ),
					__( 'Advanced filtering', 'wpshadow' ),
				),
			),
		);

		if ( isset( $benefits[ $product ][ $feature ] ) ) {
			return $benefits[ $product ][ $feature ];
		}

		return array();
	}

	/**
	 * Track that we showed an upgrade path to the user
	 *
	 * Philosophy #9: Everything Has a KPI
	 *
	 * @since 1.6093.1200
	 * @param  string $finding_id Finding identifier.
	 * @param  string $product    Product slug.
	 * @param  string $feature    Feature slug.
	 * @return void
	 */
	private static function track_upgrade_path_shown( $finding_id, $product, $feature ) {
		// Track in activity log.
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'upgrade_path_shown',
				__( 'Upgrade path prompt shown', 'wpshadow' ),
				'upsell',
				array(
					'finding_id' => $finding_id,
					'product'    => $product,
					'feature'    => $feature,
					'timestamp'  => current_time( 'mysql' ),
				)
			);
		}

		// Increment counter for analytics.
		$counter_key = 'wpshadow_upgrade_path_shown_' . $product;
		$count       = get_option( $counter_key, 0 );
		update_option( $counter_key, $count + 1, false );
	}

	/**
	 * Track when user clicks "Learn More" on upgrade path
	 *
	 * Called via AJAX from frontend.
	 *
	 * @since 1.6093.1200
	 * @param  string $finding_id Finding identifier.
	 * @param  string $product    Product slug.
	 * @return void
	 */
	public static function track_upgrade_path_clicked( $finding_id, $product ) {
		// Track in activity log.
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'upgrade_path_clicked',
				__( 'Upgrade path prompt clicked', 'wpshadow' ),
				'upsell',
				array(
					'finding_id' => $finding_id,
					'product'    => $product,
					'timestamp'  => current_time( 'mysql' ),
				)
			);
		}

		// Increment counter for conversion funnel analytics.
		$counter_key = 'wpshadow_upgrade_path_clicked_' . $product;
		$count       = get_option( $counter_key, 0 );
		update_option( $counter_key, $count + 1, false );
	}

	/**
	 * Get conversion analytics for upgrade paths
	 *
	 * Used in dashboard to show effectiveness of upgrade prompts.
	 *
	 * @since 1.6093.1200
	 * @return array Analytics data.
	 */
	public static function get_analytics() {
		$products  = array_keys( self::$products );
		$analytics = array();

		foreach ( $products as $product ) {
			$shown_key   = 'wpshadow_upgrade_path_shown_' . $product;
			$clicked_key = 'wpshadow_upgrade_path_clicked_' . $product;

			$shown   = get_option( $shown_key, 0 );
			$clicked = get_option( $clicked_key, 0 );

			$analytics[ $product ] = array(
				'shown'           => $shown,
				'clicked'         => $clicked,
				'click_rate'      => $shown > 0 ? round( ( $clicked / $shown ) * 100, 2 ) : 0,
				'product_name'    => self::$products[ $product ]['name'],
			);
		}

		return $analytics;
	}

	/**
	 * Check if user has a specific pro product
	 *
	 * @since 1.6093.1200
	 * @param  string $product Product slug.
	 * @return bool Whether user has the product.
	 */
	public static function has_pro_product( $product ) {
		// Check if pro module is active.
		$plugin_map = array(
			'vault'       => 'wpshadow-pro-vault/wpshadow-pro-vault.php',
			'integration' => 'wpshadow-pro-integration/wpshadow-pro-integration.php',
			'media-image' => 'wpshadow-pro-wpadmin-media-image/wpshadow-pro-wpadmin-media-image.php',
			'media-video' => 'wpshadow-pro-wpadmin-media-video/wpshadow-pro-wpadmin-media-video.php',
			'media-doc'   => 'wpshadow-pro-wpadmin-media-document/wpshadow-pro-wpadmin-media-document.php',
		);

		if ( isset( $plugin_map[ $product ] ) ) {
			return is_plugin_active( $plugin_map[ $product ] );
		}

		return false;
	}
}
