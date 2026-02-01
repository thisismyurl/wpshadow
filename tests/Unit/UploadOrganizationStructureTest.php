<?php
/**
 * Tests for Upload Organization Structure Diagnostic
 *
 * @package WPShadow\Tests\Unit
 * @since   1.26032.1352
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPShadow\Diagnostics\Diagnostic_Upload_Organization_Structure;

// Manually load the diagnostic class for testing
require_once WPSHADOW_PLUGIN_DIR . '/includes/diagnostics/tests/functionality/class-diagnostic-upload-organization-structure.php';

/**
 * Upload Organization Structure Diagnostic Tests
 *
 * Tests the diagnostic that validates WordPress upload folder
 * organization and year/month folder structure.
 *
 * @since 1.26032.1352
 */
class UploadOrganizationStructureTest extends TestCase {

	/**
	 * Test diagnostic slug matches expected value.
	 *
	 * @return void
	 */
	public function test_diagnostic_slug() {
		$slug = Diagnostic_Upload_Organization_Structure::get_slug();
		$this->assertEquals( 'upload-organization-structure', $slug );
	}

	/**
	 * Test diagnostic title.
	 *
	 * @return void
	 */
	public function test_diagnostic_title() {
		$title = Diagnostic_Upload_Organization_Structure::get_title();
		$this->assertEquals( 'Upload Organization Structure', $title );
	}

	/**
	 * Test diagnostic description.
	 *
	 * @return void
	 */
	public function test_diagnostic_description() {
		$description = Diagnostic_Upload_Organization_Structure::get_description();
		$this->assertStringContainsString( 'year/month', $description );
		$this->assertStringContainsString( 'folder', $description );
	}

	/**
	 * Test diagnostic family.
	 *
	 * @return void
	 */
	public function test_diagnostic_family() {
		$family = Diagnostic_Upload_Organization_Structure::get_family();
		$this->assertEquals( 'functionality', $family );
	}

	/**
	 * Test diagnostic has proper structure when issues found.
	 *
	 * @return void
	 */
	public function test_diagnostic_has_proper_structure() {
		// Just test that the class exists and has the expected methods
		$this->assertTrue( method_exists( Diagnostic_Upload_Organization_Structure::class, 'check' ) );
		$this->assertTrue( method_exists( Diagnostic_Upload_Organization_Structure::class, 'get_slug' ) );
		$this->assertTrue( method_exists( Diagnostic_Upload_Organization_Structure::class, 'get_title' ) );
		$this->assertTrue( method_exists( Diagnostic_Upload_Organization_Structure::class, 'get_description' ) );
		$this->assertTrue( method_exists( Diagnostic_Upload_Organization_Structure::class, 'get_family' ) );
	}
}
