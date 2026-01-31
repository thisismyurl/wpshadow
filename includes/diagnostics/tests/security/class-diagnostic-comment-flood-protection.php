<?php
/**
 * Comment Flood Protection Diagnostic
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

class Diagnostic_Comment_Flood_Protection extends Diagnostic_Base {
	protected static $slug = 'comment-flood-protection';
	protected static $title = 'Comment Flood Protection';
	protected static $description = 'Checks if rate limiting prevents comment spam floods';
	protected static $family = 'security';

	public static function check() {
		// WordPress has built-in flood protection, but check if it's been disabled.
		$has_flood_filter = has_filter( 'comment_flood_filter' );

		// Check for rapid commenting in database.
		global $wpdb;
		$flood_threshold = apply_filters( 'comment_flood_filter_time', 15 );

		// Look for users/IPs submitting comments too rapidly.
		$recent_floods = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT comment_author_IP, COUNT(*) as count
				FROM {$wpdb->comments}
				WHERE comment_date > DATE_SUB(NOW(), INTERVAL %d SECOND)
				GROUP BY comment_author_IP
				HAVING count > 3
				LIMIT 5",
				$flood_threshold
			)
		);

		if ( ! empty( $recent_floods ) || ! $has_flood_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment flood protection may be disabled or ineffective - detected rapid submissions', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-flood-protection',
			);
		}
		return null;
	}
}
