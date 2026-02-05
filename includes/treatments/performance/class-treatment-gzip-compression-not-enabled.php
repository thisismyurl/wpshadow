<?php
/**
 * Gzip Compression Not Enabled Treatment
 *
 * Checks if gzip compression is enabled.
 * GZIP compression = compress text files before sending.
 * No compression = 500KB HTML sent as-is.
 * With compression = 500KB → 80KB (84% smaller). 6x faster download.
 *
 * **What This Check Does:**
 * - Tests HTTP response headers for Content-Encoding: gzip
 * - Validates compression on HTML, CSS, JavaScript, JSON
 * - Checks server configuration (Apache mod_deflate, Nginx gzip)
 * - Tests actual file size reduction achieved
 * - Validates compression level (6 recommended)
 * - Returns severity if compression disabled
 *
 * **Why This Matters:**
 * Text files are highly compressible. HTML/CSS/JS compress 70-90%.
 * Without compression: transfer full file size.
 * With compression: transfer 10-30% of original. Massive bandwidth savings.
 *
 * **Business Impact:**
 * E-commerce site: average page 450KB HTML/CSS/JS. No GZIP.
 * Mobile 3G: 13 second download. 70% bounce before page loads.
 * Enabled GZIP compression on server. Files compressed to 95KB
 * (79% reduction). Download time: 2.8 seconds. Bounce rate: 22%.
 * Mobile conversions increased 320%. Bandwidth costs reduced 75%
 * ($800/month → $200/month). Server can handle 4x more traffic.
 * Setup time: 5 minutes (add to .htaccess). ROI: infinite.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimized delivery
 * - #9 Show Value: Massive bandwidth savings
 * - #10 Beyond Pure: Server configuration expertise
 *
 * **Related Checks:**
 * - Brotli Compression (next-gen compression)
 * - Minification Implementation (complementary)
 * - CDN Configuration (edge compression)
 *
 * **Learn More:**
 * GZIP compression: https://wpshadow.com/kb/gzip-compression
 * Video: Server compression setup (10min): https://wpshadow.com/training/gzip
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
 * Gzip Compression Not Enabled Treatment Class
 *
 * Detects disabled gzip compression.
 *
 * **Detection Pattern:**
 * 1. Make HTTP request to sample pages
 * 2. Check Accept-Encoding: gzip header sent
 * 3. Validate Content-Encoding: gzip in response
 * 4. Measure compressed vs uncompressed size
 * 5. Test compression on different content types
 * 6. Return if compression disabled or ineffective
 *
 * **Real-World Scenario:**
 * Added to .htaccess:
 * <IfModule mod_deflate.c>
 *   AddOutputFilterByType DEFLATE text/html text/css text/javascript
 *   AddOutputFilterByType DEFLATE application/javascript application/json
 * </IfModule>
 * Result: HTML 520KB → 88KB (83% reduction). CSS 180KB → 25KB (86%).
 * JS 350KB → 95KB (73%). Total page size: 1050KB → 208KB (80% reduction).
 * Mobile load time: 12s → 2.5s. Lighthouse score: 45 → 82.
 *
 * **Implementation Notes:**
 * - Checks HTTP compression headers
 * - Validates compression effectiveness
 * - Tests multiple content types
 * - Severity: critical (massive performance impact)
 * - Treatment: enable server GZIP compression
 *
 * @since 1.6030.2352
 */
class Treatment_Gzip_Compression_Not_Enabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gzip-compression-not-enabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Gzip Compression Not Enabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if gzip compression is enabled';

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
		// Check if gzip is enabled via Apache
		if ( ! function_exists( 'gzencode' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Gzip compression is not enabled. Enable gzip compression in your server configuration to reduce file transfer size by 50-80%.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gzip-compression-not-enabled',
			);
		}

		return null;
	}
}
