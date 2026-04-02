<?php
/**
 * Comment Link Limit Set Diagnostic (Stub)
 *
 * TODO stub mapped to the security gauge.
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
 * Diagnostic_Comment_Link_Limit_Set Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Comment_Link_Limit_Set extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'comment-link-limit-set';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Comment Link Limit Set';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Comment Link Limit Set';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check comment_max_links option.
	 *
	 * TODO Fix Plan:
	 * - Set anti-spam threshold.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If comments are globally disabled, the link limit is irrelevant.
		if ( ! WP_Settings::are_comments_open_by_default() ) {
			return null;
		}

		$limit = WP_Settings::get_max_links_in_comment();

		// 0 means WordPress will NOT hold comments based on link count.
		// Any positive value means comments with that many links are held for review.
		if ( $limit > 0 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No limit is set for the number of links allowed in a comment before it is held for moderation. Comments with many links are a classic spam pattern. Set a low limit (1–2) in Settings > Discussion.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-link-limit',
			'details'      => array(
				'comment_max_links'  => $limit,
				'recommended_limit'  => 2,
			),
		);
	}
}
