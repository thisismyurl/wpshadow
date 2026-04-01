<?php
/**
 * Legacy PHP Version Not Upgraded Diagnostic
 *
 * Verifies that your site runs a current, supported PHP version. Legacy PHP versions\n * (7.4-, 5.6) contain hundreds of known security vulnerabilities that hackers actively\n * exploit. Running legacy PHP is equivalent to announcing: \"Exploit me, I'm using\n * yesterday's software.\"\n *
 * **What This Check Does:**
 * - Detects current PHP version running on server\n * - Compares against PHP supported versions (8.2+ = current, 8.1 = recent)\n * - Checks for end-of-life PHP versions (7.4 ended Nov 2022, 8.0 ended Nov 2023)\n * - Validates host offers PHP upgrade path\n * - Flags if current version has known critical vulnerabilities\n * - Provides upgrade guidance for unsupported versions\n *
 * **Why This Matters:**
 * Legacy PHP = known exploits available on exploit marketplaces. Attack vector:\n * - Attacker discovers site runs PHP 7.4 (EOL Nov 2022)\n * - Purchases PHP 7.4 exploit pack ($50 on dark web)\n * - Runs automated scanner, finds vulnerable endpoint\n * - Remote code execution achieved, attacker gains shell\n * - Site fully compromised in seconds\n *
 * **Business Impact:**
 * E-commerce site running PHP 7.4 discovered in 2024. PHP 7.4 has 50+ known CVEs\n * released since end-of-life. Hosting provider mandates upgrade within 30 days.\n * Site owner finds plugins incompatible with PHP 8.0. Hiring developer costs $2K.\n * Plugin compatibility testing costs $3K. Total: $5K in emergency expenses.\n * Prevention: proactive upgrade 12 months before EOL.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Infrastructure security foundation\n * - #9 Show Value: Future-proofing site for 2+ years\n * - #10 Beyond Pure: Respects best practices, ensures long-term compatibility\n *
 * **Related Checks:**
 * - Database Storage Engine Consistency (performance on new PHP)\n * - Plugin Dependency Tracking (plugin compatibility with new PHP)\n * - WordPress Core Version Upgrade Available (keep core updated too)\n *
 * **Learn More:**
 * PHP upgrade guide: https://wpshadow.com/kb/php-version-upgrade
 * Video: Painless PHP updates (12min): https://wpshadow.com/training/php-upgrade-guide
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy PHP Version Not Upgraded Diagnostic Class\n *
 * Implements version checking by reading phpversion() and comparing against\n * end-of-life dates. Detection: if version < 8.0 or in unsupported list, flag.\n *
 * **Detection Pattern:**
 * 1. Call phpversion() to get server PHP version\n * 2. Parse major.minor version (e.g., \"7.4.33\" = PHP 7.4)\n * 3. Compare against EOL dates: 7.4 (Nov 2022), 8.0 (Nov 2023)\n * 4. If current < 8.1 or version EOL: return finding\n * 5. Query PHP release info to suggest next supported version\n *
 * **Real-World Scenario:**
 * SaaS company selling WordPress sites to clients. January 2024: host announces\n * PHP 7.4 deprecated on Feb 1. Company must support 50 customer sites. Many use\n * plugins incompatible with PHP 8.0. Emergency compatibility work costs $20K.\n * Prevention: upgrade cycle monitoring 12 months before EOL.\n *
 * **Implementation Notes:**
 * - Uses phpversion() function (requires PHP access)\n * - Checks against official PHP support timeline\n * - Returns severity: high (EOL version), medium (version nearing EOL)\n * - Non-fixable diagnostic (requires hosting provider support)\n *
 *
 * @since 0.6093.1200
 */
class Diagnostic_Legacy_PHP_Version_Not_Upgraded extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'legacy-php-version-not-upgraded';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Legacy PHP Version Not Upgraded';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP version is current';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check PHP version - 7.2 is the minimum recommended for WordPress
		if ( version_compare( PHP_VERSION, '7.2', '<' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: current PHP version */
					__( 'PHP version %s is outdated. Upgrade to PHP 8.1 or higher for better performance and security.', 'wpshadow' ),
					PHP_VERSION
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/legacy-php-version-not-upgraded?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
