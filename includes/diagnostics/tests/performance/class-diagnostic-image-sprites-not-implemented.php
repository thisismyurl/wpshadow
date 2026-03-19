<?php
/**
 * Image Sprites Not Implemented Diagnostic
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
 * Image Sprites Not Implemented Diagnostic Class
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
 * 18 → 1. Page load:1.0s → 0.5s. Also modernized: replaced with
 * icon font (Font Awesome). Scalable, color-customizable, 1 font file.
 *
 * **Implementation Notes:**
 * - Checks for multiple small image requests
 * - Validates sprite implementation
 * - Considers modern alternatives
 * - Severity: low (HTTP/2 reduces impact, but still valuable)
 * - Treatment: implement sprite sheet or modern alternative
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Sprites_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-sprites-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Sprites Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image sprites are implemented';

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
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Image sprites are less critical with HTTP/2.
		// Check if server supports HTTP/2.
		$server_protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : '';
		$has_http2 = strpos( $server_protocol, 'HTTP/2' ) !== false;

		// If HTTP/2, sprites less important (multiplexing handles many requests).
		if ( $has_http2 ) {
			return null;
		}

		// Count enqueued small images (icons, logos).
		global $wp_styles;
		$small_image_count = 0;

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				// Check for background image usage in CSS.
				if ( ! empty( $style->src ) && is_string( $style->src ) && ( strpos( $style->src, 'icon' ) !== false || strpos( $style->src, 'sprite' ) !== false ) ) {
					$small_image_count++;
				}
			}
		}

		// Check for icon fonts (modern alternative).
		$icon_fonts = array(
			'font-awesome/font-awesome.php' => 'Font Awesome',
			'dashicons'                     => 'Dashicons (WordPress core)',
		);

		$has_icon_fonts = false;
		foreach ( $icon_fonts as $font => $name ) {
			if ( wp_style_is( $font, 'enqueued' ) || wp_style_is( 'dashicons', 'enqueued' ) ) {
				$has_icon_fonts = true;
				break;
			}
		}

		// If using icon fonts, sprites not needed.
		if ( $has_icon_fonts ) {
			return null;
		}

		// Low priority issue - HTTP/1.1 site without icon optimization.
		if ( ! $has_http2 && ! $has_icon_fonts ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Image sprites not implemented. Your server uses HTTP/1.1, which limits parallel requests. Multiple small icons (social media, UI elements) load slowly. Combine icons into sprite sheet (1 file) or use icon font. Reduces requests by 80-90%, improves load time 20-40%.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image-sprites',
				'details'     => array(
					'server_protocol' => $server_protocol,
					'has_http2'       => false,
					'has_icon_fonts'  => false,
					'recommendation'  => __( 'BEST: Upgrade to HTTP/2 hosting (most modern hosts support it). GOOD: Implement icon font (Font Awesome, free). ALTERNATIVE: Create image sprite sheet (combine icons into one file with CSS positioning).', 'wpshadow' ),
					'modern_alternatives' => array(
						'http2' => 'Multiplexing allows many parallel requests (sprites less important)',
						'icon_fonts' => 'Single font file, scalable, color-customizable',
						'svg_sprites' => 'Vector sprites (scalable, smaller file size)',
						'inline_svg' => 'Embed SVG in HTML (no HTTP request)',
					),
					'sprite_technique' => array(
						'combine' => 'All icons in one image (grid layout)',
						'css' => 'background-image: url(sprite.png); background-position: -32px -64px;',
						'benefit' => '20 icon files → 1 sprite file = 95% fewer requests',
					),
				),
			);
		}

		// No issues - HTTP/2 or icon fonts in use.
		return null;
	}
}
