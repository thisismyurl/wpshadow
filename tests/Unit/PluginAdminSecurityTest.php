<?php
/**
 * Tests for Plugin Admin Page Security Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Plugin_Admin_Security;
use WP_Mock\Tools\TestCase;

/**
 * Plugin Admin Security Test Class
 *
 * @since 1.6093.1200
 */
class PluginAdminSecurityTest extends TestCase {

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
	 * Test diagnostic passes with secure plugins
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_secure_plugins(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags unescaped output
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_unescaped_output(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerable_files' => array(
					array(
						'file'   => '/plugins/test-plugin/admin.php',
						'plugin' => 'test-plugin',
						'issues' => array(
							array(
								'type'        => 'unescaped_output',
								'severity'    => 'high',
								'description' => 'Unescaped output detected',
							),
						),
					),
				),
				'total_scanned'    => 10,
				'issue_count'      => 1,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Found %2$d security issue',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'plugin-admin-page-security', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test diagnostic flags unsanitized input
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_unsanitized_input(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerable_files' => array(
					array(
						'file'   => '/plugins/bad-plugin/admin.php',
						'plugin' => 'bad-plugin',
						'issues' => array(
							array(
								'type'        => 'unsanitized_input',
								'severity'    => 'high',
								'description' => 'Direct superglobal access',
							),
						),
					),
				),
				'total_scanned'    => 8,
				'issue_count'      => 1,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['issue_count'] );
	}

	/**
	 * Test diagnostic flags missing nonce
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_missing_nonce(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerable_files' => array(
					array(
						'file'   => '/plugins/nonce-missing/admin.php',
						'plugin' => 'nonce-missing',
						'issues' => array(
							array(
								'type'        => 'missing_nonce',
								'severity'    => 'medium',
								'description' => 'Form processing without nonce',
							),
						),
					),
				),
				'total_scanned'    => 5,
				'issue_count'      => 1,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'medium', $result['severity'] );
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
				'vulnerable_files' => array(
					array(
						'file'   => '/test.php',
						'plugin' => 'test',
						'issues' => array(
							array( 'type' => 'unescaped_output', 'severity' => 'high' ),
						),
					),
				),
				'total_scanned'    => 3,
				'issue_count'      => 1,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

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

		$this->assertEquals( 'plugin-admin-page-security', $result['id'] );
		$this->assertEquals( 'security', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes scan statistics
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_scan_statistics(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerable_files' => array(
					array( 'file' => '/file1.php', 'plugin' => 'p1', 'issues' => array( array( 'type' => 'xss' ) ) ),
					array( 'file' => '/file2.php', 'plugin' => 'p2', 'issues' => array( array( 'type' => 'csrf' ) ) ),
				),
				'total_scanned'    => 15,
				'issue_count'      => 2,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 15, $result['meta']['total_scanned'] );
		$this->assertEquals( 2, $result['meta']['vulnerable_files'] );
		$this->assertEquals( 2, $result['meta']['issue_count'] );
	}

	/**
	 * Test details include vulnerable file list
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_vulnerable_file_list(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerable_files' => array(
					array( 'file' => '/vuln1.php', 'plugin' => 'p1', 'issues' => array( array( 'type' => 'xss' ) ) ),
					array( 'file' => '/vuln2.php', 'plugin' => 'p2', 'issues' => array( array( 'type' => 'csrf' ) ) ),
				),
				'total_scanned'    => 10,
				'issue_count'      => 2,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'vulnerable_files', $result['details'] );
		$this->assertCount( 2, $result['details']['vulnerable_files'] );
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
				'vulnerable_files' => array(
					array( 'file' => '/test.php', 'plugin' => 'test', 'issues' => array( array( 'type' => 'xss' ) ) ),
				),
				'total_scanned'    => 5,
				'issue_count'      => 1,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

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
				'vulnerable_files' => array(
					array(
						'file'   => '/critical.php',
						'plugin' => 'critical',
						'issues' => array(
							array( 'type' => 'unescaped_output', 'severity' => 'high' ),
						),
					),
				),
				'total_scanned'    => 8,
				'issue_count'      => 1,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 65, $result['threat_level'] );
	}

	/**
	 * Test issue types tracked
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_issue_types_tracked(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'vulnerable_files' => array(
					array(
						'file'   => '/test.php',
						'plugin' => 'test',
						'issues' => array(
							array( 'type' => 'unescaped_output' ),
							array( 'type' => 'missing_nonce' ),
						),
					),
				),
				'total_scanned'    => 5,
				'issue_count'      => 2,
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

		$result = Diagnostic_Plugin_Admin_Security::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'issue_types', $result['meta'] );
	}
}
