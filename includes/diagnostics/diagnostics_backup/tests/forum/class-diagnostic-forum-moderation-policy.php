<?php
/**
 * Forum Moderation and Content Policy Enforcement Diagnostic
 *
 * Checks if forum sites have clear moderation policies, automated
 * moderation tools, and content filtering systems in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forum
 * @since      1.6031.1453
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum Moderation Policy Diagnostic Class
 *
 * Verifies forum sites have proper moderation policies and tools.
 *
 * @since 1.6031.1453
 */
class Diagnostic_Forum_Moderation_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'forum-moderation-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Forum Moderation and Content Policy Enforcement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies forum sites implement clear moderation policies and enforcement tools';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forum';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1453
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for forum plugins.
		$forum_plugins = array(
			'bbpress',
			'buddypress',
			'wpforo',
			'asgaros-forum',
		);

		$has_forum = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $forum_plugins as $forum_plugin ) {
				if ( stripos( $plugin, $forum_plugin ) !== false ) {
					$has_forum = true;
					break 2;
				}
			}
		}

		if ( ! $has_forum ) {
			return null; // No forum.
		}

		$issues = array();

		// Check for community guidelines/rules page.
		$pages = get_pages();
		$has_guidelines = false;

		foreach ( $pages as $page ) {
			if ( stripos( $page->post_title, 'guideline' ) !== false ||
				stripos( $page->post_title, 'rules' ) !== false ||
				stripos( $page->post_title, 'community standards' ) !== false ||
				stripos( $page->post_content, 'forum rules' ) !== false ) {
				$has_guidelines = true;
				break;
			}
		}

		if ( ! $has_guidelines ) {
			$issues[] = __( 'No community guidelines/rules page found', 'wpshadow' );
		}

		// Check for anti-spam plugins.
		$has_antispam = false;
		$antispam_plugins = array(
			'akismet',
			'antispam-bee',
			'spam-protection',
			'stop-spam',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $antispam_plugins as $spam_plugin ) {
				if ( stripos( $plugin, $spam_plugin ) !== false ) {
					$has_antispam = true;
					break 2;
				}
			}
		}

		if ( ! $has_antispam ) {
			$issues[] = __( 'No anti-spam plugin detected', 'wpshadow' );
		}

		// Check for content filtering/moderation plugins.
		$has_filtering = false;
		$filter_plugins = array(
			'word-filter',
			'profanity',
			'content-moderation',
			'bad-behavior',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $filter_plugins as $filt_plugin ) {
				if ( stripos( $plugin, $filt_plugin ) !== false ) {
					$has_filtering = true;
					break 2;
				}
			}
		}

		if ( ! $has_filtering ) {
			$issues[] = __( 'No content filtering/profanity plugin found', 'wpshadow' );
		}

		// Check if comment moderation is enabled.
		if ( ! get_option( 'comment_moderation' ) ) {
			$issues[] = __( 'Comment moderation not required (all posts go live immediately)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Forum moderation concerns: %s. Community sites should have clear guidelines, anti-spam tools, and content filtering to maintain a safe environment.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-moderation-policy',
		);
	}
}
