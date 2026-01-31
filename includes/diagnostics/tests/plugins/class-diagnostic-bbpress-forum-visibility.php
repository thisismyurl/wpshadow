<?php
/**
 * bbPress Forum Visibility Diagnostic
 *
 * bbPress forum visibility settings wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.511.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Forum Visibility Diagnostic Class
 *
 * @since 1.511.0000
 */
class Diagnostic_BbpressForumVisibility extends Diagnostic_Base {

	protected static $slug = 'bbpress-forum-visibility';
	protected static $title = 'bbPress Forum Visibility';
	protected static $description = 'bbPress forum visibility settings wrong';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Forum visibility.
		$forum_visible = get_option( 'bbpress_forums_visible', '1' );
		if ( '0' === $forum_visible ) {
			$issues[] = 'forums completely hidden';
		}
		
		// Check 2: Anonymous access.
		$anon_access = get_option( 'bbpress_anonymous_can_view', '1' );
		if ( '0' === $anon_access ) {
			$issues[] = 'non-logged-in users cannot view';
		}
		
		// Check 3: Topic visibility.
		$topic_visible = get_option( 'bbpress_topics_visible', '1' );
		if ( '0' === $topic_visible ) {
			$issues[] = 'topics hidden';
		}
		
		// Check 4: Reply visibility.
		$reply_visible = get_option( 'bbpress_replies_visible', '1' );
		if ( '0' === $reply_visible ) {
			$issues[] = 'replies hidden';
		}
		
		// Check 5: Role restrictions.
		$role_restrict = get_option( 'bbpress_restrict_by_role', '0' );
		if ( '1' === $role_restrict ) {
			$rules = get_option( 'bbpress_role_visibility', array() );
			if ( empty( $rules ) ) {
				$issues[] = 'role restrictions enabled but no rules';
			}
		}
		
		// Check 6: Search indexing.
		$search_indexed = get_option( 'bbpress_search_indexed', '1' );
		if ( '0' === $search_indexed ) {
			$issues[] = 'excluded from search';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 55 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'bbPress visibility issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-forum-visibility',
			);
		}
		
		return null;
	}
}
