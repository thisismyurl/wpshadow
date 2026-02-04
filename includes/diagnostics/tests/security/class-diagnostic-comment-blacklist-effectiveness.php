<?php
/**
 * Comment Blacklist Effectiveness Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_Blacklist_Effectiveness extends Diagnostic_Base {
	protected static $slug = 'comment-blacklist-effectiveness';
	protected static $title = 'Comment Blacklist Effectiveness';
	protected static $description = 'Measures effectiveness of comment blacklist rules';
	protected static $family = 'security';

	public static function check() {
		$blacklist = get_option( 'disallowed_keys', '' );
		$blacklist = get_option( 'blacklist_keys', $blacklist ); // Backwards compat.

		$blacklist_count = empty( $blacklist ) ? 0 : count( explode( "\n", trim( $blacklist ) ) );

		if ( $blacklist_count < 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Comment blacklist has only %d entries - recommended: 50+ common spam keywords', 'wpshadow' ),
					$blacklist_count
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-blacklist-effectiveness',
			);
		}

		// Check spam rate in recent comments.
		global $wpdb;
		$total_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			WHERE comment_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		$spam_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			WHERE comment_approved = 'spam'
			AND comment_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		if ( $total_comments > 0 ) {
			$spam_rate = ( $spam_comments / $total_comments ) * 100;

			if ( $spam_rate > 50 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						__( 'High spam rate detected: %.1f%% - blacklist may be ineffective', 'wpshadow' ),
						$spam_rate
					),
					'severity'     => 'high',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-blacklist-effectiveness',
				);
			}
		}

		return null;
	}
}
