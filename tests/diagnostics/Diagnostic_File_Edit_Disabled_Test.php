<?php
declare(strict_types=1);

namespace WPShadow\Tests\Diagnostics;

use PHPUnit\Framework\TestCase;
use WP_Mock;
use WPShadow\Diagnostics\Diagnostic_File_Edit_Disabled;

/**
 * Test case for File Edit Disabled diagnostic
 *
 * @package WPShadow
 * @subpackage Tests
 */
class Diagnostic_File_Edit_Disabled_Test extends TestCase {

	/**
	 * Setup before each test
	 */
	public function setUp(): void {
		WP_Mock::setUp();
	}

	/**
	 * Teardown after each test
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	/**
	 * Test that check() returns null when DISALLOW_FILE_EDIT is true
	 */
	public function test_check_returns_null_when_file_edit_disabled() {
		// Define the constant as true (file editing disabled)
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		$result = Diagnostic_File_Edit_Disabled::check();

		$this->assertNull( $result, 'Expected null when DISALLOW_FILE_EDIT is true' );
	}

	/**
	 * Test that check() returns finding when DISALLOW_FILE_EDIT is not defined
	 *
	 * Note: This test runs in a separate process to avoid constant definition conflicts
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_check_returns_finding_when_file_edit_not_disabled() {
		// Ensure constant is not defined
		$result = Diagnostic_File_Edit_Disabled::check();

		$this->assertIsArray( $result, 'Expected array when DISALLOW_FILE_EDIT is not true' );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertEquals( 'file-edit-disabled', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test that check() returns finding when DISALLOW_FILE_EDIT is false
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_check_returns_finding_when_file_edit_explicitly_enabled() {
		// Define constant as false
		define( 'DISALLOW_FILE_EDIT', false );

		$result = Diagnostic_File_Edit_Disabled::check();

		$this->assertIsArray( $result, 'Expected array when DISALLOW_FILE_EDIT is false' );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertGreaterThan( 0, $result['threat_level'] );
	}

	/**
	 * Test diagnostic metadata methods
	 */
	public function test_diagnostic_metadata() {
		$this->assertEquals( 'file-edit-disabled', Diagnostic_File_Edit_Disabled::get_slug() );
		$this->assertEquals( 'File Edit Disabled Check', Diagnostic_File_Edit_Disabled::get_title() );
		$this->assertNotEmpty( Diagnostic_File_Edit_Disabled::get_description() );
	}
}
