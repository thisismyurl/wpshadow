<?php
/**
 * Tests to ensure proper separation between free and pro plugins
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test free/pro plugin separation
 */
class FreeProSeparationTest extends TestCase {

	/**
	 * Pro-only class files that should NOT exist in free
	 *
	 * @var array
	 */
	private array $pro_only_files = array(
		'/includes/class-wps-license.php',
		'/includes/class-wps-license-widget.php',
		'/includes/class-wps-network-license.php',
		'/includes/api/class-wps-rest-license-controller.php',
		'/includes/class-wps-plugin-upgrader.php',
	);

	/**
	 * Test pro-only files don't exist in free
	 */
	public function test_pro_files_not_in_free(): void {
		$base_dir      = dirname( dirname( dirname( __FILE__ ) ) );
		$missing_files = array();

		foreach ( $this->pro_only_files as $rel_path ) {
			$free_file = $base_dir . '/free' . $rel_path;

			if ( file_exists( $free_file ) ) {
				$missing_files[] = $rel_path;
			}
		}

		$this->assertEmpty(
			$missing_files,
			"Pro-only files found in free plugin:\n" . implode( "\n", $missing_files )
		);
	}

	/**
	 * Test pro-only files exist in pro
	 */
	public function test_pro_files_exist_in_pro(): void {
		$base_dir      = dirname( dirname( dirname( __FILE__ ) ) );
		$missing_files = array();

		foreach ( $this->pro_only_files as $rel_path ) {
			$pro_file = $base_dir . '/pro' . $rel_path;

			if ( ! file_exists( $pro_file ) ) {
				$missing_files[] = $rel_path;
			}
		}

		$this->assertEmpty(
			$missing_files,
			"Pro-only files not found in pro plugin:\n" . implode( "\n", $missing_files )
		);
	}

	/**
	 * Test license references aren't in free plugin main file
	 */
	public function test_no_license_in_free_main(): void {
		$base_dir      = dirname( dirname( dirname( __FILE__ ) ) );
		$free_main     = $base_dir . '/free/wpshadow.php';
		$content       = file_get_contents( $free_main );

		$license_terms = array(
			'class-wps-license.php',
			'WPSHADOW_License::init',
			'WPSHADOW_License_Widget::init',
		);

		$violations = array();

		foreach ( $license_terms as $term ) {
			if ( strpos( $content, $term ) !== false ) {
				$violations[] = $term;
			}
		}

		$this->assertEmpty(
			$violations,
			"License code found in free plugin main file: " . implode( ', ', $violations )
		);
	}

	/**
	 * Test plugin upgrader references aren't in free plugin main file
	 */
	public function test_no_plugin_upgrader_in_free_main(): void {
		$base_dir  = dirname( dirname( dirname( __FILE__ ) ) );
		$free_main = $base_dir . '/free/wpshadow.php';
		$content   = file_get_contents( $free_main );

		$this->assertStringNotContainsString(
			'class-wps-plugin-upgrader.php',
			$content,
			'Plugin upgrader require found in free plugin main file'
		);
	}

	/**
	 * Test ghost features catalog location
	 */
	public function test_ghost_features_catalog_location(): void {
		$base_dir = dirname( dirname( dirname( __FILE__ ) ) );

		// Should exist in pro
		$pro_file = $base_dir . '/pro/ghost-features-catalog.php';
		$this->assertFileExists( $pro_file );

		// Should NOT exist in free
		$free_file = $base_dir . '/free/ghost-features-catalog.php';
		$this->assertFileDoesNotExist( $free_file );
	}

	/**
	 * Test vault helpers in pro
	 */
	public function test_vault_helpers_in_pro(): void {
		$base_dir = dirname( dirname( dirname( __FILE__ ) ) );

		$vault_file = $base_dir . '/pro/includes/wps-vault-helpers.php';
		$this->assertFileExists( $vault_file );
	}

	/**
	 * Test module downloader exists in both
	 */
	public function test_module_downloader_in_both(): void {
		$base_dir = dirname( dirname( dirname( __FILE__ ) ) );

		$free_file = $base_dir . '/free/includes/class-wps-module-downloader.php';
		$pro_file  = $base_dir . '/pro/includes/class-wps-module-downloader.php';

		$this->assertFileExists( $free_file, 'Module downloader missing from free' );
		$this->assertFileExists( $pro_file, 'Module downloader missing from pro' );
	}

	/**
	 * Test module registry exists in both
	 */
	public function test_module_registry_in_both(): void {
		$base_dir = dirname( dirname( dirname( __FILE__ ) ) );

		$free_file = $base_dir . '/free/includes/class-wps-module-registry.php';
		$pro_file  = $base_dir . '/pro/includes/class-wps-module-registry.php';

		$this->assertFileExists( $free_file, 'Module registry missing from free' );
		$this->assertFileExists( $pro_file, 'Module registry missing from pro' );
	}

	/**
	 * Test no license requires in free main file
	 */
	public function test_license_requires_not_in_free(): void {
		$base_dir      = dirname( dirname( dirname( __FILE__ ) ) );
		$free_main     = $base_dir . '/free/wpshadow.php';
		$content       = file_get_contents( $free_main );

		$license_requires = array(
			'require_once',
			'class-wps-license',
		);

		// Count license-related requires
		preg_match_all( '/require_once.*?license.*?\.php/i', $content, $matches );

		$this->assertEmpty(
			$matches[0] ?? array(),
			'License requires found in free plugin: ' . implode( '\n', $matches[0] ?? array() )
		);
	}
}
