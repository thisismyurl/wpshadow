<?php
/**
 * Image Resizing Configuration Missing Diagnostic
 *
 * Detects when WordPress image resizing is not properly configured,
 * leading to unnecessarily large images being served to users.
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
 * Image Resizing Misconfigured Diagnostic Class
 *
 * Checks if WordPress image resizing is properly configured to generate
 * appropriately-sized images for different contexts (thumbnails, medium, large).
 * Proper resizing prevents serving full-resolution images where smaller
 * versions would suffice.
 *
 * Based on EWWW Image Optimizer resizing validation patterns.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Image_Resizing_Misconfigured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-resizing-misconfigured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Resizing Configuration Missing or Misconfigured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WordPress image resizing settings for optimal responsive image delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if WordPress image sizes are properly configured.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$resize_config = self::get_resize_configuration();

		if ( $resize_config['properly_configured'] ) {
			// Image resizing is properly configured.
			return null;
		}

		$description = '';
		$severity = 'low';
		$threat_level = 20;

		if ( $resize_config['all_disabled'] ) {
			$description = __( 'WordPress image resizing is completely disabled. All images are served at full resolution regardless of context, wasting bandwidth and slowing page loads. Enable at least medium and large image sizes to improve performance.', 'wpshadow' );
			$severity = 'medium';
			$threat_level = 40;
		} elseif ( ! empty( $resize_config['issues'] ) ) {
			$description = sprintf(
				/* translators: %s: list of issues */
				__( 'WordPress image resizing has configuration issues: %s. Properly configured image sizes ensure responsive images are served efficiently across devices.', 'wpshadow' ),
				implode( ', ', $resize_config['issues'] )
			);
			$severity = 'low';
			$threat_level = 25;
		}

		return array(
			'id'                    => self::$slug,
			'title'                 => self::$title,
			'description'           => $description,
			'severity'              => $severity,
			'threat_level'          => $threat_level,
			'auto_fixable'          => true,
			'current_config'        => $resize_config['sizes'],
			'recommended_config'    => array(
				'thumbnail' => array( 'width' => 150, 'height' => 150, 'crop' => true ),
				'medium'    => array( 'width' => 300, 'height' => 300, 'crop' => false ),
				'large'     => array( 'width' => 1024, 'height' => 1024, 'crop' => false ),
			),
			'issues'                => $resize_config['issues'],
			'expected_benefits'     => 'Reduce bandwidth by 50-80% for images viewed on mobile devices',
			'kb_link'               => 'https://wpshadow.com/kb/wordpress-image-resizing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Get image resize configuration.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Resize configuration information.
	 *
	 *     @type bool  $properly_configured Whether resizing is properly configured.
	 *     @type bool  $all_disabled        Whether all image sizes are disabled.
	 *     @type array $sizes               Current image size settings.
	 *     @type array $issues              List of configuration issues.
	 * }
	 */
	private static function get_resize_configuration() {
		$sizes = array(
			'thumbnail' => array(
				'width'  => (int) get_option( 'thumbnail_size_w', 150 ),
				'height' => (int) get_option( 'thumbnail_size_h', 150 ),
				'crop'   => (bool) get_option( 'thumbnail_crop', true ),
			),
			'medium'    => array(
				'width'  => (int) get_option( 'medium_size_w', 300 ),
				'height' => (int) get_option( 'medium_size_h', 300 ),
				'crop'   => false,
			),
			'large'     => array(
				'width'  => (int) get_option( 'large_size_w', 1024 ),
				'height' => (int) get_option( 'large_size_h', 1024 ),
				'crop'   => false,
			),
		);

		$issues = array();
		$all_disabled = true;

		// Check if all sizes are disabled (set to 0).
		foreach ( $sizes as $size_name => $size_data ) {
			if ( $size_data['width'] > 0 || $size_data['height'] > 0 ) {
				$all_disabled = false;
			}
		}

		if ( $all_disabled ) {
			$issues[] = __( 'All image sizes disabled', 'wpshadow' );
			return array(
				'properly_configured' => false,
				'all_disabled'        => true,
				'sizes'               => $sizes,
				'issues'              => $issues,
			);
		}

		// Check for specific configuration issues.
		if ( 0 === $sizes['medium']['width'] && 0 === $sizes['medium']['height'] ) {
			$issues[] = __( 'Medium size disabled', 'wpshadow' );
		}

		if ( 0 === $sizes['large']['width'] && 0 === $sizes['large']['height'] ) {
			$issues[] = __( 'Large size disabled', 'wpshadow' );
		}

		// Check for unreasonably small sizes.
		if ( $sizes['medium']['width'] > 0 && $sizes['medium']['width'] < 200 ) {
			$issues[] = __( 'Medium size unusually small (< 200px)', 'wpshadow' );
		}

		if ( $sizes['large']['width'] > 0 && $sizes['large']['width'] < 800 ) {
			$issues[] = __( 'Large size unusually small (< 800px)', 'wpshadow' );
		}

		// Check for unreasonably large sizes (defeats the purpose).
		if ( $sizes['medium']['width'] > 1000 ) {
			$issues[] = __( 'Medium size too large (> 1000px)', 'wpshadow' );
		}

		if ( $sizes['large']['width'] > 2000 ) {
			$issues[] = __( 'Large size too large (> 2000px)', 'wpshadow' );
		}

		$properly_configured = empty( $issues );

		return array(
			'properly_configured' => $properly_configured,
			'all_disabled'        => false,
			'sizes'               => $sizes,
			'issues'              => $issues,
		);
	}
}
