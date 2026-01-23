<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_CodeQuality_Comment_Spam extends Diagnostic_Base {

	public static function check(): ?array {
		global $wpdb;

		$spam_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		if ( $spam_count > 100 ) {
			return array(
				'id'           => 'spam-comments-backlog',
				'title'        => sprintf( '%d Spam Comments', $spam_count ),
				'description'  => 'Large spam backlog. Consider emptying spam comments regularly.',
				'threat_level' => 30,
			);
		}
		return null;
	}

	public static function test_live_spam_comments(): array {
		global $wpdb;
		$result = self::check();
		$spam_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		if ( $spam_count > 100 ) {
			if ( is_null( $result ) ) {
				return array(
					'passed' => false,
					'message' => 'Spam backlog exists, check() should return issue.',
				);
			}
		} else {
			if ( ! is_null( $result ) ) {
				return array(
					'passed' => false,
					'message' => 'Spam count OK, check() should return null.',
				);
			}
		}

		return array(
			'passed' => true,
			'message' => 'Spam comments check passed.',
		);
	}
}
