<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Comment Moderation Enabled
 *
 * Category: Content & Community
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is comment moderation enabled?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Comment_Moderation_Enabled extends Diagnostic_Base {

	protected static $slug         = 'comment-moderation-enabled';
	protected static $title        = 'Comment Moderation Enabled';
	protected static $description  = 'Is comment moderation enabled?';
	protected static $category     = 'Content & Community';
	protected static $threat_level = 'low';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check if comments require moderation
		$moderate_comments      = get_option( 'comment_moderation' );
		$comments_need_approval = get_option( 'comment_whitelist' );

		if ( ! $moderate_comments && ! $comments_need_approval ) {
			// Check if comments are enabled at all
			$default_comments = get_option( 'default_comment_status' );
			if ( 'open' === $default_comments ) {
				return Diagnostic_Lean_Checks::build_finding(
					'comment-moderation-enabled',
					'Comments Not Moderated',
					'Comments are enabled but not moderated. Consider enabling moderation to prevent spam.',
					'Content & Community',
					'low',
					'low'
				);
			}
		}

		return null;
	}
}
