<?php
/**
 * Image Srcset Not Configured Diagnostic
 *
 * Checks if responsive images srcset is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Srcset Not Configured Diagnostic Class
 *
 * Detects missing responsive image configuration.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Image_Srcset_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-srcset-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Srcset Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if responsive images are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if theme supports featured image sizes
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Responsive image srcset is not configured. Enable featured images and multiple sizes to optimize for all devices.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-srcset-not-configured',
			);
		}

		return null;
	}
}
