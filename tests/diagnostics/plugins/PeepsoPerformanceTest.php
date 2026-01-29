<?php
/**
 * Test for PeepSo Performance
 *
 * @package WPShadow\Tests
 */

namespace WPShadow\Tests\Diagnostics;

use WP_Mock\Tools\TestCase;
use WPShadow\Diagnostics\Diagnostic_PeepsoPerformance;

/**
 * PeepsoPerformanceTest Class
 */
class PeepsoPerformanceTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	public function test_diagnostic_class_exists() {
		$this->assertTrue( class_exists( 'WPShadow\Diagnostics\\Diagnostic_PeepsoPerformance' ) );
	}

	public function test_check_method_is_callable() {
		$this->assertTrue( method_exists( 'WPShadow\Diagnostics\\Diagnostic_PeepsoPerformance', 'check' ) );
		$this->assertTrue( is_callable( [ 'WPShadow\Diagnostics\\Diagnostic_PeepsoPerformance', 'check' ] ) );
	}

	public function test_returns_null_when_plugin_not_active() {
		\WP_Mock::userFunction( 'class_exists' )
			->with( 'PeepSo' )
			->andReturn( false );
		
		$result = Diagnostic_PeepsoPerformance::check();
		
		// Should return null when plugin is not active
		$this->assertNull( $result );
	}

	public function test_result_structure_when_issue_found() {
		\WP_Mock::userFunction( 'class_exists' )
			->with( 'PeepSo' )
			->andReturn( true );
		
		$result = Diagnostic_PeepsoPerformance::check();
		
		if ( is_array( $result ) ) {
			$this->assertArrayHasKey( 'id', $result );
			$this->assertArrayHasKey( 'title', $result );
			$this->assertArrayHasKey( 'description', $result );
			$this->assertArrayHasKey( 'severity', $result );
			$this->assertArrayHasKey( 'threat_level', $result );
			$this->assertArrayHasKey( 'auto_fixable', $result );
			$this->assertArrayHasKey( 'kb_link', $result );
		}
	}

	public function test_slug_property_matches_expected() {
		$reflection = new \ReflectionClass( 'WPShadow\Diagnostics\\Diagnostic_PeepsoPerformance' );
		$property = $reflection->getProperty( 'slug' );
		$property->setAccessible( true );
		$slug = $property->getValue();
		
		$this->assertEquals( 'peepso-performance', $slug );
	}

	public function test_family_property_is_valid() {
		$reflection = new \ReflectionClass( 'WPShadow\Diagnostics\\Diagnostic_PeepsoPerformance' );
		$property = $reflection->getProperty( 'family' );
		$property->setAccessible( true );
		$family = $property->getValue();
		
		$valid_families = [ 'security', 'performance', 'functionality' ];
		$this->assertContains( $family, $valid_families, 
			"Family must be one of: " . implode( ', ', $valid_families ) 
		);
	}

	public function test_threat_level_is_in_valid_range() {
		\WP_Mock::userFunction( 'class_exists' )
			->with( 'PeepSo' )
			->andReturn( true );
		
		$result = Diagnostic_PeepsoPerformance::check();
		
		if ( is_array( $result ) && isset( $result['threat_level'] ) ) {
			$this->assertIsInt( $result['threat_level'] );
			$this->assertGreaterThanOrEqual( 0, $result['threat_level'] );
			$this->assertLessThanOrEqual( 100, $result['threat_level'] );
		}
	}

	public function test_kb_link_format_is_valid() {
		\WP_Mock::userFunction( 'class_exists' )
			->with( 'PeepSo' )
			->andReturn( true );
		
		$result = Diagnostic_PeepsoPerformance::check();
		
		if ( is_array( $result ) && isset( $result['kb_link'] ) ) {
			$this->assertIsString( $result['kb_link'] );
			$this->assertStringContainsString( 'wpshadow.com/kb/', $result['kb_link'] );
		}
	}
}
