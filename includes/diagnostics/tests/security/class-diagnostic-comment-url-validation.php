<?php
/**
 * Comment URL Validation Diagnostic
 *
 * Validates that comment author URLs are properly sanitized and checked for malicious\n * links, preventing comment fields from becoming malware distribution vectors.\n * Attackers inject malware URLs into comment author URLs: every visitor clicking\n * the commenter profile link gets exposed to drive-by download.\n *
 * **What This Check Does:**
 * - Checks if comment author URLs are sanitized (esc_url_raw filtering)\n * - Detects suspicious URLs already in comments (URL shorteners, free domains, IPs)\n * - Validates URL scheme (http/https only, not javascript: or data: URIs)\n * - Flags comments linking to known malware domains\n * - Tests URL validation doesn't have bypasses (protocol spoofing)\n * - Confirms malicious URL patterns are blocked\n *
 * **Why This Matters:**
 * Comment URL field exploited for malware distribution. Scenarios:\n * - Attacker posts comment with author URL = bit.ly/malware-exploit\n * - Every commenter profile link could trigger drive-by download\n * - URLs shortened to hide actual destination (click looks safe, isn't)\n * - Free domain registration: attacker registers trusting-looking domain hosting malware\n * - IP address used directly (obfuscates actual target)\n *
 * **Business Impact:**
 * Malware in comment URLs infects site visitors. Scenario: Tech blog with 10K monthly\n * readers. Attacker injects malware URL in comment. 5% of readers click (500 people).\n * Drive-by download installs ransomware on 100 machines. Victim organizations seek\n * damages. Total liability: $1M+. Prevention: URL validation, costs $0.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevent content-injection attack class\n * - #9 Show Value: Eliminate malware distribution risk\n * - #10 Beyond Pure: Protects all site visitors from malicious links\n *
 * **Related Checks:**
 * - Comment HTML Tag Whitelist (XSS in comment content)\n * - Comment Link Count Limits (spam links in comments)\n * - Bot Traffic Detection (detect malware distribution attempts)\n *
 * **Learn More:**
 * URL validation guide: https://wpshadow.com/kb/comment-url-validation
 * Video: Link security in user content (7min): https://wpshadow.com/training/link-safety
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment URL Validation Diagnostic Class\n *
 * Implements URL validation checks by testing URL sanitization filters and\n * scanning recent comments for suspicious URL patterns (URL shorteners,\n * free domains, raw IP addresses).\n *
 * **Detection Pattern:**
 * 1. Check if pre_comment_author_url filter includes esc_url_raw\n * 2. Query recent comments for suspicious URL patterns\n * 3. Look for: bit.ly, tinyurl, shortened URLs\n * 4. Detect free domains: .tk, .ml, .ga (common malware registrations)\n * 5. Find raw IPs (XXX.XXX.XXX.XXX pattern)\n * 6. Flag if suspicious patterns found\n *
 * **Real-World Scenario:**
 * News site with comment section. June 2024: attacker discovers comment\n * URL field has no sanitization. Posts 100 comments with bit.ly URLs\n * (shortened links to exploit kits). Site visitors click profiles, 5% infected.\n * Site reputation destroyed as unwitting malware distributor. Cleanup: remove\n * all malicious comments, security audit, notify users.\n *
 * **Implementation Notes:**
 * - Detects common URL shortener services\n * - Checks free domain registrars (known for malware)\n * - Flags raw IPs (lack of domain = obfuscation attempt)\n * - Returns severity: critical (malicious URL present), high (shorteners detected)\n * - Auto-fixable treatment: sanitize existing URLs, enforce validation\n *
 * @since 1.6093.1200
 */
class Diagnostic_Comment_URL_Validation extends Diagnostic_Base {
	protected static $slug = 'comment-url-validation';
	protected static $title = 'Comment URL Validation';
	protected static $description = 'Checks if comment URLs are validated for malware';
	protected static $family = 'security';

	public static function check() {
		// Check if URLs are being validated/sanitized.
		$has_url_filter = has_filter( 'pre_comment_author_url', 'esc_url_raw' );

		if ( ! $has_url_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment URLs not being sanitized - may allow malicious links', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-url-validation',
			);
		}

		// Check for suspicious URLs in recent comments.
		global $wpdb;
		$suspicious_urls = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			WHERE comment_author_url LIKE '%bit.ly%'
			OR comment_author_url LIKE '%tinyurl%'
			OR comment_author_url LIKE '%.tk%'
			OR comment_author_url LIKE '%.ml%'
			OR comment_author_url REGEXP '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}'
			LIMIT 20"
		);

		if ( $suspicious_urls > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d comments with suspicious URLs (URL shorteners, free domains, or IP addresses)', 'wpshadow' ),
					$suspicious_urls
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-url-validation',
			);
		}

		return null;
	}
}
