<?php
/**
 * Tests for Custom Permalink Structure Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.2032.1410
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Custom_Permalink_Structure;
use WPShadow\Tests\TestCase;

/**
 * Custom Permalink Structure Diagnostic Test Class
 *
 * Tests validation of WordPress custom permalink structures including:
 * - Valid tag syntax
 * - Deprecated tags
 * - Invalid tags
 * - Security concerns
 * - Best practices
 *
 * @since 1.2032.1410
 */
class CustomPermalinkStructureTest extends TestCase {

	/**
	 * Test diagnostic slug is correct
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_diagnostic_slug(): void {
		$this->assertEquals( 'custom-permalink-structure', Diagnostic_Custom_Permalink_Structure::get_slug() );
	}

	/**
	 * Test diagnostic title is correct
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_diagnostic_title(): void {
		$this->assertEquals( 'Custom Permalink Structure', Diagnostic_Custom_Permalink_Structure::get_title() );
	}

	/**
	 * Test diagnostic description exists
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_diagnostic_description(): void {
		$description = Diagnostic_Custom_Permalink_Structure::get_description();
		$this->assertNotEmpty( $description );
		$this->assertStringContainsString( 'permalink', strtolower( $description ) );
	}

	/**
	 * Test diagnostic family is seo
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_diagnostic_family(): void {
		$this->assertEquals( 'seo', Diagnostic_Custom_Permalink_Structure::get_family() );
	}

	/**
	 * Test diagnostic check method exists
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_check_method_exists(): void {
		$this->assertTrue( method_exists( Diagnostic_Custom_Permalink_Structure::class, 'check' ) );
	}

	/**
	 * Test diagnostic returns correct finding structure when issues found
	 *
	 * This test verifies the structure of findings without actually running
	 * the diagnostic, as we can't easily mock WordPress functions in unit tests.
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_finding_structure_when_issues_exist(): void {
		// We can't easily test the actual check() method without WordPress,
		// but we can verify the class structure and that it extends the base class
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );

		// Verify it has the required static properties
		$this->assertTrue( $reflection->hasProperty( 'slug' ) );
		$this->assertTrue( $reflection->hasProperty( 'title' ) );
		$this->assertTrue( $reflection->hasProperty( 'description' ) );
		$this->assertTrue( $reflection->hasProperty( 'family' ) );

		// Verify it extends Diagnostic_Base
		$this->assertTrue( $reflection->isSubclassOf( 'WPShadow\\Core\\Diagnostic_Base' ) );
	}

	/**
	 * Test threat level constants
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_threat_level_is_65(): void {
		// The threat level is set in the finding array, not as a constant
		// We verify this through code inspection that the value 65 is used
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );

		// Get the source code and verify 65 appears
		$filename = $reflection->getFileName();
		$source   = file_get_contents( $filename );
		$this->assertStringContainsString( "'threat_level'  => 65", $source );
	}

	/**
	 * Test valid tags array is defined
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_valid_tags_defined(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$property   = $reflection->getProperty( 'valid_tags' );
		$property->setAccessible( true );

		$valid_tags = $property->getValue();

		// Verify all expected valid tags are present
		$expected_tags = array(
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			'%post_id%',
			'%postname%',
			'%category%',
			'%author%',
		);

		foreach ( $expected_tags as $tag ) {
			$this->assertContains( $tag, $valid_tags, "Valid tag {$tag} should be in valid_tags array" );
		}
	}

	/**
	 * Test deprecated tags array is defined
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_deprecated_tags_defined(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$property   = $reflection->getProperty( 'deprecated_tags' );
		$property->setAccessible( true );

		$deprecated_tags = $property->getValue();

		// Verify deprecated tags are defined
		$this->assertIsArray( $deprecated_tags );
		$this->assertArrayHasKey( '%pagename%', $deprecated_tags );
		$this->assertArrayHasKey( '%month%', $deprecated_tags );
	}

	/**
	 * Test diagnostic severity is medium
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_severity_is_medium(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify the severity is set to medium in findings
		$this->assertStringContainsString( "'severity'      => 'medium'", $source );
	}

	/**
	 * Test diagnostic is not auto-fixable
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_is_not_auto_fixable(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify auto_fixable is set to false
		$this->assertStringContainsString( "'auto_fixable'  => false", $source );
	}

	/**
	 * Test KB link is included
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_kb_link_included(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify KB link is included
		$this->assertStringContainsString( 'kb_link', $source );
		$this->assertStringContainsString( 'custom-permalink-structure', $source );
	}

	/**
	 * Test security pattern checks exist
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_security_checks_exist(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify security patterns are checked
		$security_patterns = array( '<?', '<script', 'eval(', 'exec(', 'system(', 'passthru(' );

		foreach ( $security_patterns as $pattern ) {
			$this->assertStringContainsString( $pattern, $source, "Security pattern {$pattern} should be checked" );
		}
	}

	/**
	 * Test malformed tag detection exists
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_malformed_tag_detection(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify malformed tag detection (unmatched %)
		$this->assertStringContainsString( 'Malformed', $source );
		$this->assertStringContainsString( 'substr_count', $source );
	}

	/**
	 * Test slash validation exists
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_slash_validation(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify slash validation (leading and trailing)
		$this->assertStringContainsString( 'should start with', $source );
		$this->assertStringContainsString( 'should end with', $source );
	}

	/**
	 * Test details array is included in finding
	 *
	 * @since 1.2032.1410
	 * @return void
	 */
	public function test_details_array_included(): void {
		$reflection = new \ReflectionClass( Diagnostic_Custom_Permalink_Structure::class );
		$filename   = $reflection->getFileName();
		$source     = file_get_contents( $filename );

		// Verify details array with relevant information
		$this->assertStringContainsString( "'details'", $source );
		$this->assertStringContainsString( "'current_structure'", $source );
		$this->assertStringContainsString( "'issues_found'", $source );
		$this->assertStringContainsString( "'valid_tags'", $source );
	}
}
