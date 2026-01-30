<?php
/**
 * Ewww Image Optimizer Api Diagnostic
 *
 * Ewww Image Optimizer Api detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.750.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ewww Image Optimizer Api Diagnostic Class
 *
 * @since 1.750.0000
 */
class Diagnostic_EwwwImageOptimizerApi extends Diagnostic_Base {

	protected static $slug = 'ewww-image-optimizer-api';
	protected static $title = 'Ewww Image Optimizer Api';
	protected static $description = 'Ewww Image Optimizer Api detected';
	protected static $family = 'security';

	public static function check() {
		// Check if EWWW is installed
		if ( ! defined( 'EWWW_IMAGE_OPTIMIZER_VERSION' ) && ! class_exists( 'EWWW_Image_Optimizer' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Get EWWW settings
		$ewww_cloud_key = get_option( 'ewww_image_optimizer_cloud_key', '' );

		// Check if API key is exposed in code
		if ( ! empty( $ewww_cloud_key ) ) {
			// Check if key is stored in wp-config (more secure)
			if ( ! defined( 'EWWW_IMAGE_OPTIMIZER_CLOUD_KEY' ) ) {
				$issues[] = 'api_key_in_database';
				$threat_level += 30;
			}
		}

		// Check compression level
		$compression_level = get_option( 'ewww_image_optimizer_jpg_level', 10 );
		if ( $compression_level < 30 ) {
			$issues[] = 'compression_too_low';
			$threat_level += 10;
		}

		// Check cloud optimization
		$cloud_enabled = get_option( 'ewww_image_optimizer_cloud_enabled', false );
		if ( $cloud_enabled && empty( $ewww_cloud_key ) ) {
			$issues[] = 'cloud_enabled_without_key';
			$threat_level += 25;
		}

		// Check API key validity
		if ( ! empty( $ewww_cloud_key ) ) {
			$api_verify = get_option( 'ewww_image_optimizer_cloud_exceeded', 0 );
			if ( $api_verify > 0 ) {
				$issues[] = 'api_quota_exceeded';
				$threat_level += 20;
			}
		}

		// Check WebP conversion
		$webp_enabled = get_option( 'ewww_image_optimizer_webp', false );
		if ( ! $webp_enabled ) {
			$issues[] = 'webp_conversion_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of API and optimization issues */
				__( 'EWWW Image Optimizer has configuration issues: %s. This exposes API credentials and reduces optimization effectiveness.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ewww-image-optimizer-api',
			);
		}
		
		return null;
	}
}
