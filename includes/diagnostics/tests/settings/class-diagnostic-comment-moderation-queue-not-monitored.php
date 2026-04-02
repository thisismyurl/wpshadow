<?php
/**
 * Comment Moderation Queue Not Monitored Diagnostic
 *
 * Checks if comment moderation is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * Detects unmonitored comment moderation.
 *
 * @since 1.6093.1200
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
	protected static $description = 'Checks if comment moderation is monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for pending comments
		$pending = wp_count_comments();

		if ( ! empty( $pending->moderated ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment moderation queue is not monitored. Review pending comments regularly to maintain community quality and engagement.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-moderation-queue-not-monitored',
			);
		}

		return null;
	}
}
