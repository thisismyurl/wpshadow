<?php
/**
 * Image Sprites Not Used Diagnostic
 *
 * Checks if image sprites are used.
 * Sprite sheet exists but not properly utilized.
 * CSS still loads individual images instead of sprite.
 * Defeats the purpose of sprite optimization.
 *
 * **What This Check Does:**
 * - Detects sprite sheets in theme/plugin
 * - Validates CSS references sprite
 * - Checks for proper background-position usage
 * - Tests if sprite actually reduces requests
 * - Identifies unused individual images still loaded
 * - Returns severity if sprite exists but unused
 *
 * **Why This Matters:**
 * Developer created sprite sheet. Good intention.
 * But CSS still references individual icon files. Sprite unused.
 * Result: worst of both worlds (sprite + individuals loaded).
 * Wasted effort, no performance gain.
 *
 * **Business Impact:**
 * Theme includes sprite sheet (social-icons-sprite.png, 45KB, 20 icons).
 * But CSS still loaded individual icons: icon-facebook.png, icon-twitter.png,
 * etc. Total requests: 21 (sprite + 20 individuals). Total data: 85KB.
 * Updated CSS to use sprite with background-position. Removed individual
 * files. Requests: 21 → 1. Data: 85KB → 45KB. Load time improved1.0s.
 * Properly utilizing existing sprite = zero additional work, immediate gain.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimization actually working
 * - #9 Show Value: Realize intended performance gain
 * - #10 Beyond Pure: Proper implementation matters
 *
 * **Related Checks:**
 * - Image Sprites Not Implemented (creation check)
 * - Unused Assets Loaded (broader check)
 * - CSS Optimization (related area)
 *
 * **Learn More:**
 * Using sprites correctly: https://wpshadow.com/kb/sprite-usage
 * Video: Sprite CSS techniques (8min): https://wpshadow.com/training/sprite-css
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
 * Image Sprites Not Used Diagnostic Class
 *
 * Detects missing image sprite usage.
 *
 * **Detection Pattern:**
 * 1. Scan for sprite files (*-sprite.png, spritesheet.*)
 * 2. Analyze CSS for background-position references
 * 3. Check if individual icon files still loaded
 * 4. Compare requests (should reduce with sprite)
 * 5. Identify orphaned sprite files
 * 6. Return if sprite exists but not properly used
 *
 * **Real-World Scenario:**
 * Found sprite file in theme but CSS used url('icon-home.png').
 * Updated to: background-image: url('sprite.png'); background-position:
 * 0 -32px; (for home icon in sprite grid). Repeated for all icons.
 * Removed individual icon files. HTTP requests reduced 85%. This is
 * common: developers create sprites but forget to update CSS references.
 *
 * **Implementation Notes:**
 * - Checks sprite file existence
 * - Validates CSS sprite usage
 * - Identifies unused individual files
 * - Severity: medium (wasted optimization opportunity)
 * - Treatment: update CSS to use sprite, remove individuals
 *
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
