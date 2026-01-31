<?php
/**
 * bbPress Moderator Permissions Diagnostic
 *
 * bbPress moderator roles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.509.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Moderator Permissions Diagnostic Class
 *
 * @since 1.509.0000
 */
class Diagnostic_BbpressModeratorPermissions extends Diagnostic_Base {

	protected static $slug = 'bbpress-moderator-permissions';
	protected static $title = 'bbPress Moderator Permissions';
	protected static $description = 'bbPress moderator roles misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Moderator role exists
		$moderator_role = get_role( 'bbp_moderator' );
		if ( ! $moderator_role ) {
			$issues[] = 'bbPress moderator role not found';
		}

		// Check 2: Moderator delete capability
		$mod_delete = get_option( 'bbp_moderator_delete_posts', 0 );
		if ( ! $mod_delete ) {
			$issues[] = 'Moderator delete posts capability not enabled';
		}

		// Check 3: Moderator edit capability
		$mod_edit = get_option( 'bbp_moderator_edit_posts', 0 );
		if ( ! $mod_edit ) {
			$issues[] = 'Moderator edit posts capability not enabled';
		}

		// Check 4: Moderator manage topics
		$mod_topics = get_option( 'bbp_moderator_manage_topics', 0 );
		if ( ! $mod_topics ) {
			$issues[] = 'Moderator manage topics capability not enabled';
		}

		// Check 5: Moderator manage forums
		$mod_forums = get_option( 'bbp_moderator_manage_forums', 0 );
		if ( ! $mod_forums ) {
			$issues[] = 'Moderator manage forums capability not enabled';
		}

		// Check 6: Moderation logs enabled
		$mod_logs = get_option( 'bbp_moderation_logs_enabled', 0 );
		if ( ! $mod_logs ) {
			$issues[] = 'Moderation logging not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d bbPress moderator permission issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-moderator-permissions',
			);
		}

		return null;
	}
}
