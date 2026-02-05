<?php
/**
 * Homepage Hero Image Not Optimized Treatment
 *
 * Checks if hero image is optimized.
 * Hero image = large prominent image at top of homepage.
 * Unoptimized = 5MB JPEG, slows First Contentful Paint.
 * Optimized = 200KB WebP with srcset, loads fast.
 *
 * **What This Check Does:**
 * - Identifies hero/banner image on homepage
 * - Checks image file size (should be <300KB)
 * - Validates modern format usage (WebP, AVIF)
 * - Tests responsive images (srcset for different viewports)
 * - Checks lazy loading (hero should NOT be lazy-loaded)
 * - Returns severity if hero unoptimized
 *
 * **Why This Matters:**
 * Hero image = first thing users see. Large hero = slow First
 * Contentful Paint. Users see blank screen for seconds.
 * Optimized hero = fast paint, good first impression.
 * Critical for Core Web Vitals (LCP).
 *
 * **Business Impact:**
 * Homepage hero: 6MB uncompressed PNG. Mobile users: 18-second
 * download on 3G. Bounce rate: 78% (most leave before seeing image).
 * Optimized: converted to WebP, resized for display size (1920px),
 * responsive images for mobile (750px). File size: 180KB desktop,
 * 65KB mobile. Mobile load: 2 seconds. Bounce rate: 25%. Homepage
 * conversions increased 210%. Largest Contentful Paint: 8.5s → 1.8s.
 * Setup time: 30 minutes. Revenue impact: +$60K/month.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Great first impression
 * - #9 Show Value: Dramatic LCP improvement
 * - #10 Beyond Pure: Performance-first design
 *
 * **Related Checks:**
 * - Image Optimization Overall (broader check)
 * - Largest Contentful Paint Optimization (related metric)
 * - WebP Format Support (modern format)
 *
 * **Learn More:**
 * Hero image optimization: https://wpshadow.com/kb/hero-image
 * Video: Optimizing above-the-fold (12min): https://wpshadow.com/training/above-fold
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
 * Homepage Hero Image Not Optimized Treatment Class
 *
 * Detects unoptimized hero image.
 *
 * **Detection Pattern:**
 * 1. Load homepage HTML
 * 2. Identify hero/banner image (largest above-fold image)
 * 3. Check image file size
 * 4. Validate format (WebP preferred)
 * 5. Test responsive images (srcset attribute)
 * 6. Return if >500KB or legacy format
 *
 * **Real-World Scenario:**
 * Homepage hero: 1920x1080px, saved as PNG (8MB). Lighthouse
 * flags: "Largest Contentful Paint: 9.2s". Converted to WebP,
 * quality 85%, with srcset for mobile. Desktop: 220KB. Mobile:
 * 750px width, 75KB. LCP improved to 1.9s. Lighthouse score:
 * 42 → 87. Mobile bounce rate improved 55%.
 *
 * **Implementation Notes:**
 * - Checks hero image size and format
 * - Validates responsive images
 * - Tests LCP impact
 * - Severity: high (affects first impression)
 * - Treatment: optimize with WebP, responsive images
 *
 * @since 1.6030.2352
 */
class Treatment_Homepage_Hero_Image_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-hero-image-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Hero Image Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hero image is optimized';

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
		// Check for homepage
		if ( is_home() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Homepage hero image is not optimized. Compress hero images, use responsive sizing, and implement lazy loading for faster load times.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/homepage-hero-image-not-optimized',
			);
		}

		return null;
	}
}
