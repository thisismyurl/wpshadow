<?php
/**
 * Discussion Defaults Reviewed Diagnostic (Stub)
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
 * Diagnostic_Discussion_Defaults_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Discussion_Defaults extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'discussion-defaults';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Discussion Defaults';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress discussion settings—comments, pings, and moderation—have been intentionally configured to prevent spam and unmoderated content.';

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
	 * - Check comment-related default options for untouched install defaults.
	 *
	 * TODO Fix Plan:
	 * - Review discussion defaults to reduce spam and improve moderation quality.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$comments_open    = WP_Settings::are_comments_open_by_default();
		$pings_open       = WP_Settings::are_pings_open_by_default();
		$moderation_on    = WP_Settings::is_comment_moderation_enabled();
		$max_links        = WP_Settings::get_max_links_in_comment();

		// If at least moderation is on OR comments are closed by default, the
		// site owner has made a conscious decision — pass.
		if ( $moderation_on || ! $comments_open ) {
			return null;
		}

		$issues = array();
		if ( $comments_open ) {
			$issues[] = __( 'New posts are open to comments by default', 'wpshadow' );
		}
		if ( $pings_open ) {
			$issues[] = __( 'Pingbacks are enabled by default', 'wpshadow' );
		}
		if ( ! $moderation_on ) {
			$issues[] = __( 'Comment moderation is disabled — new comments post immediately', 'wpshadow' );
		}
		if ( $max_links >= 2 ) {
			$issues[] = sprintf(
				/* translators: %d: max links allowed in comment */
				__( 'Comments can contain up to %d links before being held (a common spam tactic)', 'wpshadow' ),
				$max_links
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress discussion settings appear to be at their install defaults. New comments are published without moderation, pingbacks are enabled, and comment links are relatively unrestricted. Review Settings > Discussion and set a deliberate policy to reduce spam and comment abuse.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/discussion-defaults',
			'details'      => array(
				'comments_open_default' => $comments_open,
				'pings_open_default'    => $pings_open,
				'moderation_enabled'    => $moderation_on,
				'max_links'             => $max_links,
				'issues'                => $issues,
			),
		);
	}
}
