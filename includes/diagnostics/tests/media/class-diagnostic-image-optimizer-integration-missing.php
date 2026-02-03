<?php
/**
 * Image Optimizer Integration Missing Diagnostic
 *
 * Detects when no image optimization plugin is active or properly configured,
 * leading to unnecessarily large image file sizes and slower page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimizer Integration Missing Diagnostic Class
 *
 * Checks if a reputable image optimization plugin is active and properly configured.
 * Image optimization is critical for page speed and Core Web Vitals.
 *
 * Based on EWWW Image Optimizer integration testing patterns.
 *
 * @since 1.6033.1500
 */
class Diagnostic_Image_Optimizer_Integration_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimizer-integration-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Plugin Missing or Misconfigured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for active image optimization integration to improve page load speed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if a reputable image optimizer plugin is active and configured.
	 *
	 * @since  1.6033.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$optimizer_status = self::get_optimizer_status();

		if ( $optimizer_status['active'] && $optimizer_status['configured'] ) {
			// Optimizer is active and configured properly.
			return null;
		}

		$description = '';
		$severity = 'medium';
		$threat_level = 40;

		if ( ! $optimizer_status['active'] ) {
			$description = __( 'No image optimization plugin is currently active. Unoptimized images can be 60-80% larger than necessary, significantly slowing page load times and hurting Core Web Vitals scores.', 'wpshadow' );
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( ! $optimizer_status['configured'] ) {
			$description = sprintf(
				/* translators: %s: plugin name */
				__( '%s is active but not properly configured. Set up API keys or optimization settings to start reducing image file sizes.', 'wpshadow' ),
				$optimizer_status['plugin']
			);
			$severity = 'low';
			$threat_level = 30;
		}

		return array(
			'id'                    => self::$slug,
			'title'                 => self::$title,
			'description'           => $description,
			'severity'              => $severity,
			'threat_level'          => $threat_level,
			'auto_fixable'          => false,
			'optimizer_plugin'      => $optimizer_status['plugin'] ?? 'None',
			'configured'            => $optimizer_status['configured'],
			'recommended_plugins'   => array(
				'EWWW Image Optimizer' => 'https://wordpress.org/plugins/ewww-image-optimizer/',
				'ShortPixel'           => 'https://wordpress.org/plugins/shortpixel-image-optimiser/',
				'Imagify'              => 'https://wordpress.org/plugins/imagify/',
				'WP Smush'             => 'https://wordpress.org/plugins/wp-smushit/',
			),
			'expected_benefits'     => '40-80% image file size reduction, faster page loads, better Core Web Vitals',
			'kb_link'               => 'https://wpshadow.com/kb/image-optimization-setup',
		);
	}

	/**
	 * Get optimizer plugin status.
	 *
	 * @since  1.6033.1500
	 * @return array {
	 *     Optimizer status information.
	 *
	 *     @type bool        $active     Whether an optimizer is active.
	 *     @type bool        $configured Whether the optimizer is configured.
	 *     @type string|null $plugin     Name of active plugin.
	 * }
	 */
	private static function get_optimizer_status() {
		// Check EWWW Image Optimizer.
		if ( is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			// EWWW can work with local tools or cloud API.
			$cloud_key = get_option( 'ewww_image_optimizer_cloud_key', '' );
			$png_level = get_option( 'ewww_image_optimizer_png_level', 0 );
			$jpg_level = get_option( 'ewww_image_optimizer_jpg_level', 0 );

			$configured = ! empty( $cloud_key ) || $png_level > 0 || $jpg_level > 0;

			return array(
				'active'     => true,
				'configured' => $configured,
				'plugin'     => 'EWWW Image Optimizer',
			);
		}

		// Check ShortPixel.
		if ( is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) ) {
			$api_key = get_option( 'wp-shortpixel-apiKey', '' );
			$configured = ! empty( $api_key );

			return array(
				'active'     => true,
				'configured' => $configured,
				'plugin'     => 'ShortPixel',
			);
		}

		// Check Imagify.
		if ( is_plugin_active( 'imagify/imagify.php' ) ) {
			$api_key = get_option( 'imagify_settings', array() );
			$configured = ! empty( $api_key['api_key'] ?? '' );

			return array(
				'active'     => true,
				'configured' => $configured,
				'plugin'     => 'Imagify',
			);
		}

		// Check TinyPNG.
		if ( is_plugin_active( 'tiny-compress-images/tiny-compress-images.php' ) ) {
			$api_key = get_option( 'tinypng_api_key', '' );
			$configured = ! empty( $api_key );

			return array(
				'active'     => true,
				'configured' => $configured,
				'plugin'     => 'TinyPNG',
			);
		}

		// Check WP Smush.
		if ( is_plugin_active( 'wp-smushit/wp-smush.php' ) ) {
			$auto_smush = get_option( 'wp-smush-auto', false );
			// Smush works without API key for basic features.
			$configured = true;

			return array(
				'active'     => true,
				'configured' => $configured,
				'plugin'     => 'WP Smush',
			);
		}

		// Check Optimole.
		if ( is_plugin_active( 'optimole-wp/optimole-wp.php' ) ) {
			$api_key = get_option( 'optimole_api_key', '' );
			$configured = ! empty( $api_key );

			return array(
				'active'     => true,
				'configured' => $configured,
				'plugin'     => 'Optimole',
			);
		}

		return array(
			'active'     => false,
			'configured' => false,
			'plugin'     => null,
		);
	}
}
