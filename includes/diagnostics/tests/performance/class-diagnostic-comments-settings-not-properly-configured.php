<?php
/**
 * Comments Settings Not Properly Configured Diagnostic
 *
 * Tests for comment moderation settings.
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
 * Comments Settings Not Properly Configured Diagnostic Class
 *
 * Tests for comment moderation and configuration.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Comments_Settings_Not_Properly_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comments-settings-not-properly-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comments Settings Not Properly Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for comment moderation settings';

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

		// Check if comments are enabled.
		$default_comment_status = get_option( 'default_comment_status' );

		if ( $default_comment_status !== 'open' ) {
			$issues[] = __( 'Comments are not enabled by default on new posts', 'wpshadow' );
		}

		// Check if comments require approval.
		$comment_moderation = get_option( 'comment_moderation' );

		if ( empty( $comment_moderation ) ) {
			$issues[] = __( 'Comment moderation is not enabled - spam will be published immediately', 'wpshadow' );
		}

		// Check for spam filtering plugins.
		$akismet_active = is_plugin_active( 'akismet/akismet.php' );
		$jetpack_comments = is_plugin_active( 'jetpack/jetpack.php' );

		if ( ! $akismet_active && ! $jetpack_comments ) {
			$issues[] = __( 'No spam filtering plugin active - recommend Akismet or Jetpack', 'wpshadow' );
		}

		// Check blacklist/whitelist.
		$comment_blacklist = get_option( 'blacklist_keys' );

		if ( empty( $comment_blacklist ) ) {
			$issues[] = __( 'No comment blacklist configured - unable to filter spam keywords', 'wpshadow' );
		}

		// Check for hold on links in comments.
		$comment_max_links = get_option( 'comment_max_links' );

		if ( (int) $comment_max_links === 0 ) {
			$issues[] = __( 'No link limit on comments - potential spam vector', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comments-settings-not-properly-configured',
			);
		}

		return null;
	}
}
