<?php
/**
 * Multi-Region Deployment Diagnostic
 *
 * Checks if infrastructure is deployed across multiple geographic regions.
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
 * Multi-Region Deployment Diagnostic Class
 *
 * Verifies that WordPress is deployed across multiple geographic regions for
 * improved resilience, disaster recovery, and global performance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Multi_Region_Deployment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multi-region-deployment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multi-Region Deployment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if infrastructure is deployed across multiple geographic regions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the multi-region deployment diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if multi-region gaps detected, null otherwise.
	 */
	public static function check() {
		$region_indicators = array();
		$detected_regions  = array();

		// Check for AWS region indicators.
		if ( defined( 'AWS_REGION' ) ) {
			$detected_regions[] = 'AWS: ' . AWS_REGION;
		}
		if ( getenv( 'AWS_REGION' ) ) {
			$detected_regions[] = 'AWS: ' . getenv( 'AWS_REGION' );
		}
		if ( getenv( 'AWS_DEFAULT_REGION' ) ) {
			$detected_regions[] = 'AWS: ' . getenv( 'AWS_DEFAULT_REGION' );
		}

		// Check for GCP region indicators.
		if ( getenv( 'GCP_REGION' ) || getenv( 'GOOGLE_CLOUD_REGION' ) ) {
			$detected_regions[] = 'GCP: ' . ( getenv( 'GCP_REGION' ) ?: getenv( 'GOOGLE_CLOUD_REGION' ) );
		}

		// Check for Azure region indicators.
		if ( getenv( 'AZURE_REGION' ) || getenv( 'WEBSITE_AZURE_REGION' ) ) {
			$detected_regions[] = 'Azure: ' . ( getenv( 'AZURE_REGION' ) ?: getenv( 'WEBSITE_AZURE_REGION' ) );
		}

		// Check for CloudFlare multi-region (via headers).
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$region_indicators['cloudflare'] = __( 'Cloudflare global CDN active', 'wpshadow' );
		}

		// Check for multi-region database indicators.
		if ( defined( 'DB_HOST_MULTI_REGION' ) || defined( 'DB_REPLICA_REGIONS' ) ) {
			$region_indicators['db_multi_region'] = __( 'Multi-region database configured', 'wpshadow' );
		}

		// Check for geo-distributed storage.
		if ( defined( 'S3_MULTI_REGION' ) || 
			 defined( 'CLOUDFRONT_DISTRIBUTION' ) ||
			 class_exists( 'DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item' ) ) {
			$region_indicators['storage'] = __( 'Geo-distributed storage detected', 'wpshadow' );
		}

		// Deduplicate detected regions.
		$detected_regions = array_unique( $detected_regions );
		$region_count     = count( $detected_regions );

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and only single region.
		if ( $is_enterprise && $region_count <= 1 && empty( $region_indicators ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Enterprise site is deployed in a single region. Multi-region deployment improves disaster recovery and global performance.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multi-region-deployment',
				'context'      => array(
					'detected_regions'  => $detected_regions,
					'region_indicators' => $region_indicators,
				),
			);
		}

		// If some multi-region indicators but incomplete.
		if ( $region_count === 1 && ! empty( $region_indicators ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: detected region */
					__( 'Partial multi-region setup detected (Primary region: %s). Consider expanding to additional regions for better resilience.', 'wpshadow' ),
					$detected_regions[0]
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multi-region-deployment',
				'context'      => array(
					'detected_regions'  => $detected_regions,
					'region_indicators' => $region_indicators,
				),
			);
		}

		// If multi-region is detected, check for best practices.
		if ( $region_count > 1 ) {
			$warnings = array();

			// Check if object cache is configured (critical for multi-region).
			if ( ! wp_using_ext_object_cache() ) {
				$warnings[] = __( 'External object cache recommended for multi-region consistency', 'wpshadow' );
			}

			// Check if CDN is configured.
			if ( empty( $region_indicators['cloudflare'] ) && ! defined( 'CLOUDFRONT_DISTRIBUTION' ) ) {
				$warnings[] = __( 'CDN recommended for optimal multi-region performance', 'wpshadow' );
			}

			if ( ! empty( $warnings ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: number of regions, 2: list of warnings */
						__( 'Multi-region deployment active (%1$d regions detected). Recommendations: %2$s', 'wpshadow' ),
						$region_count,
						implode( ', ', $warnings )
					),
					'severity'     => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/multi-region-deployment',
					'context'      => array(
						'detected_regions'  => $detected_regions,
						'region_indicators' => $region_indicators,
						'warnings'          => $warnings,
					),
				);
			}
		}

		return null; // Multi-region is properly configured or not needed.
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since 1.6093.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			is_multisite() && get_blog_count() > 50,
		);

		return in_array( true, $enterprise_indicators, true );
	}
}
