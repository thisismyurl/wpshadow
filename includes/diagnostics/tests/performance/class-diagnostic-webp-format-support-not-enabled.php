<?php
/**
 * WebP Format Support Not Enabled Diagnostic
 *
 * Checks if WebP image format support is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WebP Format Support Not Enabled Diagnostic Class
 *
 * Detects missing WebP image support.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WebP_Format_Support_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-format-support-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Format Support Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebP format is supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if server supports WebP
		$imagick = extension_loaded( 'imagick' );
		$gd      = extension_loaded( 'gd' );

		if ( ! $imagick && ! $gd ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Neither GD nor ImageMagick extension is available. WebP image conversion cannot be performed.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/webp-format-support-not-enabled',
			);
		}

		// Check for WebP conversion plugins
		$webp_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'imagify/imagify.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'optimus/optimus.php',
		);

		$webp_active = false;
		foreach ( $webp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$webp_active = true;
				break;
			}
		}

		if ( ! $webp_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WebP image format is not enabled. WebP images are 25-35% smaller than JPEG, significantly improving load times.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/webp-format-support-not-enabled',
			);
		}

		return null;
	}
}
