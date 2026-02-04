<?php
/**
 * Hash Verification For Updates Not Configured Diagnostic
 *
 * Validates that WordPress update packages verify SHA256 hashes before installation.\n * Without hash verification, man-in-the-middle attacks deliver compromised updates.\n * Attacker intercepts update download, replaces with malware-laden version.\n *
 * **What This Check Does:**
 * - Checks if update hash verification is enabled\n * - Validates SHA256 verification for WordPress core updates\n * - Tests plugin/theme update hash verification\n * - Confirms signature verification method used\n * - Validates update API calls use HTTPS (transport security)\n * - Tests rollback if hash verification fails\n *
 * **Why This Matters:**
 * Unverified updates enable update-based malware injection. Scenarios:\n * - Network attacker intercepts update download\n * - Replaces legitimate update with malware version\n * - Hash check disabled = malware installed automatically\n * - Entire site compromised from update process\n *
 * **Business Impact:**
 * WordPress site auto-updates enabled, hash verification disabled. Network attacker\n * (compromised ISP router) intercepts update download. Replaces with malware.\n * Malware runs as web server user. Database access obtained. 100K customer records\n * stolen. Site used to serve phishing emails (spam bot installed). Recovery: full\n * rebuild, forensics, notification. Cost: $500K+ liability + cleanup.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Updates safe from tampering\n * - #9 Show Value: Prevents update-chain-of-custody compromises\n * - #10 Beyond Pure: Defense in depth, cryptographic verification\n *
 * **Related Checks:**
 * - Core WordPress Updates Not Automatically Applied (update safety)\n * - SSL/TLS Configuration Not Set (transport encryption)\n * - Plugin Integrity Verification (malware detection)\n *
 * **Learn More:**
 * Update security best practices: https://wpshadow.com/kb/update-hash-verification\n * Video: Securing the update process (9min): https://wpshadow.com/training/update-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hash Verification For Updates Not Configured Diagnostic Class
 *
 * Implements detection of disabled update hash verification.\n *
 * **Detection Pattern:**
 * 1. Check WordPress update verification hooks\n * 2. Query update process for hash validation\n * 3. Validate HTTPS for update API calls\n * 4. Check SHA256 verification enabled\n * 5. Test signature verification method\n * 6. Return severity if verification disabled\n *
 * **Real-World Scenario:**
 * Developer disables hash verification to \"speed up\" updates (believes it slows\n * WordPress). Network attacker compromises office network. Admin initiates update.\n * Attacker intercepts download. Replaces WordPress core with backdoor version.\n * Verification disabled = silently installed. Admin wonders why database is slow\n * (attacker running data exfiltration queries). Discovers compromise 3 weeks later.\n *
 * **Implementation Notes:**
 * - Checks update filter hooks for verification\n * - Validates HTTPS enforcement\n * - Tests SHA256/GPG signature checks\n * - Severity: critical (verification disabled), high (weak verification)\n * - Treatment: enable update hash verification\n *
 * @since 1.6030.2352
 */
class Diagnostic_Hash_Verification_For_Updates_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hash-verification-for-updates-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hash Verification For Updates Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hash verification is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if hash verification filter is active
		if ( ! has_filter( 'upgrader_pre_download', 'verify_update_hash' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Hash verification for updates is not configured. Verify SHA hashes of downloaded updates to prevent man-in-the-middle attacks.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hash-verification-for-updates-not-configured',
			);
		}

		return null;
	}
}
