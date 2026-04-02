<?php
/**
 * Comment Moderation Enabled Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 28.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Enabled Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Moderation_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Comment Moderation Enabled. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check comment_moderation and related moderation options.
	 *
	 * TODO Fix Plan:
	 * Fix by enabling moderation queue and thresholds.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// If comments are globally disabled, there is nothing to moderate.
		if ( ! WP_Settings::are_comments_open_by_default() ) {
			return null;
		}

		if ( WP_Settings::is_comment_moderation_enabled() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'New comments are not held for moderation before being published. Spam comments, malicious links, and abusive content can appear on your site immediately. Enable comment moderation in Settings > Discussion.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-moderation',
			'details'      => array(
				'comments_open_by_default' => true,
				'moderation_enabled'       => false,
			),
		);
	}
}
