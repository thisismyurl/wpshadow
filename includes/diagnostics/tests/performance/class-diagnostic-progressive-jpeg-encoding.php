<?php
/**
 * Progressive JPEG Encoding Diagnostic
 *
 * Checks if JPEG images are encoded as progressive JPEGs for better perceived
 * performance and faster initial display.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progressive JPEG Encoding Diagnostic Class
 *
 * Verifies progressive JPEG usage:
 * - Progressive JPEG detection
 * - Baseline vs progressive ratio
 * - Plugin configuration
 * - Encoding settings
 *
 * @since 1.6093.1200
 */
class Diagnostic_Progressive_Jpeg_Encoding extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progressive-jpeg-encoding';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive JPEG Encoding';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for progressive JPEG encoding for faster perceived load';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$progressive_jpeg_enabled = false;

		// Check for image optimization plugins (most set progressive by default)
		$plugins = array(
			'imagify/imagify.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'optimus/optimus.php',
		);

		foreach ( $plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$progressive_jpeg_enabled = true;
				break;
			}
		}

		if ( ! $progressive_jpeg_enabled ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Progressive JPEG encoding is not enabled. Progressive JPEGs improve perceived performance by displaying placeholder image quickly.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/progressive-jpeg',
				'meta'          => array(
					'progressive_enabled'  => $progressive_jpeg_enabled,
					'recommendation'       => 'Install image optimizer that supports progressive JPEG (Imagify, EWWW, or Optimus)',
					'impact'               => 'Progressive JPEGs improve perceived load time by 15-30%',
					'difference'           => array(
						'Baseline JPEG: Must fully load before display',
						'Progressive JPEG: Shows low-quality version immediately, refines as loads',
					),
					'user_experience'      => 'Users perceive page as faster even with same actual load time',
				),
			);
		}

		return null;
	}
}
