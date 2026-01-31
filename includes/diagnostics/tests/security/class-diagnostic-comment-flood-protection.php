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

		// Check for rapid commenting using WordPress API.
		$flood_threshold = apply_filters( 'comment_flood_filter_time', 15 );

		// Get recent comments (within flood threshold)
		$flood_time = gmdate( 'Y-m-d H:i:s', time() - $flood_threshold );
		$recent_comments = get_comments(
			array(
				'post_status' => 'any',
				'date_query'  => array(
					array(
						'after' => $flood_time,
					),
				),
				'status'  => 'any',
				'number'  => 500,
				'fields'  => 'ids',
			)
		);

		// Group comments by IP to detect flooding pattern
		$flood_ips = array();
		foreach ( $recent_comments as $comment_id ) {
			$comment = get_comment( $comment_id );
			if ( $comment && ! empty( $comment->comment_author_IP ) ) {
				$ip = $comment->comment_author_IP;
				$flood_ips[ $ip ] = isset( $flood_ips[ $ip ] ) ? $flood_ips[ $ip ] + 1 : 1;
			}
		}

		// Check for IPs with more than 3 comments in the flood threshold window
		$recent_floods = array_filter( $flood_ips, function( $count ) {
			return $count > 3;
		});

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
