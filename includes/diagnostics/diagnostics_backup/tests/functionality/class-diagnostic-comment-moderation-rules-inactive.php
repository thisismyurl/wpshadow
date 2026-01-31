<?php
/**
 * Comment Moderation Rules Inactive Diagnostic
 *
 * Checks if comment moderation rules are properly configured and active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Rules Inactive Diagnostic Class
 *
 * Checks if moderation rules are configured.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Moderation_Rules_Inactive extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-rules-inactive';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Rules Inactive';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment moderation rules are active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if moderation is enabled
		$moderate_comments = get_option( 'comment_moderation', 0 );

		if ( ! $moderate_comments ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment moderation is disabled. First-time comments will not require approval.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-moderation-rules-inactive',
			);
		}

		// Check if moderation keys are configured
		$moderation_keys = get_option( 'moderation_keys', '' );

		if ( empty( $moderation_keys ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment moderation is enabled but no moderation rules/keywords are configured.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-moderation-rules-inactive',
			);
		}

		return null;
	}
}
