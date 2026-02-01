<?php
/**
 * Comment Close Automation Diagnostic
 *
 * Verifies automatic comment closing is properly configured.
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
 * Comment Close Automation Diagnostic Class
 *
 * Checks automatic comment closing configuration to prevent spam on old posts.
 *
 * @since 1.26032.1755
 */
class Diagnostic_Comment_Close_Automation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-close-automation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Close Automation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies automatic comment closing on old posts';

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

		// Check if automatic comment closing is enabled.
		$close_comments_for_old_posts = get_option( 'close_comments_for_old_posts', 0 );
		$close_comments_days_old = (int) get_option( 'close_comments_days_old', 14 );

		if ( ! $close_comments_for_old_posts ) {
			$issues[] = __( 'Automatic comment closing disabled - old posts vulnerable to spam', 'wpshadow' );
		} else {
			// Check if the threshold is reasonable.
			if ( $close_comments_days_old < 14 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'Comments close after %d days - may be too restrictive for discussions', 'wpshadow' ),
					$close_comments_days_old
				);
			} elseif ( $close_comments_days_old > 90 ) {
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'Comments close after %d days - old posts remain spam targets longer', 'wpshadow' ),
					$close_comments_days_old
				);
			}
		}

		// Check if comments are open by default.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( $default_comment_status !== 'open' ) {
			return null; // Comments disabled, auto-closing is irrelevant.
		}

		// Check for spam on old posts.
		global $wpdb;
		$old_post_spam = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} c
				INNER JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID
				WHERE c.comment_approved = 'spam'
				AND p.post_date < DATE_SUB(NOW(), INTERVAL %d DAY)",
				90
			)
		);

		if ( $old_post_spam > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: spam comments */
				__( 'Found %d spam comments on posts older than 90 days', 'wpshadow' ),
				$old_post_spam
			);
		}

		// Check for anti-spam plugins.
		$antispam_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
			'wp-spamshield/wp-spamshield.php',
		);

		$has_antispam = false;
		foreach ( $antispam_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_antispam = true;
				break;
			}
		}

		if ( ! $has_antispam && ! $close_comments_for_old_posts ) {
			$issues[] = __( 'No anti-spam plugin and no auto-close - spam protection is minimal', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-close-automation',
			);
		}

		return null;
	}
}
