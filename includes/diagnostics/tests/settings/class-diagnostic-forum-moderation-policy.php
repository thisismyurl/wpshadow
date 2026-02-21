<?php
/**
 * Forum Community Moderation Policy Diagnostic
 *
 * Verifies forums have clear community guidelines and moderation
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_ForumModerationPolicy Class
 *
 * Checks for community guidelines, moderation tools, anti-spam
 *
 * @since 1.6031.1445
 */
class Diagnostic_ForumModerationPolicy extends Diagnostic_Base {

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
protected static $title = 'Forum Community Moderation Policy';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies forums have clear community guidelines and moderation';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'forum';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for forum plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$forum_plugins = array( 'bbpress', 'buddypress', 'wpforo', 'asgaros-forum' );
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

		// Check for terms of service page.
		$tos_page = get_page_by_path( 'terms-of-service' );
		if ( ! $tos_page ) {
			$tos_page = get_page_by_path( 'terms' );
		}
		if ( ! $tos_page ) {
			$issues[] = __( 'No terms of service page found', 'wpshadow' );
		}

		// Check for community guidelines page.
		$guidelines_page = get_page_by_path( 'community-guidelines' );
		if ( ! $guidelines_page ) {
			$guidelines_page = get_page_by_path( 'rules' );
		}
		if ( ! $guidelines_page ) {
			$issues[] = __( 'No community guidelines/rules page found', 'wpshadow' );
		}

		// Check for moderation tools.
		$moderation_plugins = array( 'moderation', 'akismet', 'antispam', 'stop-spammer' );
		$has_moderation = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $moderation_plugins as $m_plugin ) {
				if ( stripos( $plugin, $m_plugin ) !== false ) {
					$has_moderation = true;
					break 2;
				}
			}
		}

		if ( ! $has_moderation ) {
			$issues[] = __( 'No moderation/antispam plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Forum moderation concerns: %s. Forums need clear policies and moderation tools.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-moderation-policy',
		);
	}
}
