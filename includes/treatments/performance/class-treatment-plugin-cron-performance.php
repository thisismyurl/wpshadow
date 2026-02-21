<?php
/**
 * Plugin Cron Performance Treatment
 *
 * Detects plugins with problematic scheduled tasks causing site slowdowns or reliability issues.
 *
 * **What This Check Does:**
 * 1. Lists all scheduled cron tasks by plugin
 * 2. Measures execution time of each cron
 * 3. Identifies crons running too frequently
 * 4. Detects duplicate or redundant crons
 * 5. Flags crons that fail/hang
 * 6. Analyzes cumulative impact on site traffic\n *
 * **Why This Matters:**\n * Plugins with poorly-designed crons run huge operations every hour (or every minute!). A plugin might
 * rescan entire blog every hour (1 hour = 3,600 requests wasted). If it's slow, the cron hangs and blocks
 * site. Next visitor's page load waits for slow cron to finish. Site appears frozen. Cron stacks: 10
 * crons waiting to run. Then site completely freezes.\n *
 * **Real-World Scenario:**\n * Popular backup plugin scheduled full backup every hour (100GB database). Each backup took 30 minutes.
 * Cron queue backed up (impossible to finish in time). Site became slower as backups consumed CPU/disk.\n * Eventually, cron system completely broke. Site was offline for 6 hours until admin manually cleared
 * backed-up cron queue. After changing backup to once-daily (off-peak), cron system normalized and site
 * performance recovered 70%.\n *
 * **Business Impact:**\n * - Site freezes during cron execution\n * - Page loads block waiting for cron\n * - Cron queue backs up (crons pile up)\n * - CPU/disk utilization 100% from crons\n * - Site reliability unstable and unpredictable\n * - Revenue loss from downtime ($5,000-$50,000 per incident)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Fixes site freezing issues immediately\n * - #8 Inspire Confidence: Restores reliable performance\n * - #10 Talk-About-Worthy: "Scheduled tasks don't freeze the site"\n *
 * **Related Checks:**\n * - Background Job Performance (async task analysis)\n * - Server CPU Utilization (cron CPU impact)\n * - Disk Space Availability (backup cron storage)\n * - System Health Monitoring (reliability tracking)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-cron-optimization\n * - Video: https://wpshadow.com/training/wordpress-cron-101 (6 min)\n * - Advanced: https://wpshadow.com/training/async-task-patterns (13 min)\n *
 * @since   1.4031.1939
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Cron_Performance Class
 *
 * Identifies scheduled tasks that impact site performance.
 */
class Treatment_Plugin_Cron_Performance extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-cron-performance';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Cron Performance';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugin cron jobs that may impact performance';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Cron_Performance' );
	}
}
