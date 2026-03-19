<?php
/**
 * Tests for Backup Frequency Validation Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Backup_Frequency;
use WP_Mock\Tools\TestCase;

/**
 * Backup Frequency Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class BackupFrequencyTest extends TestCase {

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
	 * Test diagnostic passes when backups recent
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_when_backups_recent(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => true,
				'backup_plugin_name'     => 'UpdraftPlus',
				'last_backup_timestamp'  => time() - ( 2 * DAY_IN_SECONDS ),
				'days_since_backup'      => 2,
				'backup_status'          => 'recent',
				'issues'                 => array(),
			),
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags no backup plugin
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_no_backup_plugin(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => false,
				'backup_plugin_name'     => '',
				'last_backup_timestamp'  => 0,
				'days_since_backup'      => 0,
				'backup_status'          => 'no_plugin',
				'issues'                 => array( 'No backup plugin detected' ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'backup-frequency-validation', $result['id'] );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 75, $result['threat_level'] );
	}

	/**
	 * Test diagnostic flags plugin but no backups
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_plugin_but_no_backups(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => true,
				'backup_plugin_name'     => 'BackWPup',
				'last_backup_timestamp'  => 0,
				'days_since_backup'      => 0,
				'backup_status'          => 'no_backups',
				'issues'                 => array( 'BackWPup is installed but no backups have been created' ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 70, $result['threat_level'] );
	}

	/**
	 * Test diagnostic flags outdated backup
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_outdated_backup(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => true,
				'backup_plugin_name'     => 'UpdraftPlus',
				'last_backup_timestamp'  => time() - ( 10 * DAY_IN_SECONDS ),
				'last_backup_date'       => 'January 18, 2025',
				'days_since_backup'      => 10,
				'backup_status'          => 'outdated',
				'issues'                 => array( 'Last backup was 10 days ago' ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 10, $result['meta']['days_since_backup'] );
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
				'backup_plugin_detected' => false,
				'backup_plugin_name'     => '',
				'last_backup_timestamp'  => 0,
				'last_backup_date'       => '',
				'days_since_backup'      => 0,
				'backup_status'          => 'no_plugin',
				'issues'                 => array( 'No backup plugin' ),
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

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Backup_Frequency::check();

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

		$this->assertEquals( 'backup-frequency-validation', $result['id'] );
		$this->assertEquals( 'monitoring', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes backup data
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_backup_data(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => true,
				'backup_plugin_name'     => 'Duplicator',
				'last_backup_timestamp'  => time() - ( 8 * DAY_IN_SECONDS ),
				'last_backup_date'       => 'January 20, 2025',
				'days_since_backup'      => 8,
				'backup_status'          => 'outdated',
				'issues'                 => array( 'Backup outdated' ),
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

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertTrue( $result['meta']['backup_plugin_detected'] );
		$this->assertEquals( 'Duplicator', $result['meta']['backup_plugin_name'] );
		$this->assertEquals( 8, $result['meta']['days_since_backup'] );
		$this->assertEquals( 'outdated', $result['meta']['backup_status'] );
	}

	/**
	 * Test details include why backups matter
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_why_matters(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => false,
				'backup_plugin_name'     => '',
				'last_backup_timestamp'  => 0,
				'last_backup_date'       => '',
				'days_since_backup'      => 0,
				'backup_status'          => 'no_plugin',
				'issues'                 => array( 'No plugin' ),
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

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'why_backups_matter', $result['details'] );
		$this->assertNotEmpty( $result['details']['why_backups_matter'] );
	}

	/**
	 * Test details include backup plugins list
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_backup_plugins(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => false,
				'backup_plugin_name'     => '',
				'last_backup_timestamp'  => 0,
				'last_backup_date'       => '',
				'days_since_backup'      => 0,
				'backup_status'          => 'no_plugin',
				'issues'                 => array( 'No plugin' ),
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

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'backup_plugins', $result['details'] );
		$this->assertArrayHasKey( 'UpdraftPlus', $result['details']['backup_plugins'] );
	}

	/**
	 * Test threat level calculation
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_threat_level_calculation(): void {
		// Very old backup = highest threat.
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => true,
				'backup_plugin_name'     => 'UpdraftPlus',
				'last_backup_timestamp'  => time() - ( 35 * DAY_IN_SECONDS ),
				'last_backup_date'       => 'December 24, 2024',
				'days_since_backup'      => 35,
				'backup_status'          => 'outdated',
				'issues'                 => array( 'Very old backup' ),
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

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 65, $result['threat_level'] );
		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test data loss risk calculation
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_data_loss_risk_calculation(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'backup_plugin_detected' => true,
				'backup_plugin_name'     => 'WPvivid Backup',
				'last_backup_timestamp'  => time() - ( 16 * DAY_IN_SECONDS ),
				'last_backup_date'       => 'January 12, 2025',
				'days_since_backup'      => 16,
				'backup_status'          => 'outdated',
				'issues'                 => array( 'Outdated' ),
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

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Backup_Frequency::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'data_loss_risk', $result['meta'] );
		$this->assertStringContainsString( 'week', $result['meta']['data_loss_risk'] );
	}
}
