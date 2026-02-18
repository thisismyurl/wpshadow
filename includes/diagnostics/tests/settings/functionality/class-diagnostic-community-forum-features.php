<?php
/**
 * Community & Forum Features Diagnostic
 *
 * Checks if site has community forum features to build customer engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1025
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Community & Forum Features Diagnostic Class
 *
 * Active communities answer 60-80% of support questions for free and build
 * loyalty and organic content for SEO.
 *
 * @since 1.6035.1025
 */
class Diagnostic_Community_Forum_Features extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'community-forum-features';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community & Forum Features';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if community forum or Q&A features are available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1025
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues          = array();
		$community_score = 0;
		$max_score       = 5;

		// Check for forum plugin.
		$has_forum = self::check_forum_plugin();
		if ( $has_forum ) {
			$community_score++;
		} else {
			$issues[] = 'forum plugin';
		}

		// Check for Q&A sections.
		$has_qa = self::check_qa_sections();
		if ( $has_qa ) {
			$community_score++;
		} else {
			$issues[] = 'Q&A sections';
		}

		// Check for discussion threads.
		$has_discussions = self::check_discussion_threads();
		if ( $has_discussions ) {
			$community_score++;
		} else {
			$issues[] = 'discussion threads';
		}

		// Check for moderation tools.
		$has_moderation = self::check_moderation_tools();
		if ( $has_moderation ) {
			$community_score++;
		} else {
			$issues[] = 'moderation tools';
		}

		// Check for active community.
		$has_activity = self::check_community_activity();
		if ( $has_activity ) {
			$community_score++;
		} else {
			$issues[] = 'active community engagement';
		}

		$completion_percentage = ( $community_score / $max_score ) * 100;

		if ( $completion_percentage >= 40 ) {
			return null; // Community features present.
		}

		$severity     = $completion_percentage < 20 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 20 ? 50 : 30;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Community features at %1$d%%. Missing: %2$s. Active communities answer 60-80%% of support questions and build loyalty.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/community-forum-features',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if forum plugin is installed.
	 *
	 * @since  1.6035.1025
	 * @return bool True if forum exists.
	 */
	private static function check_forum_plugin(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$forum_plugins = array(
			'bbpress/bbpress.php',
			'buddypress/bp-loader.php',
			'wpforo/wpforo.php',
			'asgaros-forum/asgaros-forum.php',
		);

		foreach ( $forum_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if Q&A sections exist.
	 *
	 * @since  1.6035.1025
	 * @return bool True if Q&A exists.
	 */
	private static function check_qa_sections(): bool {
		// Check for Q&A plugin.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$qa_plugins = array(
			'dw-question-answer/dw-question-answer.php',
			'anspress-question-answer/anspress-question-answer.php',
		);

		foreach ( $qa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for Q&A pages.
		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			's'              => 'questions answers Q&A',
			'post_status'    => 'publish',
		);

		$qa_pages = get_posts( $args );
		return ! empty( $qa_pages );
	}

	/**
	 * Check if discussion threads exist.
	 *
	 * @since  1.6035.1025
	 * @return bool True if discussions exist.
	 */
	private static function check_discussion_threads(): bool {
		// Check for bbPress topics.
		if ( post_type_exists( 'topic' ) ) {
			$topics = get_posts(
				array(
					'post_type'      => 'topic',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( ! empty( $topics ) ) {
				return true;
			}
		}

		// Check for forum posts.
		if ( post_type_exists( 'forum' ) ) {
			$forums = get_posts(
				array(
					'post_type'      => 'forum',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( ! empty( $forums ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if moderation tools are available.
	 *
	 * @since  1.6035.1025
	 * @return bool True if moderation exists.
	 */
	private static function check_moderation_tools(): bool {
		// Check for comment moderation settings.
		$comment_moderation = get_option( 'comment_moderation', 0 );
		$moderation_notify  = get_option( 'moderation_notify', 1 );

		if ( $comment_moderation || $moderation_notify ) {
			return true;
		}

		// Check for anti-spam plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$moderation_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam_bee.php',
		);

		foreach ( $moderation_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if community is active.
	 *
	 * @since  1.6035.1025
	 * @return bool True if activity exists.
	 */
	private static function check_community_activity(): bool {
		// Check for recent comments.
		$recent_comments = get_comments(
			array(
				'number'   => 1,
				'status'   => 'approve',
				'date_query' => array(
					array(
						'after' => '7 days ago',
					),
				),
			)
		);

		if ( ! empty( $recent_comments ) ) {
			return true;
		}

		// Check for recent forum topics (bbPress).
		if ( post_type_exists( 'topic' ) ) {
			$recent_topics = get_posts(
				array(
					'post_type'      => 'topic',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
					'date_query'     => array(
						array(
							'after' => '7 days ago',
						),
					),
				)
			);
			if ( ! empty( $recent_topics ) ) {
				return true;
			}
		}

		return false;
	}
}
