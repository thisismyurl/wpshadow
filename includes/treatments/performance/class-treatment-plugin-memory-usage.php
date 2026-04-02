<?php
/**
 * Plugin Memory Usage Treatment
 *
 * Detects plugins consuming excessive server memory causing resource exhaustion.
 *
 * **What This Check Does:**
 * 1. Measures PHP memory usage by plugin
 * 2. Identifies plugins using 50MB+ memory
 * 3. Flags plugins exceeding memory limit
 * 4. Detects memory leaks (growing usage over time)
 * 5. Analyzes memory patterns by page type
 * 6. Projects server capacity impact\n *
 * **Why This Matters:**\n * Server memory is shared among all concurrent processes. If one plugin allocates 100MB per request,
 * only 2-3 concurrent users can use site before server runs out of memory and crashes. WordPress memory
 * limit is usually 256MB. A plugin using 128MB leaves only 128MB for WordPress core + other plugins.\n *
 * With 10 concurrent users, each requesting a page: server needs1.0GB memory. If server only has 512MB,\n * it crashes and becomes inaccessible.\n *
 * **Real-World Scenario:**\n * Large media site used plugin for on-the-fly image resizing. Plugin loaded entire 50MB image into memory,
 * resized it, then freed memory (hopefully). Every image resize = temporary 50MB spike. With high traffic,
 * multiple resizes simultaneously = 200MB+ memory used. During traffic spike, server hit memory limit,\n * crashed. Site went offline during peak traffic. After implementing cron-based pre-generation (resizes
 * at off-peak), memory usage stable. Site survived traffic spikes.\n *
 * **Business Impact:**\n * - Server crashes under traffic (white screen)\n * - 100% downtime during peak traffic\n * - Lost revenue during crash ($5,000-$50,000 per incident)\n * - Scaling requires larger/more expensive server ($100-$500 monthly increase)\n * - Hosting provider throttles/terminates account (abuse policies)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents crash-under-load scenarios\n * - #9 Show Value: Prevents expensive server upgrades\n * - #10 Talk-About-Worthy: "Site handles traffic spikes without crashing"\n *
 * **Related Checks:**\n * - Server Memory Availability (total capacity)\n * - Concurrent User Capacity (load simulation)\n * - Background Job Performance (memory-intensive tasks)\n * - System Health Monitoring (resource tracking)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-memory-optimization\n * - Video: https://wpshadow.com/training/php-memory-profiling (6 min)\n * - Advanced: https://wpshadow.com/training/memory-leak-detection (12 min)\n *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Memory_Usage Class
 *
 * Identifies plugins that may be consuming excessive memory.
 */
class Treatment_Plugin_Memory_Usage extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-memory-usage';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Memory Usage';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins that may consume excessive memory';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Memory_Usage' );
	}
}
