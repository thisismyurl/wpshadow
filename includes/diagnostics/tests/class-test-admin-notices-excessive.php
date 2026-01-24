<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Admin Notices
 *
 * Tests if wp-admin has too many admin notices, which can cause:
 * - User annoyance and "banner blindness"
 * - Important notices being missed
 * - Visual clutter
 * - Reduced effective screen space
 *
 * Pattern: Buffers admin_notices output to count notice elements
 * Context: Requires admin context
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin UX
 * @philosophy  #8 Inspire Confidence - Clean, uncluttered admin interface
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Notices Excessive
 *
 * Checks if admin has too many notices displayed (> 3)
 *
 * @verified Not yet tested
 */
class Test_Admin_Notices_Excessive extends Diagnostic_Base
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

		// Buffer admin notices output
		ob_start();
		do_action('admin_notices');
		do_action('all_admin_notices');
		$notices_html = ob_get_clean();

		// Count different types of notices
		$notice_count = 0;
		$error_count = 0;
		$warning_count = 0;
		$success_count = 0;
		$info_count = 0;

		// Count .notice elements (WordPress standard)
		$notice_count += substr_count($notices_html, 'class="notice');
		$notice_count += substr_count($notices_html, "class='notice");

		// Also check for legacy .updated and .error classes
		$notice_count += substr_count($notices_html, 'class="updated');
		$notice_count += substr_count($notices_html, 'class="error');

		// Count by severity
		$error_count = substr_count($notices_html, 'notice-error');
		$warning_count = substr_count($notices_html, 'notice-warning');
		$success_count = substr_count($notices_html, 'notice-success');
		$info_count = substr_count($notices_html, 'notice-info');

		// Try to identify notice sources (plugin names)
		$notice_sources = array();
		if (preg_match_all('/data-notice=["\']([^"\']+)["\']/', $notices_html, $matches)) {
			$notice_sources = array_unique($matches[1]);
		}

		// Threshold: More than 3 notices is excessive
		$threshold = 3;

		if ($notice_count <= $threshold) {
			return null; // Pass
		}

		// Calculate severity based on notice types
		$threat_level = 30;
		if ($error_count > 1) {
			$threat_level = 45; // More serious if multiple errors
		}

		return array(
			'id'           => 'admin-notices-excessive',
			'title'        => 'Too Many Admin Notices Displayed',
			'description'  => sprintf(
				'WordPress admin is displaying %d notices (%d errors, %d warnings, %d success). This creates visual clutter and "banner blindness." Recommended: Under %d notices. Review and dismiss unnecessary plugin notices.',
				$notice_count,
				$error_count,
				$warning_count,
				$success_count,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/manage-admin-notices',
			'training_link' => 'https://wpshadow.com/training/reduce-admin-notices',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module'       => 'admin-ux',
			'priority'     => 6,
			'meta'         => array(
				'total_notices'   => $notice_count,
				'error_notices'   => $error_count,
				'warning_notices' => $warning_count,
				'success_notices' => $success_count,
				'info_notices'    => $info_count,
				'threshold'       => $threshold,
				'notice_sources'  => array_slice($notice_sources, 0, 5), // Top 5 sources
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
			'name'        => 'Admin Notices Excessive',
			'category'    => 'admin-ux',
			'severity'    => 'medium',
			'description' => 'Detects excessive admin notices causing visual clutter',
		);
	}
}
