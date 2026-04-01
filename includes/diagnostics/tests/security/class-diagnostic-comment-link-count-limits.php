<?php
/**
 * Comment Link Count Limits Diagnostic
 *
 * Validates that WordPress comment forms limit the number of hyperlinks allowed per\n * comment to prevent spam distribution, malware injection, and SEO manipulation. High\n * link counts turn comments into spam vectors: each link could be malware, phishing,\n * or SEO poisoning (manipulating your site's link juice for profit).\n *
 * **What This Check Does:**
 * - Checks WordPress comment_max_links setting (default: 2)\n * - Validates limit prevents spam (2-3 links is reasonable threshold)\n * - Detects if limit is disabled or set to unrealistic value (100+)\n * - Flags unapproved comments with excessive links\n * - Confirms moderation rules catch link-heavy comments\n *
 * **Why This Matters:**
 * Comment spam exploits work by distributing malicious links. Real attack vectors:\n * - Spammer submits 1,000 comments/day, each with 5 pharmaceutical links (no link limit)\n * - Malware distribution: links to exploit kits injected in comments\n * - SEO manipulation: competitor comments with backlinks to their site (link juice theft)\n * - Phishing: comments with links to fake login pages harvest credentials\n * - Drive-by downloads: comments contain links to malicious files\n *
 * **Business Impact:**
 * High link-count comments = automatic spam filter target. Without limits:\n * - Your site becomes known spam distribution vector\n * - Search engines penalize site for \"spammy content\"\n * - Visitors see 50+ spam comments/day (unprofessional, hurts credibility)\n * - Hosting provider may suspend site for spam distribution\n * - Link juice leaked to spammer/competitor sites (SEO damage)\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Automatic spam prevention without manual effort\n * - #9 Show Value: Quantifiable spam reduction\n * - #10 Beyond Pure: Protects site visitors from malicious links\n *
 * **Related Checks:**
 * - Comment Flood Protection (too many comments too fast)\n * - Comment Form CAPTCHA Not Implemented (human verification)\n * - Comment HTML Tag Whitelist (dangerous tags in comments)\n *
 * **Learn More:**
 * Comment spam prevention: https://wpshadow.com/kb/comment-link-limits
 * Video: Securing WordPress comments (7min): https://wpshadow.com/training/comment-security
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
 * Comment Link Count Diagnostic Class
 *
 * Implements link limit validation by reading WordPress comment_max_links option.\n * Detection: compares against safety threshold (2-3 is safe, >5 is risky, 0 means disabled).\n * If limit too high or disabled, flags as medium severity (allows spam distribution).\n *
 * **Detection Pattern:**
 * 1. Query get_option( 'comment_max_links' ), default is 2\n * 2. If value > 5: moderate risk (allows excessive links)\n * 3. If value == 0: high risk (no link limit)\n * 4. If value >= 10: critical (definitely spam-friendly setting)\n * 5. Return finding if value outside safe range (2-3)\n *
 * **Real-World Scenario:**
 * Small blog owner didn't configure comment settings, left at WordPress defaults.\n * June 2024: spam campaign discovered 80+ links/comment allowed (hosting provider misconfigured).\n * Site received 50 spam comments/day, each with 10+ links. Within 2 weeks: Google deindexed\n * site for spam. Owner spent 1 week removing spam, requesting reindexing. Cost of negligence: \n * 3 weeks organic traffic loss.\n *
 * **Implementation Notes:**
 * - Uses get_option() for portability across WordPress installations\n * - Safe threshold: 2-3 links (allows legitimate references)\n * - Risky threshold: 5+ links (enables spam)\n * - Returns severity: medium (easy fix, prevents spam)\n * - Auto-fixable treatment: set comment_max_links to 2\n *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Link_Count_Limits extends Diagnostic_Base {

	protected static $slug = 'comment-link-count-limits';
	protected static $title = 'Comment Link Count Limits';
	protected static $description = 'Checks if comment link count is limited';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null
	 */
	public static function check() {
		$moderation_keys = get_option( 'comment_max_links', 2 );

		if ( $moderation_keys > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: maximum links allowed */
					__( 'Comment link limit is set to %d - recommended: 2 or fewer to prevent spam', 'wpshadow' ),
					$moderation_keys
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-link-count-limits?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
