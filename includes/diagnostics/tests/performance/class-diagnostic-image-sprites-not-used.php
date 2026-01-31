<?php
/**
 * Image Sprites Not Used Diagnostic
 *
 * Checks if image sprites are used.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Sprites Not Used Diagnostic Class
 *
 * Detects missing image sprite usage.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Image_Sprites_Not_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-sprites-not-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Sprites Not Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image sprites are used';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for sprite-based icon usage
		if ( ! has_filter( 'wp_head', 'use_icon_sprites' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image sprites are not used. Combine multiple small images (icons, buttons) into a single sprite sheet to reduce HTTP requests and improve page load times.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-sprites-not-used',
			);
		}

		return null;
	}
}
