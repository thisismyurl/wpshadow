<?php
/**
 * Comment Policy Intentional Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Comment_Policy_Intentional Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Comment_Policy_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'comment-policy-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Comment Policy Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Comment Policy Intentional';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check default_comment_status and post type support for comment intent.
	 *
	 * TODO Fix Plan:
	 * - Enable, disable, or tightly moderate comments based on business goals.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$comments_open = WP_Settings::are_comments_open_by_default();
		$moderated     = WP_Settings::is_comment_moderation_enabled();

		// Comments disabled globally — no concern.
		if ( ! $comments_open ) {
			return null;
		}

		// Comments open but moderation is on — acceptable.
		if ( $moderated ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'New posts have comments open by default and comment moderation is disabled. This allows spam and abusive comments to appear on your site immediately without review. Enable comment moderation or disable comments on post types that do not need them.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-policy',
			'details'      => array(
				'comments_open_by_default' => $comments_open,
				'moderation_enabled'       => $moderated,
				'max_links_before_hold'    => WP_Settings::get_max_links_in_comment(),
			),
		);
	}
}
