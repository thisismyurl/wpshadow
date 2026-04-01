<?php
/**
 * Comment Template Functionality Diagnostic
 *
 * Validates that comment templates are properly implemented with
 * moderation controls, spam protection, and accessibility features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Template Functionality Diagnostic Class
 *
 * Checks comment template implementation and settings.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Template_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-template-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Template Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment template implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Skip if comments are globally disabled.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( 'closed' === $default_comment_status ) {
			return null;
		}

		$issues       = array();
		$template_dir = get_template_directory();

		// Check for comments.php template.
		$comments_file = $template_dir . '/comments.php';
		if ( ! file_exists( $comments_file ) ) {
			$issues[] = __( 'Missing comments.php template', 'wpshadow' );
		} else {
			$content = file_get_contents( $comments_file );

			// Check for required functions.
			$required_functions = array(
				'comment_form'      => __( 'comment_form() missing (users cannot submit comments)', 'wpshadow' ),
				'wp_list_comments'  => __( 'wp_list_comments() missing (comments will not display)', 'wpshadow' ),
			);

			foreach ( $required_functions as $func => $message ) {
				if ( false === stripos( $content, $func ) ) {
					$issues[] = $message;
				}
			}

			// Check for pagination in comments.
			if ( false === stripos( $content, 'paginate_comments_links' ) && false === stripos( $content, 'previous_comments_link' ) ) {
				$issues[] = __( 'Comment pagination not implemented (issues with many comments)', 'wpshadow' );
			}
		}

		// Check comment settings.
		$comment_registration = get_option( 'comment_registration', 0 );
		$comment_moderation   = get_option( 'comment_moderation', 0 );

		if ( ! $comment_registration && ! $comment_moderation ) {
			$issues[] = __( 'Comments do not require registration or moderation (spam risk)', 'wpshadow' );
		}

		// Check for spam protection.
		$has_spam_protection = is_plugin_active( 'akismet/akismet.php' ) ||
							  is_plugin_active( 'antispam-bee/antispam_bee.php' ) ||
							  function_exists( 'jetpack_is_module_active' ) && jetpack_is_module_active( 'comments' );

		if ( ! $has_spam_protection ) {
			$issues[] = __( 'No spam protection plugin active (recommend Akismet)', 'wpshadow' );
		}

		// Check comment nesting depth.
		$thread_comments       = get_option( 'thread_comments', 0 );
		$thread_comments_depth = get_option( 'thread_comments_depth', 5 );

		if ( $thread_comments && $thread_comments_depth > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: comment nesting depth */
				__( 'Comment nesting depth set to %d (may cause display issues)', 'wpshadow' ),
				$thread_comments_depth
			);
		}

		// Check for unmoderated comments.
		global $wpdb;
		$pending_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '0'" );

		if ( $pending_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pending comments */
				__( '%d comments awaiting moderation (review backlog)', 'wpshadow' ),
				$pending_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of comment functionality issues */
					__( 'Found %d comment functionality issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'                => $issues,
					'pending_comments'      => $pending_count,
					'comment_moderation'    => (bool) $comment_moderation,
					'comment_registration'  => (bool) $comment_registration,
					'recommendation'        => __( 'Ensure comments.php includes required functions, enable moderation, and activate spam protection.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
