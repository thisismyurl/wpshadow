<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Meta_Widget extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-meta-widget';
	protected static $title = 'Default Meta Widget Test';
	protected static $description = 'Tests for default Meta widget in sidebar.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		if (!self::has_sidebar($body)) {
			return null;
		}

		$has_meta_widget = preg_match('/<h2[^>]*>Meta<\/h2>|<div[^>]+class=["\'][^"\']*widget_meta[^"\']*["\']/i', $body);

		if ($has_meta_widget) {
			return [
				'id' => 'wordpress-default-meta-widget',
				'title' => 'Default Meta Widget Active',
				'description' => 'Default "Meta" widget is active in sidebar. This exposes login link and admin functions. Remove or replace with useful content.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/sidebar-widgets/',
				'training_link' => 'https://wpshadow.com/training/widget-management/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['has_meta_widget' => true],
			];
		}

		return null;
	}

	protected static function has_sidebar(string $html): bool
	{
		return (bool) preg_match('/<(?:aside|div)[^>]+(?:class|id)=["\'][^"\']*(?:sidebar|widget-area)[^"\']*["\']/i', $html);
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Default Meta Widget', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks if the default Meta widget is still active.', 'wpshadow');
	}
}
