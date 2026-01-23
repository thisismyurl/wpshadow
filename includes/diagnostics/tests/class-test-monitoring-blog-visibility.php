<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Monitoring_Blog_Visibility extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$public = get_option('blog_public');
		if ($public === '0') {
			return array(
				'id' => 'blog-not-public',
				'title' => 'Blog hidden from search engines',
				'threat_level' => 20
			);
		}
		return null;
	}

	public static function test_live_monitoring_blog_visibility(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'Blog public' : 'Blog not public'
		);
	}
}
