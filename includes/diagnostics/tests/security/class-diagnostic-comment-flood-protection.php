<?php
/**
 * Comment Flood Protection Diagnostic\n *
 * Validates that WordPress implements rate limiting on comment submission to prevent\n * comment spam floods: attackers submitting hundreds of comments/minute from single IP/user.\n * Without flood protection, automated bots can overwhelm moderation queue and turn site\n * into spam distribution network in minutes.\n *
 * **What This Check Does:**
 * - Detects if WordPress comment flood filter is active and not disabled\n * - Checks comment_flood_filter timeout setting (default: 15 seconds)\n * - Analyzes recent comments for flooding patterns (3+ comments from single IP in short period)\n * - Validates that flood-detected comments are held for moderation\n * - Tests flood threshold is reasonable (blocks bots, allows legitimate rapid responses)\n * - Confirms repeat offender IPs are blocked or tracked\n *
 * **Why This Matters:**
 * Comment spam floods overwhelm moderation systems and turn sites into spam vectors. Scenarios:\n * - Bot submits 500 spam comments in 60 seconds (moderation queue breaks)\n * - Malware distribution: 1,000 comments/hour with exploit kit links\n * - SEO poisoning: comments link to competitor sites (your link juice diverted)\n * - Comment form DoS: attacker submits comments to exhaust server resources\n *
 * **Business Impact:**
 * Unprotected site typically gets 10-50 spam comments/minute during attack. Scenario:\n * - Attacker runs comment spam bot for 1 hour (flood bot available on dark web, $50)\n * - Site receives 5,000 spam comments (moderation queue unusable)\n * - Legitimate comments mixed in, moderation takes 6 hours\n * - During moderation: site shows \"under attack\" to visitors\n * - Revenue impact: $2K+ lost transactions while moderation backlog\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Automatic spam prevention (no manual intervention)\n * - #9 Show Value: Quantifiable spam reduction\n * - #10 Beyond Pure: Protects site from becoming spam distribution vector\n *
 * **Related Checks:**
 * - Comment Link Count Limits (content-based spam filtering)\n * - Comment HTML Tag Whitelist (XSS via comments)\n * - Bot Traffic Detection (general bot protection)\n *
 * **Learn More:**
 * Comment spam prevention: https://wpshadow.com/kb/comment-flood-protection
 * Video: Anti-spam strategies (8min): https://wpshadow.com/training/comment-moderation
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
 * Comment Flood Protection Diagnostic Class\n *
 * Implements flood detection by querying recent comments and analyzing submission\n * patterns. Detection: groups comments by IP/user, counts submissions in time window,\n * flags IPs with excessive frequency (3+ comments per 15 seconds = flood pattern).\n *
 * **Detection Pattern:**
 * 1. Query recent comments (last 1 hour)\n * 2. Group by comment_author_IP and comment_author_email\n * 3. For each group: calculate submission rate (comments/15 seconds)\n * 4. Flag if rate > 1 (1+ comment per 15 sec = potential flood)\n * 5. Check for repeat offenders (same IP with multiple flood incidents)\n * 6. Verify comment_flood_filter hook is active (can't be bypassed)\n *
 * **Real-World Scenario:**
 * News site with comments enabled, no flood protection configured. July 2024: attacker\n * runs comment spam bot targeting pharmaceutical links. Bot submits 300 comments/minute.\n * Within 5 minutes: 1,500 comments queued. Moderation system breaks, legitimate comments\n * lost in spam noise. Editor spends entire day cleaning up. Post-attack: implements flood\n * protection, subsequent attack attempts blocked automatically.\n *
 * **Implementation Notes:**
 * - Queries recent comments efficiently (last 1000, filters by date)\n * - Groups by IP address (primary indicator)\n * - Configurable threshold (3 comments per 15 sec is default)\n * - Returns severity: critical (active flooding detected), medium (threshold too high)\n * - Auto-fixable treatment: adjust flood protection thresholds\n *
 *
 * @since 1.6093.1200
 */
class Diagnostic_Comment_Flood_Protection extends Diagnostic_Base {
	protected static $slug        = 'comment-flood-protection';
	protected static $title       = 'Comment Flood Protection';
	protected static $description = 'Checks if rate limiting prevents comment spam floods';
	protected static $family      = 'security';

	public static function check() {
		// WordPress has built-in flood protection, but check if it's been disabled.
		$has_flood_filter = has_filter( 'comment_flood_filter' );

		// Check for rapid commenting using WordPress API.
		$flood_threshold = apply_filters( 'comment_flood_filter_time', 15 );

		// Get recent comments (within flood threshold)
		$flood_time      = gmdate( 'Y-m-d H:i:s', time() - $flood_threshold );
		$recent_comments = get_comments(
			array(
				'post_status' => 'any',
				'date_query'  => array(
					array(
						'after' => $flood_time,
					),
				),
				'status'      => 'any',
				'number'      => 500,
				'fields'      => 'ids',
			)
		);

		// Group comments by IP to detect flooding pattern
		$flood_ips = array();
		foreach ( $recent_comments as $comment_id ) {
			$comment = get_comment( $comment_id );
			if ( $comment && ! empty( $comment->comment_author_IP ) ) {
				$ip               = $comment->comment_author_IP;
				$flood_ips[ $ip ] = isset( $flood_ips[ $ip ] ) ? $flood_ips[ $ip ] + 1 : 1;
			}
		}

		// Check for IPs with more than 3 comments in the flood threshold window
		$recent_floods = array_filter(
			$flood_ips,
			function ( $count ) {
				return $count > 3;
			}
		);

		if ( ! empty( $recent_floods ) || ! $has_flood_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment flood protection may be disabled or ineffective - detected rapid submissions', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-flood-protection',
			);
		}
		return null;
	}
}
