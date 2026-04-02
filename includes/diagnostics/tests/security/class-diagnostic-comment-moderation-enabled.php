<?php
/**
 * Comment Moderation Enabled Diagnostic
 *
 * Checks whether WordPress comment moderation is enabled so submitted comments
 * are held for review before appearing publicly on the site.
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
 * Comment Moderation Enabled Diagnostic Class
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
	protected static $description = 'Checks whether WordPress comment moderation is enabled so that submitted comments are held for review before appearing publicly on the site.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the comment_moderation option and related thresholds to verify that
	 * new comments are held in the moderation queue before publishing.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when moderation is absent, null when healthy.
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
			'kb_link'      => 'https://wpshadow.com/kb/comment-moderation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'comments_open_by_default' => true,
				'moderation_enabled'       => false,
			),
		);
	}
}
