<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Sidebar_Widgets extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-sidebar-widgets';
	protected static $title = 'Sidebar Widgets Test';
	protected static $description = 'Tests for sidebar widget configuration';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$html = self::fetch_html($url ?? home_url('/'));
		if ($html === false) {
			return null;
		}

		return self::analyze_html($html, $url ?? home_url('/'));
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		// Check for sidebar presence
		$has_sidebar = preg_match('/<(?:aside|div)[^>]+(?:class|id)=["\'][^"\']*(?:sidebar|widget-area)[^"\']*["\']/i', $html);

		if (!$has_sidebar) {
			return null; // No sidebar, test not applicable
		}

		// Check for default "Meta" widget (sign of unconfigured site)
		$has_meta_widget = preg_match('/<h2[^>]*>Meta<\/h2>|<div[^>]+class=["\'][^"\']*widget_meta[^"\']*["\']/i', $html);

		// Check for "Recent Comments" showing admin comments
		$has_admin_comments = preg_match('/Mr WordPress|Hello world!/i', $html);

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

		if ($has_admin_comments) {
			return [
				'id' => 'wordpress-default-content-visible',
				'title' => 'Default WordPress Content Visible',
				'description' => 'Default WordPress content ("Mr WordPress", "Hello world!") visible in sidebar widgets. Replace with real content.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/default-content/',
				'training_link' => 'https://wpshadow.com/training/content-setup/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['has_default_content' => true],
			];
		}

		return null;
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Sidebar Widgets', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for sidebar widget configuration.', 'wpshadow');
	}
}
