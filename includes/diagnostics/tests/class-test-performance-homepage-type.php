<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Performance_Homepage_Type extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$front_page = get_option('show_on_front');
		if ($front_page === 'posts') {
			return array(
				'id' => 'homepage-posts-page',
				'title' => 'Homepage showing posts list',
				'threat_level' => 20
			);
		}
		return null;
	}

	public static function test_live_performance_homepage_type(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'Homepage properly configured' : 'Using posts page'
		);
	}
}
