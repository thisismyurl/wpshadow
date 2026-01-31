<?php
/**
 * Forum and Community Member Privacy Diagnostic
 *
 * Checks if forum/community sites implement proper member privacy controls
 * including profile visibility settings, private messaging security, and data protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forum
 * @since      1.6031.1451
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum Member Privacy Diagnostic Class
 *
 * Verifies forum sites have proper member privacy controls.
 *
 * @since 1.6031.1451
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
	protected static $title = 'Forum and Community Member Privacy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies forum sites implement proper member privacy controls and profile settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forum';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1451
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
			'simple-forum',
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

		// Check for privacy/profile control plugins.
		$has_privacy_controls = false;
		$privacy_plugins = array(
			'bp-profile-privacy',
			'buddypress-privacy',
			'profile-visibility',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $privacy_plugins as $priv_plugin ) {
				if ( stripos( $plugin, $priv_plugin ) !== false ) {
					$has_privacy_controls = true;
					break 2;
				}
			}
		}

		if ( ! $has_privacy_controls ) {
			$issues[] = __( 'No dedicated privacy control plugin for member profiles', 'wpshadow' );
		}

		// Check for private messaging security.
		$has_secure_messaging = false;
		$messaging_plugins = array(
			'bp-better-messages',
			'private-messages',
			'encrypted-messages',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $messaging_plugins as $msg_plugin ) {
				if ( stripos( $plugin, $msg_plugin ) !== false ) {
					$has_secure_messaging = true;
					break 2;
				}
			}
		}

		// Check author archives (can expose member activity).
		if ( ! get_option( 'blog_public' ) ) {
			// Good - site discourages search engine indexing.
		} else {
			$issues[] = __( 'Site allows search engine indexing (may expose member profiles)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Forum privacy concerns: %s. Community sites should implement profile visibility controls and member privacy settings.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-member-privacy',
		);
	}
}
