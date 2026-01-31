<?php
/**
 * Comment Export Issues Diagnostic
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

class Diagnostic_Comment_Export_Issues extends Diagnostic_Base {
	protected static $slug = 'comment-export-issues';
	protected static $title = 'Comment Export Issues';
	protected static $description = 'Checks if comments can be exported for backup';
	protected static $family = 'functionality';

	public static function check() {
		// Check if WordPress export functionality is available.
		if ( ! function_exists( 'export_wp' ) ) {
			require_once ABSPATH . 'wp-admin/includes/export.php';
		}

		// Check if there are a large number of comments that might cause export issues.
		global $wpdb;
		$comment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments}" );

		if ( $comment_count > 50000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Site has %s comments - WordPress export may time out. Consider batch export plugin.', 'wpshadow' ),
					number_format_i18n( $comment_count )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-export-issues',
			);
		}

		return null;
	}
}
