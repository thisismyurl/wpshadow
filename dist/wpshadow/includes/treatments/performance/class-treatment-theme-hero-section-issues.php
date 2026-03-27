<?php
/**
 * Theme Hero Section Issues Treatment
 *
 * Detects performance problems with hero sections and image sliders.
 *
 * **What This Check Does:**
 * 1. Identifies hero section implementations (video, slider, image)
 * 2. Detects unoptimized hero images (large file sizes)
 * 3. Flags auto-playing videos (bandwidth waste)
 * 4. Checks for animation/JavaScript complexity
 * 5. Measures rendering impact (CSS, JavaScript overhead)
 * 6. Analyzes mobile performance impact\n *
 * **Why This Matters:**\n * Hero sections are typically the first thing loaded (above fold). An unoptimized 5MB hero image = 5
 * seconds to download on slow connections. Auto-playing video = 20MB+ download immediately. Thousands\n * of visitors never see past hero because page loading is too slow.\n *
 * **Real-World Scenario:**\n * SaaS homepage had hero with auto-playing video (50MB). First paint: 8 seconds on 3G mobile. Visitors
 * gave up waiting before seeing any content. After replacing with optimized static image (200KB) with
 * lazy-loaded video (hidden until clicked): first paint 0.8s. Traffic from mobile increased 150%.
 * Cost: 2 hours optimization. Value: $30,000+ in recovered traffic and signups.\n *
 * **Business Impact:**\n * - First paint delayed 3-10+ seconds (hero loading)\n * - 50%+ bounce rate (visitors don't wait)\n * - Mobile visitors completely abandon site\n * - Conversion rate crashes 50-80%\n * - Revenue loss: $10,000-$100,000+ monthly\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Massive improvement in perceived speed\n * - #8 Inspire Confidence: First impression matters most\n * - #10 Talk-About-Worthy: "Site loads fast on mobile now"\n *
 * **Related Checks:**\n * - Theme Image Optimization (image-specific optimization)\n * - Mobile Performance (mobile hero impact)\n * - First Contentful Paint (content visibility timing)\n * - Video Auto-Play Configuration (unnecessary autoplay)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/hero-section-optimization\n * - Video: https://wpshadow.com/training/hero-section-techniques (6 min)\n * - Advanced: https://wpshadow.com/training/hero-video-optimization (10 min)\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Theme_Hero_Section_Issues extends Treatment_Base {
	protected static $slug = 'theme-hero-section-issues';
	protected static $title = 'Theme Hero Section Issues';
	protected static $description = 'Detects hero section/slider performance problems';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Hero_Section_Issues' );
	}
}
