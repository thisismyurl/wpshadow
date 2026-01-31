<?php
/**
 * Comment Update Lock Timeouts Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_Update_Lock_Timeouts extends Diagnostic_Base {
	protected static $slug = 'comment-update-lock-timeouts';
	protected static $title = 'Comment Update Lock Timeouts';
	protected static $description = 'Detects comments stuck in update locks';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;

		// Check for locked comments (post locks from editing).
		$locked_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				WHERE meta_key = '_edit_lock'
				AND meta_value < %d
				AND post_id IN (SELECT comment_post_ID FROM {$wpdb->comments})",
				time() - 3600
			)
		);

		if ( $locked_comments > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d posts with stale edit locks that may affect comment management', 'wpshadow' ),
					$locked_comments
				),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-update-lock-timeouts',
			);
		}

		return null;
	}
}
