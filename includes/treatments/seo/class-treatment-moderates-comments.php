<?php
/**
 * Treatment: Comments Moderated Regularly
 *
 * Tests if comments are being moderated and responded to in a timely manner.
 * Active comment moderation builds community and prevents spam.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comments Moderated Regularly Treatment Class
 *
 * Checks if site moderates comments regularly and maintains
 * clean, spam-free comment sections.
 *
 * Detection methods:
 * - Comment moderation settings
 * - Spam filtering tools
 * - Recent comment activity
 * - Comment response time
 *
 * @since 1.7034.1430
 */
class Treatment_Moderates_Comments extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'moderates-comments';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comments Moderated Regularly';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if comments are being moderated and responded to in a timely manner';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Comment moderation enabled
	 * - 1 point: Spam filtering active (Akismet, etc.)
	 * - 1 point: Low spam-to-legitimate ratio
	 * - 1 point: Admin responses to recent comments
	 * - 1 point: No pending comments older than 7 days
	 *
	 * @since  1.7034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check if comment moderation is enabled.
		$moderation_required = (int) get_option( 'comment_moderation', 0 );
		if ( 1 === $moderation_required ) {
			$score++;
			$details['moderation_enabled'] = true;
		}

		// Check for spam filtering plugins.
		$spam_plugins = array(
			'akismet/akismet.php'                        => 'Akismet',
			'antispam-bee/antispam_bee.php'              => 'Antispam Bee',
			'spam-destroyer/spam-destroyer.php'          => 'Spam Destroyer',
			'cleantalk-spam-protect/cleantalk.php'       => 'CleanTalk',
		);

		foreach ( $spam_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['spam_filter'] = $name;
				break;
			}
		}

		// Check spam-to-legitimate comment ratio.
		$total_comments = wp_count_comments();
		$spam_count     = (int) $total_comments->spam;
		$approved_count = (int) $total_comments->approved;
		
		if ( $approved_count > 0 ) {
			$spam_ratio = ( $spam_count / ( $spam_count + $approved_count ) ) * 100;
			
			// Good ratio is less than 50% spam.
			if ( $spam_ratio < 50 ) {
				$score++;
				$details['spam_ratio'] = sprintf(
					/* translators: %d: spam percentage */
					__( '%d%% spam (good filtering)', 'wpshadow' ),
					(int) $spam_ratio
				);
			}
		}

		// Check for admin responses to recent comments.
		$recent_comments = get_comments(
			array(
				'number'      => 20,
				'status'      => 'approve',
				'date_query'  => array(
					array(
						'after' => '30 days ago',
					),
				),
			)
		);

		$admin_responses = 0;
		foreach ( $recent_comments as $comment ) {
			$user = get_userdata( $comment->user_id );
			if ( $user && in_array( 'administrator', $user->roles, true ) ) {
				$admin_responses++;
			}
		}

		if ( $admin_responses > 0 && count( $recent_comments ) > 0 ) {
			$response_rate = ( $admin_responses / count( $recent_comments ) ) * 100;
			
			if ( $response_rate > 10 ) {
				$score++;
				$details['admin_response_rate'] = sprintf(
					/* translators: %d: response percentage */
					__( '%d%% admin response rate', 'wpshadow' ),
					(int) $response_rate
				);
			}
		}

		// Check for old pending comments.
		$old_pending = get_comments(
			array(
				'status'     => 'hold',
				'number'     => 1,
				'date_query' => array(
					array(
						'before' => '7 days ago',
					),
				),
			)
		);

		if ( empty( $old_pending ) ) {
			$score++;
			$details['no_old_pending'] = true;
		} else {
			$details['old_pending_count'] = count( $old_pending );
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'medium' : 'low';
		$threat_level = (int) ( 50 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Comment moderation score: %d%%. Regular moderation keeps discussions healthy and builds community trust.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-moderation',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since  1.7034.1430
	 * @return string Explanation of why this treatment matters.
	 */
	private static function get_why_matters() {
		return __(
			'Active comment moderation shows your audience that you\'re paying attention. Responding to comments builds relationships, encourages more engagement, and creates a welcoming community. Unmoderated comments can fill with spam, which hurts your SEO and user experience. Regular moderation keeps your site professional and trustworthy.',
			'wpshadow'
		);
	}
}
