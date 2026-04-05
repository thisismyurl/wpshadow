<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginBootstrapTest extends TestCase {
	public function test_plugin_is_active_in_smoke_site(): void {
		$this->assertTrue( is_plugin_active( 'wpshadow/wpshadow.php' ) );
	}

	public function test_core_classes_are_loaded(): void {
		$this->assertTrue( class_exists( \WPShadow\Core\Bootstrap_Autoloader::class ) );
		$this->assertTrue( class_exists( \WPShadow\Core\Plugin_Bootstrap::class ) );
		$this->assertTrue( class_exists( \WPShadow\Core\Hooks_Initializer::class ) );
	}

	public function test_primary_admin_callbacks_exist(): void {
		$this->assertTrue( function_exists( 'wpshadow_render_dashboard_v2' ) );
		$this->assertTrue( function_exists( 'wpshadow_render_guardian_page' ) );
		$this->assertTrue( function_exists( 'wpshadow_render_settings' ) );
	}

	public function test_key_admin_hooks_are_registered(): void {
		do_action( 'admin_menu' );

		global $menu;

		$top_level_slugs = array();
		foreach ( (array) $menu as $item ) {
			if ( is_array( $item ) && isset( $item[2] ) ) {
				$top_level_slugs[] = (string) $item[2];
			}
		}

		$this->assertContains( 'wpshadow', $top_level_slugs );
		$this->assertNotFalse( has_action( 'wp_ajax_wpshadow_get_dashboard_data' ) );
		$this->assertNotFalse( has_action( 'wp_ajax_wpshadow_post_scan_treatments' ) );
	}
}