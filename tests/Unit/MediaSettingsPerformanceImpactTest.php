<?php
/**
 * Media Settings Performance Impact Diagnostic Test
 *
 * @package WPShadow\Tests\Unit
 * @since   1.26032.1410
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Tests\TestCase;
use WPShadow\Diagnostics\Diagnostic_Media_Settings_Performance_Impact;

/**
 * Test Media Settings Performance Impact Diagnostic
 *
 * @since 1.26032.1410
 */
class MediaSettingsPerformanceImpactTest extends TestCase {

	/**
	 * Test that diagnostic can be instantiated
	 *
	 * @return void
	 */
	public function test_diagnostic_exists(): void {
		$this->assertTrue(
			class_exists( 'WPShadow\Diagnostics\Diagnostic_Media_Settings_Performance_Impact' ),
			'Diagnostic class should exist'
		);
	}

	/**
	 * Test that diagnostic has required properties
	 *
	 * @return void
	 */
	public function test_diagnostic_properties(): void {
		$this->assertEquals( 'media-settings-performance-impact', Diagnostic_Media_Settings_Performance_Impact::get_slug() );
		$this->assertEquals( 'Media Settings Performance Impact', Diagnostic_Media_Settings_Performance_Impact::get_title() );
		$this->assertEquals( 'performance', Diagnostic_Media_Settings_Performance_Impact::get_family() );
	}

	/**
	 * Test that check method returns null when no issues
	 *
	 * This test would require WordPress mocking framework.
	 * For now, we just verify the method exists.
	 *
	 * @return void
	 */
	public function test_check_method_exists(): void {
		$this->assertTrue(
			method_exists( Diagnostic_Media_Settings_Performance_Impact::class, 'check' ),
			'check() method should exist'
		);
	}

	/**
	 * Test that diagnostic follows proper structure
	 *
	 * @return void
	 */
	public function test_extends_diagnostic_base(): void {
		$reflection = new \ReflectionClass( Diagnostic_Media_Settings_Performance_Impact::class );
		$this->assertEquals(
			'WPShadow\Core\Diagnostic_Base',
			$reflection->getParentClass()->getName(),
			'Diagnostic should extend Diagnostic_Base'
		);
	}

	/**
	 * Test HIGH_IMPACT_THRESHOLD constant
	 *
	 * @return void
	 */
	public function test_high_impact_threshold_defined(): void {
		$reflection = new \ReflectionClass( Diagnostic_Media_Settings_Performance_Impact::class );
		$this->assertTrue(
			$reflection->hasConstant( 'HIGH_IMPACT_THRESHOLD' ),
			'HIGH_IMPACT_THRESHOLD constant should be defined'
		);
	}

	/**
	 * Test that get_slug returns correct value
	 *
	 * @return void
	 */
	public function test_get_slug(): void {
		$slug = Diagnostic_Media_Settings_Performance_Impact::get_slug();
		$this->assertIsString( $slug );
		$this->assertEquals( 'media-settings-performance-impact', $slug );
	}

	/**
	 * Test that get_title returns correct value
	 *
	 * @return void
	 */
	public function test_get_title(): void {
		$title = Diagnostic_Media_Settings_Performance_Impact::get_title();
		$this->assertIsString( $title );
		$this->assertEquals( 'Media Settings Performance Impact', $title );
	}

	/**
	 * Test that get_description returns correct value
	 *
	 * @return void
	 */
	public function test_get_description(): void {
		$description = Diagnostic_Media_Settings_Performance_Impact::get_description();
		$this->assertIsString( $description );
		$this->assertNotEmpty( $description );
	}

	/**
	 * Test that get_family returns correct value
	 *
	 * @return void
	 */
	public function test_get_family(): void {
		$family = Diagnostic_Media_Settings_Performance_Impact::get_family();
		$this->assertIsString( $family );
		$this->assertEquals( 'performance', $family );
	}
}
