<?php
/**
 * Tests for Theme Asset Loading Errors Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Theme_Asset_Errors;
use WP_Mock\Tools\TestCase;

/**
 * Theme Asset Errors Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class ThemeAssetErrorsTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes with no broken assets
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_no_broken_assets(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags broken CSS file
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_broken_css_file(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array(
						'url'    => 'http://example.com/style.css',
						'handle' => 'theme-style',
						'type'   => 'css',
						'status' => 404,
					),
				),
				'working_assets' => array(),
				'total_tested'   => 5,
				'total_broken'   => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Found %1$d broken CSS/JS file reference',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'theme-asset-loading-errors', $result['id'] );
		$this->assertEquals( 1, $result['meta']['total_broken'] );
	}

	/**
	 * Test diagnostic flags broken JS file
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_broken_js_file(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array(
						'url'    => 'http://example.com/script.js',
						'handle' => 'theme-script',
						'type'   => 'js',
						'status' => 404,
					),
				),
				'working_assets' => array(),
				'total_tested'   => 3,
				'total_broken'   => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['broken_js'] );
	}

	/**
	 * Test diagnostic flags multiple broken assets
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_multiple_broken_assets(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array( 'url' => 'http://example.com/style1.css', 'handle' => 'style1', 'type' => 'css', 'status' => 404 ),
					array( 'url' => 'http://example.com/style2.css', 'handle' => 'style2', 'type' => 'css', 'status' => 404 ),
					array( 'url' => 'http://example.com/script1.js', 'handle' => 'script1', 'type' => 'js', 'status' => 404 ),
				),
				'working_assets' => array(),
				'total_tested'   => 10,
				'total_broken'   => 3,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 3, $result['meta']['total_broken'] );
		$this->assertEquals( 2, $result['meta']['broken_css'] );
		$this->assertEquals( 1, $result['meta']['broken_js'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array( 'url' => 'http://example.com/broken.css', 'handle' => 'broken', 'type' => 'css', 'status' => 404 ),
				),
				'working_assets' => array(),
				'total_tested'   => 5,
				'total_broken'   => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

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

		$this->assertEquals( 'theme-asset-loading-errors', $result['id'] );
		$this->assertEquals( 'design', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes asset counts
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_asset_counts(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array( 'type' => 'css', 'url' => 'test1.css', 'handle' => 'h1', 'status' => 404 ),
					array( 'type' => 'css', 'url' => 'test2.css', 'handle' => 'h2', 'status' => 404 ),
					array( 'type' => 'js', 'url' => 'test.js', 'handle' => 'h3', 'status' => 404 ),
				),
				'working_assets' => array(),
				'total_tested'   => 8,
				'total_broken'   => 3,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 8, $result['meta']['total_tested'] );
		$this->assertEquals( 3, $result['meta']['total_broken'] );
		$this->assertEquals( 2, $result['meta']['broken_css'] );
		$this->assertEquals( 1, $result['meta']['broken_js'] );
	}

	/**
	 * Test details include broken asset list
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_broken_asset_list(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array( 'url' => 'broken1.css', 'handle' => 'h1', 'type' => 'css', 'status' => 404 ),
					array( 'url' => 'broken2.js', 'handle' => 'h2', 'type' => 'js', 'status' => 404 ),
				),
				'working_assets' => array(),
				'total_tested'   => 5,
				'total_broken'   => 2,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'broken_assets', $result['details'] );
		$this->assertCount( 2, $result['details']['broken_assets'] );
	}

	/**
	 * Test recommendations included
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_recommendations_included(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array( 'url' => 'test.css', 'handle' => 'h', 'type' => 'css', 'status' => 404 ),
				),
				'working_assets' => array(),
				'total_tested'   => 3,
				'total_broken'   => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'recommendations', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommendations'] );
	}

	/**
	 * Test severity scales with broken count
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_severity_scales_with_broken_count(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array_fill( 0, 12, array( 'url' => 'test.css', 'handle' => 'h', 'type' => 'css', 'status' => 404 ) ),
				'working_assets' => array(),
				'total_tested'   => 15,
				'total_broken'   => 12,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 50, $result['threat_level'] );
	}

	/**
	 * Test impact message included
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_impact_message_included(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'broken_assets'  => array(
					array( 'url' => 'test.css', 'handle' => 'h', 'type' => 'css', 'status' => 404 ),
				),
				'working_assets' => array(),
				'total_tested'   => 4,
				'total_broken'   => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Asset_Errors::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'impact', $result['details'] );
		$this->assertNotEmpty( $result['details']['impact'] );
	}
}
