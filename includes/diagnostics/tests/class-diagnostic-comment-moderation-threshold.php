<?php
/**
 * Comment Moderation Threshold Diagnostic
 *
 * Verifies comment moderation rules are configured to prevent spam while
 * allowing legitimate discussion.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1755
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Threshold Diagnostic Class
 *
 * Checks comment moderation queue configuration.
 *
 * @since 1.26032.1755
 */
class Diagnostic_Comment_Moderation_Threshold extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-threshold';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Threshold';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment moderation threshold';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if manual moderation is required.
		$comment_moderation = get_option( 'comment_moderation', 0 );
		if ( $comment_moderation ) {
			$issues[] = __( 'All comments must be manually approved', 'wpshadow' );
		}

		// Check comment link threshold.
		$moderation_keys = get_option( 'comment_max_links', 2 );
		if ( $moderation_keys === 0 || $moderation_keys === '0' ) {
			$issues[] = __( 'No link threshold set - spam may get through', 'wpshadow' );
		} elseif ( $moderation_keys > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of links */
				__( 'Link threshold is very high (%d) - spam may get through', 'wpshadow' ),
				$moderation_keys
			);
		}

		// Check moderation keys (spam words).
		$moderation_words = get_option( 'moderation_keys', '' );
		if ( empty( $moderation_words ) ) {
			$issues[] = __( 'No moderation keywords configured', 'wpshadow' );
		} else {
			$word_count = count( array_filter( explode( "\n", $moderation_words ) ) );
			if ( $word_count < 10 ) {
				$issues[] = __( 'Very few moderation keywords configured - consider adding more', 'wpshadow' );
			}
		}

		// Check blacklist (now called disallowed comment keys in WP 5.5+).
		$blacklist_keys = get_option( 'disallowed_keys', get_option( 'blacklist_keys', '' ) );
		if ( empty( $blacklist_keys ) ) {
			$issues[] = __( 'No disallowed keywords configured for automatic spam blocking', 'wpshadow' );
		}

		// Check pending moderation queue size.
		$pending_count = wp_count_comments();
		if ( isset( $pending_count->moderated ) && $pending_count->moderated > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pending comments */
				__( 'Large moderation queue (%d comments pending) - review settings', 'wpshadow' ),
				$pending_count->moderated
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-moderation-threshold',
			);
		}

		return null;
	}
}
