<?php
/**
 * Comment User Email Verification Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_User_Email_Verification extends Diagnostic_Base {
	protected static $slug = 'comment-user-email-verification';
	protected static $title = 'Comment User Email Verification';
	protected static $description = 'Verifies commenter email addresses when needed';
	protected static $family = 'security';

	public static function check() {
		$require_email = (int) get_option( 'require_name_email', 1 );
		
		if ( 0 === $require_email ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Email addresses not required for comments - allows anonymous spam', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-user-email-verification',
			);
		}

		// Check for obviously fake emails in recent comments.
		global $wpdb;
		$fake_emails = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} 
			WHERE comment_author_email LIKE '%noemail%' 
			OR comment_author_email LIKE '%fake%'
			OR comment_author_email LIKE '%test@test%'
			OR comment_author_email NOT LIKE '%@%.%'
			LIMIT 10"
		);

		if ( $fake_emails > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d comments with invalid/fake email addresses', 'wpshadow' ),
					$fake_emails
				),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-user-email-verification',
			);
		}

		return null;
	}
}
