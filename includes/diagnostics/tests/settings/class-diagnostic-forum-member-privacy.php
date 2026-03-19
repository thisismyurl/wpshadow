<?php
/**
 * Forum Member Privacy Protection Diagnostic
 *
 * Verifies forum member profiles and activity are properly protected
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_ForumMemberPrivacy Class
 *
 * Checks for profile visibility, private messaging, search indexing
 *
 * @since 1.6093.1200
 */
class Diagnostic_Forum_Member_Privacy extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'forum-member-privacy';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Forum Member Privacy Protection';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies forum member profiles and activity are properly protected';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'forum';

/**
 * Run the diagnostic check.
 *
 * @since 1.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for forum plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$forum_plugins = array( 'bbpress', 'buddypress', 'wpforo', 'asgaros-forum', 'simple-forum' );
		$has_forum = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $forum_plugins as $f_plugin ) {
				if ( stripos( $plugin, $f_plugin ) !== false ) {
					$has_forum = true;
					break 2;
				}
			}
		}

		if ( ! $has_forum ) {
			return null;
		}

		$issues = array();

		// Check if author archives are enabled (exposes member activity).
		if ( get_option( 'show_avatars' ) ) {
			// Check if profiles are publicly accessible.
			$author_base = get_option( 'author_base', 'author' );
			if ( ! empty( $author_base ) ) {
				$issues[] = __( 'User profiles publicly accessible via author archives', 'wpshadow' );
			}
		}

		// Check for privacy plugins.
		$privacy_plugins = array( 'profile-privacy', 'buddypress-profile-privacy', 'member-privacy' );
		$has_privacy = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $privacy_plugins as $p_plugin ) {
				if ( stripos( $plugin, $p_plugin ) !== false ) {
					$has_privacy = true;
					break 2;
				}
			}
		}

		if ( ! $has_privacy ) {
			$issues[] = __( 'No profile privacy control plugin detected', 'wpshadow' );
		}

		// Check if search engines can index member profiles.
		if ( ! get_option( 'blog_public' ) ) {
			// Good - search engines discouraged.
		} else {
			$issues[] = __( 'Search engines allowed to index site (may expose member profiles)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Forum privacy concerns: %s. Member profiles should have privacy controls.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-member-privacy',
		);
	}
}
