<?php
/**
 * bbPress Forum Security Diagnostic
 *
 * bbPress forums have security vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.239.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Forum Security Diagnostic Class
 *
 * @since 1.239.0000
 */
class Diagnostic_BbpressForumSecurity extends Diagnostic_Base {

	protected static $slug = 'bbpress-forum-security';
	protected static $title = 'bbPress Forum Security';
	protected static $description = 'bbPress forums have security vulnerabilities';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Anonymous posting.
		$anonymous_posts = get_option( 'bbpress_allow_anonymous_posts', '0' );
		if ( '1' === $anonymous_posts ) {
			$issues[] = 'anonymous posting enabled';
		}

		// Check 2: Post moderation.
		$moderation = get_option( 'bbpress_moderate_posts', '1' );
		if ( '0' === $moderation ) {
			$issues[] = 'post moderation disabled';
		}

		// Check 3: Spam protection.
		$spam_protection = get_option( 'bbpress_enable_spam_check', '1' );
		if ( '0' === $spam_protection ) {
			$issues[] = 'spam protection disabled';
		}

		// Check 4: Login requirement.
		$require_login = get_option( 'bbpress_require_login_to_post', '0' );
		if ( '0' === $require_login ) {
			$issues[] = 'posting without login allowed';
		}

		// Check 5: SSL enforcement.
		if ( ! is_ssl() ) {
			$issues[] = 'forum without HTTPS';
		}

		// Check 6: Private forum.
		$private_mode = get_option( 'bbpress_private_forum', '0' );
		if ( '0' === $private_mode ) {
			$issues[] = 'forum is public';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 60 + ( count( $issues ) * 4 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'bbPress security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-forum-security',
			);
		}

		return null;
	}
}
