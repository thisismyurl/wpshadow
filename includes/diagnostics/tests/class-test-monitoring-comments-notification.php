<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Monitoring_Comments_Notification extends Diagnostic_Base {

	public static function check(): ?array {
		$notify = get_option( 'moderation_notify' );
		if ( ! $notify ) {
			return array(
				'id'           => 'comment-notification-disabled',
				'title'        => 'Comment moderation notifications disabled',
				'threat_level' => 20,
			);
		}
		return null;
	}

	public static function test_live_monitoring_comments_notification(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Notifications enabled' : 'Notifications disabled',
		);
	}
}
