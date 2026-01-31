<?php
/**
 * Comment Status Inconsistencies Diagnostic
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

class Diagnostic_Comment_Status_Inconsistencies extends Diagnostic_Base {
	protected static $slug = 'comment-status-inconsistencies';
	protected static $title = 'Comment Status Inconsistencies';
	protected static $description = 'Finds comments with invalid status values';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;

		$valid_statuses = array( '0', '1', 'spam', 'trash', 'post-trashed' );
		$placeholders   = implode( ',', array_fill( 0, count( $valid_statuses ), '%s' ) );

		$invalid_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_approved NOT IN ($placeholders)",
				...$valid_statuses
			)
		);

		if ( $invalid_comments > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d comments with invalid status values - may cause display issues', 'wpshadow' ),
					$invalid_comments
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-status-inconsistencies',
			);
		}

		return null;
	}
}
