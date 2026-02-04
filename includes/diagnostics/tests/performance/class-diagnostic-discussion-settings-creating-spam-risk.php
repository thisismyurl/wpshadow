<?php
/**
 * Discussion Settings Creating Spam Risk Diagnostic
 *
 * Tests for discussion and notification settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discussion Settings Creating Spam Risk Diagnostic Class
 *
 * Tests for discussion and notification configuration.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Discussion_Settings_Creating_Spam_Risk extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'discussion-settings-creating-spam-risk';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Discussion Settings Creating Spam Risk';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for discussion and notification settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if pingbacks/trackbacks are enabled.
		$default_ping_status = get_option( 'default_ping_status' );

		if ( $default_ping_status === 'open' ) {
			$issues[] = __( 'Pingbacks/trackbacks are enabled - these are often spam vectors, consider disabling', 'wpshadow' );
		}

		// Check comment author email requirement.
		$require_name_email = get_option( 'require_name_email' );

		if ( empty( $require_name_email ) ) {
			$issues[] = __( 'Anonymous comments are allowed - consider requiring name/email', 'wpshadow' );
		}

		// Check comment registration requirement.
		$comment_registration = get_option( 'comment_registration' );

		if ( empty( $comment_registration ) ) {
			$issues[] = __( 'Comments do not require registration - opens door to spam', 'wpshadow' );
		}

		// Check admin notification on comments.
		$comments_notify = get_option( 'comments_notify' );

		if ( empty( $comments_notify ) ) {
			$issues[] = __( 'Admin is not notified of new comments - spam may go unnoticed', 'wpshadow' );
		}

		// Check for comment hold on multiple links.
		$comment_max_links = get_option( 'comment_max_links' );

		if ( (int) $comment_max_links > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: maximum links */
				__( 'Comments can have %d links - increase spam risk', 'wpshadow' ),
				$comment_max_links
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/discussion-settings-creating-spam-risk',
			);
		}

		return null;
	}
}
