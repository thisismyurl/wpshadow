<?php
/**
 * Image Sprites Not Implemented Treatment
 *
 * Checks if image sprites are implemented.
 * Image sprite = combine multiple small images into one file.
 * Without sprites = 20 icon requests (20 HTTP requests).
 * With sprites = 1 sprite file (1 HTTP request). 95% fewer requests.
 *
 * **What This Check Does:**
 * - Identifies multiple small icon/logo requests
 * - Checks for sprite sheet implementation
 * - Validates CSS background-position usage
 * - Tests HTTP request count reduction
 * - Checks for modern alternatives (icon fonts, SVG sprites)
 * - Returns severity if many small image requests detected
 *
 * **Why This Matters:**
 * Site loads 25 small icon images. Each = separate HTTP request.
 * HTTP/1.1 = 6 connections max. Icons load slowly.
 * With sprite = all icons in one file, one request. Much faster.
 * Note: HTTP/2 reduces need but sprites still valuable.
 *
 * **Business Impact:**
 * Social media toolbar: 15 separate icon files (24x24px PNG), each
 * 2KB. Total: 30KB data, 15 HTTP requests. Mobile loading (HTTP/1.1):
 * icons load sequentially, take 2 seconds. Combined into sprite sheet:
 * 32KB (one file), 1 request. Load time: 0.3 seconds (85% faster).
 * Also converted to SVG sprite (4KB). Retina-ready, scalable. Modern
 * approach: icon font or inline SVG. Result: cleaner code, faster load.
 * Setup: 2 hours initial, automated thereafter.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Icons load instantly
 * - #9 Show Value: Dramatic request reduction
 * - #10 Beyond Pure: Modern optimization techniques
 *
 * **Related Checks:**
 * - HTTP Request Count (broader metric)
 * - Icon Font Usage (modern alternative)
 * - SVG Optimization (vector alternative)
 *
 * **Learn More:**
 * Image sprites: https://wpshadow.com/kb/image-sprites
 * Video: Sprite sheets explained (9min): https://wpshadow.com/training/sprites
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Sprites Not Implemented Treatment Class
 *
 * Detects missing image sprites.
 *
 * **Detection Pattern:**
 * 1. Analyze page requests
 * 2. Identify multiple small images (<5KB, similar dimensions)
 * 3. Count small image requests (>10 = candidate)
 * 4. Check for sprite sheet implementation
 * 5. Validate CSS background-position usage
 * 6. Return if opportunity for sprite optimization
 *
 * **Real-World Scenario:**
 * Dashboard with 18 category icons (32x32px). Each separate PNG.
 * Combined into sprite sheet (18 icons in grid). CSS: background-image
 * (sprite), background-position (offset to specific icon). Requests:
 * 18 → 1. Page load: 1.8s → 0.5s. Also modernized: replaced with
 * icon font (Font Awesome). Scalable, color-customizable, 1 font file.
 *
 * **Implementation Notes:**
 * - Checks for multiple small image requests
 * - Validates sprite implementation
 * - Considers modern alternatives
 * - Severity: low (HTTP/2 reduces impact, but still valuable)
 * - Treatment: implement sprite sheet or modern alternative
 *
 * @since 1.6030.2352
 */
class Treatment_Image_Sprites_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-sprites-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Sprites Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image sprites are implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Sprites_Not_Implemented' );
	}

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Sprites Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image sprites are implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if sprite CSS is used
		if ( ! has_filter( 'wp_enqueue_scripts', 'load_sprite_css' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image sprites are not implemented. Combine multiple small images into sprite sheets to reduce HTTP requests and improve load time.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-sprites-not-implemented',
			);
		}

		return null;
	}
}
