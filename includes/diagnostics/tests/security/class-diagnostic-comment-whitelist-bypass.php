<?php
/**
 * Comment Whitelist Bypass Diagnostic
 *
 * Detects if whitelisted commenters are bypassing security measures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Whitelist Bypass Diagnostic Class
 *
 * Checks if comment whitelist settings may allow security bypasses.
 *
 * @since 1.26031.1300
 */
class Diagnostic_Comment_Whitelist_Bypass extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-whitelist-bypass';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Whitelist Bypass';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if whitelisted commenters bypassing security measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26031.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if comment whitelist/allowlist is enabled.
		$whitelist_enabled = (int) get_option( 'comment_whitelist', 1 );

		if ( 0 === $whitelist_enabled ) {
			// Whitelist is disabled - anyone can comment without moderation.
			$issues[] = array(
				'issue'       => 'whitelist_disabled',
				'description' => __( 'Comment allowlist is disabled - all comments bypass moderation', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check if moderation is enabled.
		$moderation_enabled = (int) get_option( 'comment_moderation', 0 );
		if ( 0 === $moderation_enabled && 0 === $whitelist_enabled ) {
			$issues[] = array(
				'issue'       => 'no_moderation',
				'description' => __( 'Both comment moderation and allowlist are disabled - no comment oversight', 'wpshadow' ),
				'severity'    => 'critical',
			);
		}

		// Check comment_previously_approved setting.
		$previously_approved = (int) get_option( 'comment_previously_approved', 1 );
		if ( 1 === $previously_approved ) {
			// This means users with previously approved comments bypass moderation.
			// Check if there's also a minimum number of approved comments required.
			$approved_threshold = apply_filters( 'wpshadow_comment_whitelist_threshold', 1 );
			
			$issues[] = array(
				'issue'       => 'auto_approve_previous_commenters',
				'description' => sprintf(
					/* translators: %d: number of approved comments required */
					__( 'Users with %d or more approved comments bypass moderation - could be exploited', 'wpshadow' ),
					$approved_threshold
				),
				'severity'    => 'medium',
			);
		}

		// Check if there are any users with excessive approved comments.
		global $wpdb;
		$suspicious_users = $wpdb->get_results(
			"SELECT comment_author_email, COUNT(*) as count 
			FROM {$wpdb->comments} 
			WHERE comment_approved = '1' 
			GROUP BY comment_author_email 
			HAVING count > 100 
			ORDER BY count DESC 
			LIMIT 5"
		);

		if ( ! empty( $suspicious_users ) ) {
			foreach ( $suspicious_users as $user ) {
				$issues[] = array(
					'issue'       => 'high_volume_commenter',
					'email'       => $user->comment_author_email,
					'count'       => $user->count,
					'description' => sprintf(
						/* translators: 1: email, 2: comment count */
						__( 'User %1$s has %2$d approved comments - verify legitimacy', 'wpshadow' ),
						$user->comment_author_email,
						$user->count
					),
					'severity'    => 'low',
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment whitelist configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/comment-whitelist-bypass',
		);
	}
}
