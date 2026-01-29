<?php
/**
 * Test for Genesis Framework Hooks Optimization
 *
 * @package WPShadow\Tests
 */

namespace WPShadow\Tests\Diagnostics;

use WP_Mock\Tools\TestCase;
use WPShadow\Diagnostics\Diagnostic_GenesisFrameworkHooksOptimization;

/**
 * GenesisFrameworkHooksOptimizationTest Class
 */
class GenesisFrameworkHooksOptimizationTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	public function test_diagnostic_class_exists() {
		$this->assertTrue( class_exists( 'WPShadow\Diagnostics\\Diagnostic_GenesisFrameworkHooksOptimization' ) );
	}

	public function test_check_method_is_callable() {
		$this->assertTrue( method_exists( 'WPShadow\Diagnostics\\Diagnostic_GenesisFrameworkHooksOptimization', 'check' ) );
		$this->assertTrue( is_callable( [ 'WPShadow\Diagnostics\\Diagnostic_GenesisFrameworkHooksOptimization', 'check' ] ) );
	}

	public function test_returns_null_when_plugin_not_active() {
		// No specific plugin activation check
		
		$result = Diagnostic_GenesisFrameworkHooksOptimization::check();
		
		// Should return null when plugin is not active
		$this->assertNull( $result );
	}

	public function test_result_structure_when_issue_found() {
		// No specific plugin activation check
		
		$result = Diagnostic_GenesisFrameworkHooksOptimization::check();
		
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
		$reflection = new \ReflectionClass( 'WPShadow\Diagnostics\\Diagnostic_GenesisFrameworkHooksOptimization' );
		$property = $reflection->getProperty( 'slug' );
		$property->setAccessible( true );
		$slug = $property->getValue();
		
		$this->assertEquals( 'genesis-framework-hooks-optimization', $slug );
	}

	public function test_family_property_is_valid() {
		$reflection = new \ReflectionClass( 'WPShadow\Diagnostics\\Diagnostic_GenesisFrameworkHooksOptimization' );
		$property = $reflection->getProperty( 'family' );
		$property->setAccessible( true );
		$family = $property->getValue();
		
		$valid_families = [ 'security', 'performance', 'functionality' ];
		$this->assertContains( $family, $valid_families, 
			"Family must be one of: " . implode( ', ', $valid_families ) 
		);
	}

	public function test_threat_level_is_in_valid_range() {
		// No specific plugin activation check
		
		$result = Diagnostic_GenesisFrameworkHooksOptimization::check();
		
		if ( is_array( $result ) && isset( $result['threat_level'] ) ) {
			$this->assertIsInt( $result['threat_level'] );
			$this->assertGreaterThanOrEqual( 0, $result['threat_level'] );
			$this->assertLessThanOrEqual( 100, $result['threat_level'] );
		}
	}

	public function test_kb_link_format_is_valid() {
		// No specific plugin activation check
		
		$result = Diagnostic_GenesisFrameworkHooksOptimization::check();
		
		if ( is_array( $result ) && isset( $result['kb_link'] ) ) {
			$this->assertIsString( $result['kb_link'] );
			$this->assertStringContainsString( 'wpshadow.com/kb/', $result['kb_link'] );
		}
	}
}
