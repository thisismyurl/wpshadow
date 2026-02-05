<?php
/**
 * Comment Author Whitelist Treatment
 *
 * Verifies that previously approved commenters can post without moderation delays.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Author Whitelist Treatment Class
 *
 * Checks comment approval whitelist configuration.
 *
 * @since 1.6032.1755
 */
class Treatment_Comment_Author_Whitelist extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-whitelist';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Whitelist';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment approval whitelist configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check comment approval whitelist setting.
		$whitelist_enabled = get_option( 'comment_whitelist', 1 );

		if ( ! $whitelist_enabled ) {
			$issues[] = __( 'Comment author whitelist is disabled - all comments require moderation', 'wpshadow' );
		}

		// Check if comment_previously_approved option exists (older WP versions).
		$previously_approved = get_option( 'comment_previously_approved' );
		if ( $previously_approved !== false && ! $previously_approved ) {
			$issues[] = __( 'Previously approved commenters not whitelisted', 'wpshadow' );
		}

		// Check moderation requirements.
		$comment_moderation = get_option( 'comment_moderation', 0 );
		if ( $comment_moderation && $whitelist_enabled ) {
			// This is actually good - manual moderation with whitelist for approved authors.
			// No issue to report.
		} elseif ( $comment_moderation && ! $whitelist_enabled ) {
			$issues[] = __( 'All comments held for moderation with no whitelist for approved authors', 'wpshadow' );
		}

		// Check if there are any approved comments.
		global $wpdb;
		$approved_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT comment_author_email) 
			FROM {$wpdb->comments} 
			WHERE comment_approved = '1'"
		);

		if ( $approved_count > 100 && ! $whitelist_enabled ) {
			$issues[] = sprintf(
				/* translators: %d: number of approved authors */
				__( '%d authors have approved comments but whitelist is disabled', 'wpshadow' ),
				(int) $approved_count
			);
		}

		// Check comment registration requirement.
		$comment_registration = get_option( 'comment_registration', 0 );
		if ( $comment_registration ) {
			$issues[] = __( 'Only registered users can comment - whitelist is irrelevant', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-author-whitelist',
			);
		}

		return null;
	}
}
