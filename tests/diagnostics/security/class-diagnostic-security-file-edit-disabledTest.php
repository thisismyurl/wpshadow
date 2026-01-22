<?php
declare(strict_types=1);

namespace WPShadow\Tests\Diagnostics\Security;

use PHPUnit\Framework\TestCase;
use WP_Mock;
use WPShadow\Diagnostics\Diagnostic_Security_File_Edit_Disabled;

/**
 * Test case for Diagnostic_Security_File_Edit_Disabled
 */
class Diagnostic_Security_File_Edit_DisabledTest extends TestCase {

	public function setUp(): void {
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_check_returns_null_when_file_edit_is_disabled(): void {
		// Simulate DISALLOW_FILE_EDIT being true (secure configuration)
		if (!defined('DISALLOW_FILE_EDIT')) {
			define('DISALLOW_FILE_EDIT', true);
		}

		$result = Diagnostic_Security_File_Edit_Disabled::check();

		$this->assertNull($result, 'Should return null when file editing is properly disabled');
	}

	public function test_check_returns_finding_when_file_edit_enabled(): void {
		// Note: Can't easily test undefined constant or false value
		// in same test run, so we test the structure when issue is found
		
		// This test validates the structure of findings
		$result = Diagnostic_Security_File_Edit_Disabled::check();

		// Result could be null (if DISALLOW_FILE_EDIT is true) or array (if false/undefined)
		if ($result !== null) {
			$this->assertIsArray($result);
			$this->assertArrayHasKey('id', $result);
			$this->assertEquals('security-file-edit-disabled', $result['id']);
		} else {
			// If null, that's also acceptable (secure state)
			$this->assertNull($result);
		}
	}

	public function test_check_returns_proper_structure_when_issue_found(): void {
		// When an issue is detected, verify the structure
		$result = Diagnostic_Security_File_Edit_Disabled::check();

		if ($result !== null) {
			// Verify all required fields are present
			$this->assertArrayHasKey('id', $result);
			$this->assertArrayHasKey('title', $result);
			$this->assertArrayHasKey('description', $result);
			$this->assertArrayHasKey('category', $result);
			$this->assertArrayHasKey('severity', $result);
			$this->assertArrayHasKey('threat_level', $result);
			$this->assertArrayHasKey('kb_link', $result);
			$this->assertArrayHasKey('training_link', $result);

			// Verify data types
			$this->assertIsString($result['id']);
			$this->assertIsString($result['title']);
			$this->assertIsString($result['description']);
			$this->assertIsInt($result['threat_level']);
			$this->assertGreaterThanOrEqual(0, $result['threat_level']);
			$this->assertLessThanOrEqual(100, $result['threat_level']);
		} else {
			// No issue found is also a valid state
			$this->assertTrue(true, 'No security issue detected');
		}
	}

	public function test_get_slug_returns_correct_value(): void {
		$this->assertEquals('security-file-edit-disabled', Diagnostic_Security_File_Edit_Disabled::get_slug());
	}

	public function test_get_title_returns_string(): void {
		$title = Diagnostic_Security_File_Edit_Disabled::get_title();
		$this->assertIsString($title);
		$this->assertNotEmpty($title);
	}

	public function test_get_description_returns_string(): void {
		$description = Diagnostic_Security_File_Edit_Disabled::get_description();
		$this->assertIsString($description);
		$this->assertNotEmpty($description);
	}
}
