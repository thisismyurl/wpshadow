<?php
/**
 * Comment Auto-Approval Issues Diagnostic
 *
 * Checks if comment auto-approval settings are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Auto-Approval Issues Diagnostic Class
 *
 * Checks comment auto-approval configuration.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Comment_Auto_Approval_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-auto-approval-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Auto-Approval Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks comment auto-approval settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if comment moderation is enabled
		$moderate_comments = get_option( 'comment_moderation', 0 );

		if ( $moderate_comments ) {
			// Check moderation settings
			$moderation_hold = get_option( 'moderation_keys', '' );
			$comment_whitelist = get_option( 'comment_whitelist', 0 );

			// Get approved comment threshold
			$thread_comments = get_option( 'thread_comments', 0 );

			if ( empty( $moderation_hold ) && $comment_whitelist ) {
				$issues[] = __( 'Comment whitelist enabled but no moderation rules configured', 'wpshadow' );
			}
		}

		// Check if spam protection is properly configured
		$akismet_key = get_option( 'wordpress_api_key', '' );
		if ( class_exists( 'Akismet' ) && empty( $akismet_key ) ) {
			$issues[] = __( 'Akismet is active but not properly configured', 'wpshadow' );
		}

		// Check if first-time comment moderation is enabled
		$first_comment_moderation = get_option( 'comment_moderation', 0 );
		if ( ! $first_comment_moderation ) {
			$issues[] = __( 'First-time comment moderation is disabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d comment approval configuration issues', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-auto-approval-issues',
			);
		}

		return null;
	}
}
