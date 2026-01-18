<?php
/**
 * Tests for plugin bootstrap and initialization
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test plugin bootstrap and constants
 */
class PluginBootstrapTest extends TestCase {

	/**
	 * Test plugin file exists
	 */
	public function test_plugin_file_exists(): void {
		$plugin_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/wpshadow.php';
		$this->assertFileExists( $plugin_file );
	}

	/**
	 * Test free plugin file exists
	 */
	public function test_free_plugin_file_exists(): void {
		$plugin_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/wpshadow.php';
		$this->assertFileExists( $plugin_file );
	}

	/**
	 * Test pro plugin file exists
	 */
	public function test_pro_plugin_file_exists(): void {
		$plugin_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/pro/wpshadow-pro.php';
		$this->assertFileExists( $plugin_file );
	}

	/**
	 * Test composer.json exists and is valid JSON
	 */
	public function test_composer_json_is_valid(): void {
		$composer_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/composer.json';
		$this->assertFileExists( $composer_file );

		$content = file_get_contents( $composer_file );
		$this->assertIsString( $content );

		$decoded = json_decode( $content, true );
		$this->assertIsArray( $decoded );
		$this->assertArrayHasKey( 'name', $decoded );
		$this->assertStringContainsString( 'wpshadow', $decoded['name'] );
	}

	/**
	 * Test includes directory structure
	 */
	public function test_includes_directory_exists(): void {
		$includes_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/includes';
		$this->assertDirectoryExists( $includes_dir );
	}

	/**
	 * Test free plugin includes directory exists
	 */
	public function test_free_includes_directory_exists(): void {
		$includes_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/includes';
		$this->assertDirectoryExists( $includes_dir );
	}

	/**
	 * Test pro plugin includes directory exists
	 */
	public function test_pro_includes_directory_exists(): void {
		$includes_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/pro/includes';
		$this->assertDirectoryExists( $includes_dir );
	}

	/**
	 * Test key free plugin files exist
	 */
	public function test_key_free_plugin_files_exist(): void {
		$base_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/includes';

		$required_files = array(
			'class-wps-capabilities.php',
			'class-wps-module-registry.php',
			'class-wps-module-bootstrap.php',
		);

		foreach ( $required_files as $file ) {
			$this->assertFileExists( $base_dir . '/' . $file, "Missing required file: $file" );
		}
	}

	/**
	 * Test pro-only files exist in pro plugin only
	 */
	public function test_pro_only_files_in_pro_plugin(): void {
		$pro_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/pro/includes';

		$pro_files = array(
			'class-wps-license.php',
			'class-wps-plugin-upgrader.php',
		);

		foreach ( $pro_files as $file ) {
			$this->assertFileExists( $pro_dir . '/' . $file, "Missing pro file: $file" );
		}
	}

	/**
	 * Test pro-only files don't exist in free plugin
	 */
	public function test_pro_only_files_not_in_free_plugin(): void {
		$free_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/includes';

		$pro_only_files = array(
			'class-wps-license.php',
			'class-wps-plugin-upgrader.php',
		);

		foreach ( $pro_only_files as $file ) {
			$this->assertFileDoesNotExist( $free_dir . '/' . $file, "Pro-only file should not be in free: $file" );
		}
	}

	/**
	 * Test namespace conventions
	 */
	public function test_namespace_convention(): void {
		$free_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/wpshadow.php';
		$content    = file_get_contents( $free_file );

		$this->assertStringContainsString( 'namespace WPShadow\\CoreSupport', $content );
	}
}
