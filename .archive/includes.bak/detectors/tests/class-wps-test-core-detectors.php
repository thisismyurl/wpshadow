<?php

declare(strict_types=1);

namespace WPShadow\Detectors\Tests;

use WPShadow\Detectors\WPSHADOW_Detector_SSL_Configuration;
use WPShadow\Detectors\WPSHADOW_Detector_Site_Description;
use WPShadow\Detectors\WPSHADOW_Detector_Permalinks;
use WPShadow\Detectors\WPSHADOW_Detector_Backup_Plugin;
use WPShadow\Detectors\WPSHADOW_Detector_Memory_Limit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Test_Core_Detectors extends \WP_UnitTestCase {

	public function test_ssl_detector_detects_missing_ssl() {
		$detector = new WPSHADOW_Detector_SSL_Configuration();
		$count = $detector->run();

		$this->assertGreaterThanOrEqual( 0, $count );
	}

	public function test_ssl_detector_get_issue_count() {
		$detector = new WPSHADOW_Detector_SSL_Configuration();
		$count = $detector->get_issue_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
		$this->assertLessThanOrEqual( 1, $count );
	}

	public function test_ssl_detector_properties() {
		$detector = new WPSHADOW_Detector_SSL_Configuration();

		$this->assertEquals( 'ssl-configuration', $detector->detector_id );
		$this->assertEquals( 'critical', $detector->severity );
		$this->assertFalse( $detector->auto_fixable );
	}

	public function test_site_description_detector_runs() {
		$detector = new WPSHADOW_Detector_Site_Description();
		$count = $detector->run();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	public function test_site_description_detector_with_description() {
		update_option( 'blogdescription', 'Test Site Description' );

		$detector = new WPSHADOW_Detector_Site_Description();
		$count = $detector->get_issue_count();

		$this->assertEquals( 0, $count );

		delete_option( 'blogdescription' );
	}

	public function test_site_description_detector_without_description() {
		update_option( 'blogdescription', '' );

		$detector = new WPSHADOW_Detector_Site_Description();
		$count = $detector->get_issue_count();

		$this->assertEquals( 1, $count );

		delete_option( 'blogdescription' );
	}

	public function test_site_description_detector_auto_fixable() {
		$detector = new WPSHADOW_Detector_Site_Description();

		$this->assertTrue( $detector->auto_fixable );
	}

	public function test_permalinks_detector_runs() {
		$detector = new WPSHADOW_Detector_Permalinks();
		$count = $detector->run();

		$this->assertIsInt( $count );
	}

	public function test_permalinks_detector_with_structure() {
		update_option( 'permalink_structure', '/%postname%/' );

		$detector = new WPSHADOW_Detector_Permalinks();
		$count = $detector->get_issue_count();

		$this->assertEquals( 0, $count );

		delete_option( 'permalink_structure' );
	}

	public function test_permalinks_detector_without_structure() {
		update_option( 'permalink_structure', '' );

		$detector = new WPSHADOW_Detector_Permalinks();
		$count = $detector->get_issue_count();

		$this->assertEquals( 1, $count );

		delete_option( 'permalink_structure' );
	}

	public function test_permalinks_detector_auto_fixable() {
		$detector = new WPSHADOW_Detector_Permalinks();

		$this->assertTrue( $detector->auto_fixable );
	}

	public function test_backup_plugin_detector_runs() {
		$detector = new WPSHADOW_Detector_Backup_Plugin();
		$count = $detector->run();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	public function test_backup_plugin_detector_get_issue_count() {
		$detector = new WPSHADOW_Detector_Backup_Plugin();
		$count = $detector->get_issue_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
		$this->assertLessThanOrEqual( 1, $count );
	}

	public function test_backup_plugin_detector_not_auto_fixable() {
		$detector = new WPSHADOW_Detector_Backup_Plugin();

		$this->assertFalse( $detector->auto_fixable );
	}

	public function test_memory_limit_detector_runs() {
		$detector = new WPSHADOW_Detector_Memory_Limit();
		$count = $detector->run();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	public function test_memory_limit_detector_get_issue_count() {
		$detector = new WPSHADOW_Detector_Memory_Limit();
		$count = $detector->get_issue_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
		$this->assertLessThanOrEqual( 1, $count );
	}

	public function test_memory_limit_detector_not_auto_fixable() {
		$detector = new WPSHADOW_Detector_Memory_Limit();

		$this->assertFalse( $detector->auto_fixable );
	}

	public function test_all_detectors_extend_base_class() {
		$detectors = array(
			new WPSHADOW_Detector_SSL_Configuration(),
			new WPSHADOW_Detector_Site_Description(),
			new WPSHADOW_Detector_Permalinks(),
			new WPSHADOW_Detector_Backup_Plugin(),
			new WPSHADOW_Detector_Memory_Limit(),
		);

		foreach ( $detectors as $detector ) {
			$this->assertInstanceOf(
				'WPShadow\CoreSupport\WPSHADOW_Issue_Detection',
				$detector
			);
		}
	}

	public function test_all_detectors_have_valid_ids() {
		$detectors = array(
			new WPSHADOW_Detector_SSL_Configuration(),
			new WPSHADOW_Detector_Site_Description(),
			new WPSHADOW_Detector_Permalinks(),
			new WPSHADOW_Detector_Backup_Plugin(),
			new WPSHADOW_Detector_Memory_Limit(),
		);

		$ids = array();
		foreach ( $detectors as $detector ) {
			$this->assertNotEmpty( $detector->detector_id );
			$this->assertFalse( in_array( $detector->detector_id, $ids, true ) );
			$ids[] = $detector->detector_id;
		}
	}

	public function test_all_detectors_have_valid_severity() {
		$detectors = array(
			new WPSHADOW_Detector_SSL_Configuration(),
			new WPSHADOW_Detector_Site_Description(),
			new WPSHADOW_Detector_Permalinks(),
			new WPSHADOW_Detector_Backup_Plugin(),
			new WPSHADOW_Detector_Memory_Limit(),
		);

		$valid_severities = array( 'critical', 'high', 'medium', 'low' );

		foreach ( $detectors as $detector ) {
			$this->assertContains( $detector->severity, $valid_severities );
		}
	}

	public function test_ssl_detector_issue_data_structure() {
		$detector = new WPSHADOW_Detector_SSL_Configuration();
		$detector->run();

		$issues = $detector->get_issues();

		if ( ! empty( $issues ) ) {
			$issue = reset( $issues );
			$this->assertArrayHasKey( 'id', $issue );
			$this->assertArrayHasKey( 'severity', $issue );
			$this->assertArrayHasKey( 'title', $issue );
			$this->assertArrayHasKey( 'description', $issue );
			$this->assertArrayHasKey( 'resolution', $issue );
			$this->assertArrayHasKey( 'confidence', $issue );
		}
	}

	public function test_site_description_detector_issue_data_structure() {
		update_option( 'blogdescription', '' );

		$detector = new WPSHADOW_Detector_Site_Description();
		$detector->run();

		$issues = $detector->get_issues();

		if ( ! empty( $issues ) ) {
			$issue = reset( $issues );
			$this->assertArrayHasKey( 'id', $issue );
			$this->assertArrayHasKey( 'detector_id', $issue );
			$this->assertArrayHasKey( 'severity', $issue );
			$this->assertArrayHasKey( 'title', $issue );
		}

		delete_option( 'blogdescription' );
	}

	public function test_detector_confidence_scores() {
		$detectors = array(
			new WPSHADOW_Detector_SSL_Configuration(),
			new WPSHADOW_Detector_Site_Description(),
			new WPSHADOW_Detector_Permalinks(),
			new WPSHADOW_Detector_Backup_Plugin(),
			new WPSHADOW_Detector_Memory_Limit(),
		);

		foreach ( $detectors as $detector ) {
			$detector->run();
			$issues = $detector->get_issues();

			foreach ( $issues as $issue ) {
				$confidence = $issue['confidence'] ?? 0;
				$this->assertGreaterThanOrEqual( 0.0, $confidence );
				$this->assertLessThanOrEqual( 1.0, $confidence );
			}
		}
	}

	public function test_detector_performance_ssl() {
		$detector = new WPSHADOW_Detector_SSL_Configuration();

		$start = microtime( true );
		$detector->run();
		$end = microtime( true );

		$elapsed = ( $end - $start ) * 1000;

		$this->assertLessThan( 100, $elapsed );
	}

	public function test_detector_performance_site_description() {
		$detector = new WPSHADOW_Detector_Site_Description();

		$start = microtime( true );
		$detector->run();
		$end = microtime( true );

		$elapsed = ( $end - $start ) * 1000;

		$this->assertLessThan( 100, $elapsed );
	}

	public function test_detector_performance_permalinks() {
		$detector = new WPSHADOW_Detector_Permalinks();

		$start = microtime( true );
		$detector->run();
		$end = microtime( true );

		$elapsed = ( $end - $start ) * 1000;

		$this->assertLessThan( 100, $elapsed );
	}

	public function test_detector_performance_backup_plugin() {
		$detector = new WPSHADOW_Detector_Backup_Plugin();

		$start = microtime( true );
		$detector->run();
		$end = microtime( true );

		$elapsed = ( $end - $start ) * 1000;

		$this->assertLessThan( 100, $elapsed );
	}

	public function test_detector_performance_memory_limit() {
		$detector = new WPSHADOW_Detector_Memory_Limit();

		$start = microtime( true );
		$detector->run();
		$end = microtime( true );

		$elapsed = ( $end - $start ) * 1000;

		$this->assertLessThan( 100, $elapsed );
	}
}
