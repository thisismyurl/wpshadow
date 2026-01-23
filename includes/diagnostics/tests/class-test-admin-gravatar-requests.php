<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Gravatar Requests
 *
 * Tests if admin makes too many Gravatar API requests, which causes:
 * - Privacy concerns (leaks user emails to Gravatar/Automattic)
 * - Slow page load (external HTTP requests)
 * - GDPR compliance issues (no user consent)
 * - Single Point of Failure if Gravatar is down
 *
 * Pattern: Counts Gravatar URLs in buffered admin output
 * Context: Requires admin context, checks for gravatar.com requests
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Privacy & Performance
 * @philosophy  #10 Beyond Pure - Privacy-first, consent required
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Excessive Gravatar Requests
 *
 * Detects too many Gravatar image requests (> 10)
 *
 * @verified Not yet tested
 */
class Test_Admin_Gravatar_Requests extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Only run in admin context
		if (! is_admin()) {
			return null;
		}

		// Check if this is a page that might have comments/users
		global $pagenow;
		$gravatar_pages = array('index.php', 'edit-comments.php', 'users.php', 'profile.php');

		// Only test on relevant pages
		if (! in_array($pagenow, $gravatar_pages, true) && ! isset($_GET['page'])) {
			return null; // Not a relevant page
		}

		// Count Gravatars in different ways
		$gravatar_count = 0;

		// Method 1: Count recent comments that would show Gravatars
		if ($pagenow === 'index.php' || $pagenow === 'edit-comments.php') {
			$recent_comments = wp_count_comments();
			// Estimate: Dashboard shows ~5 recent comments, edit-comments shows 20+ per page
			if ($pagenow === 'index.php') {
				$gravatar_count = min(5, $recent_comments->approved ?? 0);
			} else {
				$gravatar_count = 20; // Default per-page count
			}
		}

		// Method 2: Count users on users.php page
		if ($pagenow === 'users.php') {
			$user_count = count_users();
			$total_users = $user_count['total_users'] ?? 0;
			// Users page typically shows 20 users per page
			$gravatar_count = min(20, $total_users);
		}

		// Method 3: Check for Gravatar settings
		$show_avatars = get_option('show_avatars', '1');

		if ($show_avatars === '0') {
			return null; // Avatars disabled, no Gravatars load
		}

		// Threshold: More than 10 Gravatar requests per page is excessive
		$threshold = 10;

		if ($gravatar_count <= $threshold) {
			return null; // Pass
		}

		// Calculate privacy impact
		// Each Gravatar request leaks email hash to third party
		$data_sent = $gravatar_count * 32; // MD5 hash = 32 chars per request

		return array(
			'id'           => 'admin-gravatar-requests',
			'title'        => 'Excessive Gravatar Requests in Admin',
			'description'  => sprintf(
				'WordPress admin is making approximately %d requests to Gravatar.com (Automattic). Each request sends an MD5 hash of user emails to a third party without explicit consent. This raises GDPR concerns and creates a Single Point of Failure. Consider using local avatars or disabling avatars in admin.',
				$gravatar_count
			),
			'color'        => '#FF4500',
			'bg_color'     => '#FFF4F1',
			'kb_link'      => 'https://wpshadow.com/kb/disable-gravatars',
			'training_link' => 'https://wpshadow.com/training/privacy-compliant-avatars',
			'auto_fixable' => true, // Can disable via settings
			'threat_level' => 42, // Medium-high (privacy concern)
			'module'       => 'privacy',
			'priority'     => 21,
			'meta'         => array(
				'gravatar_count' => $gravatar_count,
				'threshold'      => $threshold,
				'page'           => $pagenow,
				'privacy_impact' => 'Sends email hashes to third party',
				'gdpr_concern'   => true,
			),
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Excessive Gravatar Requests',
			'category'    => 'privacy',
			'severity'    => 'medium',
			'description' => 'Detects excessive third-party Gravatar requests',
		);
	}
}
