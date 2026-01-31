<?php
/**
 * Comment Moderation Queue Not Monitored Diagnostic
 *
 * Checks if pending comments are being monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Queue Not Monitored Diagnostic Class
 *
 * Detects unmonitored pending comments.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Comment_Moderation_Queue_Not_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-queue-not-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Queue Not Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pending comments are monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$pending_comments = wp_count_comments();
		$pending_count    = isset( $pending_comments->moderated ) ? $pending_comments->moderated : 0;

		if ( $pending_count > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'You have %d pending comments awaiting moderation. Review and moderate comments to maintain site quality.', 'wpshadow' ),
					$pending_count
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-moderation-queue-not-monitored',
			);
		}

		return null;
	}
}
