<?php
/**
 * Tests for Dead Code Detection Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6028.1655
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Dead_Code;
use WP_Mock\Tools\TestCase;

/**
 * Dead Code Diagnostic Test Class
 *
 * @since 1.6028.1655
 */
class DeadCodeTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes with minimal dead code
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_passes_with_minimal_dead_code(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array(),
				'dead_classes'    => array(),
				'total_defined'   => 150,
				'total_dead'      => 5,
				'dead_percentage' => 3.3,
				'files_scanned'   => 25,
			),
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags high dead code percentage
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_flags_high_dead_code_percentage(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array(
					'unused_function_1' => '/path/to/file.php',
					'unused_function_2' => '/path/to/file2.php',
				),
				'dead_classes'    => array(
					'UnusedClass' => '/path/to/class.php',
				),
				'total_defined'   => 100,
				'total_dead'      => 30,
				'dead_percentage' => 30.0,
				'files_scanned'   => 15,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 30.0% dead code (30 unused functions/classes).',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'dead-code-detection', $result['id'] );
		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertEquals( 30.0, $result['meta']['dead_percentage'] );
	}

	/**
	 * Test diagnostic flags excessive dead code
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_flags_excessive_dead_code(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array_fill_keys( array_map( fn( $i ) => "unused_func_$i", range( 1, 40 ) ), '/path/file.php' ),
				'dead_classes'    => array(),
				'total_defined'   => 100,
				'total_dead'      => 40,
				'dead_percentage' => 40.0,
				'files_scanned'   => 20,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 25, $result['threat_level'] );
		$this->assertEquals( 40, $result['meta']['total_dead'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array( 'test_func' => '/file.php' ),
				'dead_classes'    => array( 'TestClass' => '/class.php' ),
				'total_defined'   => 50,
				'total_dead'      => 10,
				'dead_percentage' => 20.0,
				'files_scanned'   => 10,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertArrayHasKey( 'family', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );

		$this->assertEquals( 'dead-code-detection', $result['id'] );
		$this->assertEquals( 'code-quality', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes scan statistics
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_meta_includes_scan_statistics(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array( 'func1' => '/file.php', 'func2' => '/file.php' ),
				'dead_classes'    => array( 'Class1' => '/class.php' ),
				'total_defined'   => 75,
				'total_dead'      => 15,
				'dead_percentage' => 20.0,
				'files_scanned'   => 30,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 20.0, $result['meta']['dead_percentage'] );
		$this->assertEquals( 75, $result['meta']['total_defined'] );
		$this->assertEquals( 15, $result['meta']['total_dead'] );
		$this->assertEquals( 30, $result['meta']['files_scanned'] );
	}

	/**
	 * Test details include function samples
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_details_include_function_samples(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array(
					'unused_1' => '/file1.php',
					'unused_2' => '/file2.php',
					'unused_3' => '/file3.php',
				),
				'dead_classes'    => array(),
				'total_defined'   => 50,
				'total_dead'      => 15,
				'dead_percentage' => 30.0,
				'files_scanned'   => 12,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'dead_function_samples', $result['details'] );
		$this->assertNotEmpty( $result['details']['dead_function_samples'] );
	}

	/**
	 * Test details include recommendations
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_details_include_recommendations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array( 'test' => '/file.php' ),
				'dead_classes'    => array(),
				'total_defined'   => 40,
				'total_dead'      => 10,
				'dead_percentage' => 25.0,
				'files_scanned'   => 8,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommendations', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommendations'] );
	}

	/**
	 * Test threat level scales with percentage
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_threat_level_scales_with_percentage(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array_fill_keys( range( 1, 20 ), '/file.php' ),
				'dead_classes'    => array(),
				'total_defined'   => 50,
				'total_dead'      => 20,
				'dead_percentage' => 40.0,
				'files_scanned'   => 10,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 25, $result['threat_level'] );
		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test class samples included
	 *
	 * @since 1.6028.1655
	 * @return void
	 */
	public function test_class_samples_included(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'dead_functions'  => array(),
				'dead_classes'    => array(
					'UnusedClass1' => '/class1.php',
					'UnusedClass2' => '/class2.php',
				),
				'total_defined'   => 30,
				'total_dead'      => 10,
				'dead_percentage' => 33.3,
				'files_scanned'   => 5,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Dead_Code::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'dead_class_samples', $result['details'] );
		$this->assertNotEmpty( $result['details']['dead_class_samples'] );
	}
}
