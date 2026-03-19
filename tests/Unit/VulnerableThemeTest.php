<?php
/**
 * Tests for Vulnerable Theme Detection Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Vulnerable_Theme;
use WP_Mock\Tools\TestCase;

/**
 * Vulnerable Theme Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class VulnerableThemeTest extends TestCase {

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
	 * Test diagnostic passes with secure theme
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_secure_theme(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags outdated theme
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_outdated_theme(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerabilities'     => array(
					array(
						'type'        => 'outdated',
						'severity'    => 'medium',
						'description' => 'Theme outdated',
					),
				),
				'theme_name'          => 'Test Theme',
				'theme_slug'          => 'test-theme',
				'theme_version'       => '1.0.0',
				'latest_version'      => '1.2.0',
				'is_outdated'         => true,
				'vulnerability_count' => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Theme %1$s has %2$d known security vulnerability',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com/wp-admin/themes.php',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'vulnerable-theme-detected', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertTrue( $result['meta']['is_outdated'] );
	}

	/**
	 * Test diagnostic flags known vulnerability
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_known_vulnerability(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerabilities'     => array(
					array(
						'type'          => 'known_vulnerability',
						'vulnerability' => 'XSS',
						'severity'      => 'high',
						'theme'         => 'vulnerable-theme',
					),
				),
				'theme_name'          => 'Vulnerable Theme',
				'theme_slug'          => 'vulnerable-theme',
				'theme_version'       => '1.0.0',
				'latest_version'      => '',
				'is_outdated'         => false,
				'vulnerability_count' => 1,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com/wp-admin/themes.php',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 90, $result['threat_level'] );
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
				'vulnerabilities'     => array(
					array( 'type' => 'outdated', 'severity' => 'medium' ),
				),
				'theme_name'          => 'Test Theme',
				'theme_slug'          => 'test',
				'theme_version'       => '1.0',
				'latest_version'      => '1.5',
				'is_outdated'         => true,
				'vulnerability_count' => 1,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

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

		$this->assertEquals( 'vulnerable-theme-detected', $result['id'] );
		$this->assertEquals( 'security', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes theme details
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_theme_details(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerabilities'     => array( array( 'type' => 'outdated' ) ),
				'theme_name'          => 'My Theme',
				'theme_slug'          => 'my-theme',
				'theme_version'       => '2.1.0',
				'latest_version'      => '2.5.0',
				'is_outdated'         => true,
				'vulnerability_count' => 1,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 'My Theme', $result['meta']['theme_name'] );
		$this->assertEquals( 'my-theme', $result['meta']['theme_slug'] );
		$this->assertEquals( '2.1.0', $result['meta']['theme_version'] );
		$this->assertEquals( '2.5.0', $result['meta']['latest_version'] );
	}

	/**
	 * Test details include vulnerability list
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_vulnerability_list(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerabilities'     => array(
					array( 'type' => 'outdated', 'description' => 'Theme outdated' ),
					array( 'type' => 'xss', 'description' => 'XSS vulnerability' ),
				),
				'theme_name'          => 'Vuln Theme',
				'theme_slug'          => 'vuln',
				'theme_version'       => '1.0',
				'latest_version'      => '2.0',
				'is_outdated'         => true,
				'vulnerability_count' => 2,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'vulnerabilities', $result['details'] );
		$this->assertCount( 2, $result['details']['vulnerabilities'] );
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
				'vulnerabilities'     => array( array( 'type' => 'outdated' ) ),
				'theme_name'          => 'Test',
				'theme_slug'          => 'test',
				'theme_version'       => '1.0',
				'latest_version'      => '1.5',
				'is_outdated'         => true,
				'vulnerability_count' => 1,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'recommendations', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommendations'] );
	}

	/**
	 * Test threat level scales with severity
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_threat_level_scales_with_severity(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerabilities'     => array(
					array( 'type' => 'known_vulnerability', 'severity' => 'high' ),
				),
				'theme_name'          => 'Critical Theme',
				'theme_slug'          => 'critical',
				'theme_version'       => '1.0',
				'latest_version'      => '',
				'is_outdated'         => false,
				'vulnerability_count' => 1,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 90, $result['threat_level'] );
		$this->assertEquals( 'critical', $result['severity'] );
	}

	/**
	 * Test vulnerability count accuracy
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_vulnerability_count_accuracy(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerabilities'     => array(
					array( 'type' => 'xss' ),
					array( 'type' => 'sql_injection' ),
					array( 'type' => 'outdated' ),
				),
				'theme_name'          => 'Multi Vuln',
				'theme_slug'          => 'multi',
				'theme_version'       => '1.0',
				'latest_version'      => '2.0',
				'is_outdated'         => true,
				'vulnerability_count' => 3,
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

		\WP_Mock::userFunction( 'admin_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_Vulnerable_Theme::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 3, $result['meta']['vulnerability_count'] );
	}
}
