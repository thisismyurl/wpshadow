<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport\Tests;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Test_Issue_Repository extends \WP_UnitTestCase {

	private \WPShadow\CoreSupport\WPSHADOW_Issue_Repository $repository;

	private array $test_issue_data = array(
		'id'            => 'test-issue-1',
		'severity'      => 'critical',
		'title'         => 'Test Issue',
		'description'   => 'A test issue',
		'detected_at'   => 1234567890,
		'detector_id'   => 'test-detector',
		'confidence'    => 0.95,
	);

	public function setUp(): void {
		parent::setUp();
		$this->repository = new \WPShadow\CoreSupport\WPSHADOW_Issue_Repository();
	}

	public function tearDown(): void {
		$this->repository->delete_all_current_issues();
		parent::tearDown();
	}

	public function test_store_single_issue() {
		$result = $this->repository->store_issue( $this->test_issue_data['id'], $this->test_issue_data );

		$this->assertTrue( $result );
		$this->assertEquals( 1, $this->repository->get_issue_count() );
	}

	public function test_get_current_issues() {
		$this->repository->store_issue( 'issue-1', $this->test_issue_data );

		$issues = $this->repository->get_current_issues();

		$this->assertIsArray( $issues );
		$this->assertArrayHasKey( 'issue-1', $issues );
	}

	public function test_get_specific_issue() {
		$this->repository->store_issue( 'issue-1', $this->test_issue_data );

		$issue = $this->repository->get_issue( 'issue-1' );

		$this->assertIsArray( $issue );
		$this->assertEquals( 'test-issue-1', $issue['id'] );
	}

	public function test_get_non_existent_issue_returns_null() {
		$issue = $this->repository->get_issue( 'non-existent' );

		$this->assertNull( $issue );
	}

	public function test_store_multiple_issues() {
		$issues = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
			'issue-2' => array_merge( $this->test_issue_data, array( 'id' => 'issue-2', 'severity' => 'high' ) ),
			'issue-3' => array_merge( $this->test_issue_data, array( 'id' => 'issue-3', 'severity' => 'medium' ) ),
		);

		$result = $this->repository->store_issues( $issues );

		$this->assertTrue( $result );
		$this->assertEquals( 3, $this->repository->get_issue_count() );
	}

	public function test_delete_issue() {
		$this->repository->store_issue( 'issue-1', $this->test_issue_data );
		$this->assertEquals( 1, $this->repository->get_issue_count() );

		$result = $this->repository->delete_issue( 'issue-1' );

		$this->assertTrue( $result );
		$this->assertEquals( 0, $this->repository->get_issue_count() );
	}

	public function test_delete_non_existent_issue_returns_false() {
		$result = $this->repository->delete_issue( 'non-existent' );

		$this->assertFalse( $result );
	}

	public function test_delete_all_current_issues() {
		$this->repository->store_issue( 'issue-1', $this->test_issue_data );
		$this->repository->store_issue( 'issue-2', $this->test_issue_data );

		$result = $this->repository->delete_all_current_issues();

		$this->assertTrue( $result );
		$this->assertEquals( 0, $this->repository->get_issue_count() );
	}

	public function test_get_issues_by_severity() {
		$this->repository->store_issues(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1', 'severity' => 'critical' ) ),
				'issue-2' => array_merge( $this->test_issue_data, array( 'id' => 'issue-2', 'severity' => 'high' ) ),
				'issue-3' => array_merge( $this->test_issue_data, array( 'id' => 'issue-3', 'severity' => 'critical' ) ),
			)
		);

		$critical_issues = $this->repository->get_issues_by_severity( 'critical' );

		$this->assertCount( 2, $critical_issues );
	}

	public function test_has_issues() {
		$this->assertFalse( $this->repository->has_issues() );

		$this->repository->store_issue( 'issue-1', $this->test_issue_data );

		$this->assertTrue( $this->repository->has_issues() );
	}

	public function test_get_severity_breakdown() {
		$this->repository->store_issues(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1', 'severity' => 'critical' ) ),
				'issue-2' => array_merge( $this->test_issue_data, array( 'id' => 'issue-2', 'severity' => 'critical' ) ),
				'issue-3' => array_merge( $this->test_issue_data, array( 'id' => 'issue-3', 'severity' => 'high' ) ),
				'issue-4' => array_merge( $this->test_issue_data, array( 'id' => 'issue-4', 'severity' => 'medium' ) ),
				'issue-5' => array_merge( $this->test_issue_data, array( 'id' => 'issue-5', 'severity' => 'low' ) ),
			)
		);

		$breakdown = $this->repository->get_severity_breakdown();

		$this->assertEquals( 2, $breakdown['critical'] );
		$this->assertEquals( 1, $breakdown['high'] );
		$this->assertEquals( 1, $breakdown['medium'] );
		$this->assertEquals( 1, $breakdown['low'] );
	}

	public function test_create_daily_snapshot() {
		$issues = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
		);

		$result = $this->repository->create_daily_snapshot( $issues );

		$this->assertTrue( $result );

		$today     = gmdate( 'Ymd' );
		$snapshot  = $this->repository->get_snapshot( $today );

		$this->assertIsArray( $snapshot );
		$this->assertEquals( 1, $snapshot['total_issues'] );
	}

	public function test_get_snapshot() {
		$today = gmdate( 'Ymd' );
		$this->repository->create_daily_snapshot(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
			)
		);

		$snapshot = $this->repository->get_snapshot( $today );

		$this->assertIsArray( $snapshot );
		$this->assertArrayHasKey( 'timestamp', $snapshot );
		$this->assertArrayHasKey( 'date', $snapshot );
		$this->assertArrayHasKey( 'total_issues', $snapshot );
	}

	public function test_get_non_existent_snapshot_returns_null() {
		$snapshot = $this->repository->get_snapshot( '20000101' );

		$this->assertNull( $snapshot );
	}

	public function test_get_history() {
		$issues = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
		);

		for ( $i = 0; $i < 3; $i++ ) {
			$this->repository->create_daily_snapshot( $issues );
			wp_cache_flush();
			sleep( 1 );
		}

		$history = $this->repository->get_history( 10 );

		$this->assertIsArray( $history );
		$this->assertGreaterThan( 0, count( $history ) );
	}

	public function test_snapshot_contains_severity_breakdown() {
		$issues = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1', 'severity' => 'critical' ) ),
			'issue-2' => array_merge( $this->test_issue_data, array( 'id' => 'issue-2', 'severity' => 'high' ) ),
		);

		$today = gmdate( 'Ymd' );
		$this->repository->create_daily_snapshot( $issues );

		$snapshot  = $this->repository->get_snapshot( $today );
		$breakdown = $snapshot['severity_breakdown'];

		$this->assertEquals( 1, $breakdown['critical'] );
		$this->assertEquals( 1, $breakdown['high'] );
	}

	public function test_store_empty_issues_array() {
		$this->repository->store_issue( 'issue-1', $this->test_issue_data );
		$this->assertEquals( 1, $this->repository->get_issue_count() );

		$result = $this->repository->store_issues( array() );

		$this->assertTrue( $result );
		$this->assertEquals( 0, $this->repository->get_issue_count() );
	}

	public function test_issue_gets_detected_at_timestamp() {
		$issue_data = array(
			'id'        => 'issue-1',
			'severity'  => 'high',
			'title'     => 'Test',
		);

		$this->repository->store_issue( 'issue-1', $issue_data );

		$stored = $this->repository->get_issue( 'issue-1' );

		$this->assertArrayHasKey( 'detected_at', $stored );
		$this->assertGreaterThan( 0, $stored['detected_at'] );
	}

	public function test_serialize_and_unserialize_data() {
		$original_data = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
		);

		$this->repository->store_issues( $original_data );
		$retrieved = $this->repository->get_current_issues();

		$this->assertEquals( $original_data['issue-1']['id'], $retrieved['issue-1']['id'] );
		$this->assertEquals( $original_data['issue-1']['severity'], $retrieved['issue-1']['severity'] );
	}

	public function test_large_data_compression() {
		$large_issues = array();
		for ( $i = 0; $i < 100; $i++ ) {
			$large_issues[ 'issue-' . $i ] = array_merge(
				$this->test_issue_data,
				array(
					'id'          => 'issue-' . $i,
					'description' => str_repeat( 'Test data ', 100 ),
				)
			);
		}

		$result = $this->repository->store_issues( $large_issues );

		$this->assertTrue( $result );
		$this->assertEquals( 100, $this->repository->get_issue_count() );
	}

	public function test_get_latest_snapshot() {
		$today = gmdate( 'Ymd' );
		$this->repository->create_daily_snapshot(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
			)
		);

		$latest = $this->repository->get_latest_snapshot();

		$this->assertIsArray( $latest );
		$this->assertEquals( $today, $latest['date'] );
	}

	public function test_get_latest_snapshot_returns_null_when_no_snapshots() {
		$latest = $this->repository->get_latest_snapshot();

		$this->assertNull( $latest );
	}

	public function test_snapshot_statistics() {
		$issues = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
		);

		$this->repository->create_daily_snapshot( $issues );

		$stats = $this->repository->get_snapshot_statistics();

		$this->assertIsArray( $stats );
		$this->assertArrayHasKey( 'total_snapshots', $stats );
		$this->assertArrayHasKey( 'average_issues', $stats );
		$this->assertArrayHasKey( 'trend', $stats );
	}

	public function test_snapshot_statistics_empty_returns_defaults() {
		$stats = $this->repository->get_snapshot_statistics();

		$this->assertEquals( 0, $stats['total_snapshots'] );
		$this->assertNull( $stats['date_range'] );
	}

	public function test_export_snapshot_as_json() {
		$today = gmdate( 'Ymd' );
		$this->repository->create_daily_snapshot(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
			)
		);

		$json = $this->repository->export_snapshot( $today, 'json' );

		$this->assertIsString( $json );
		$this->assertStringContainsString( 'total_issues', $json );

		$decoded = json_decode( $json, true );
		$this->assertIsArray( $decoded );
	}

	public function test_export_snapshot_as_csv() {
		$today = gmdate( 'Ymd' );
		$this->repository->create_daily_snapshot(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
			)
		);

		$csv = $this->repository->export_snapshot( $today, 'csv' );

		$this->assertIsString( $csv );
		$this->assertStringContainsString( 'Date,Total Issues', $csv );
	}

	public function test_export_non_existent_snapshot_returns_empty_string() {
		$json = $this->repository->export_snapshot( '20000101', 'json' );

		$this->assertEquals( '', $json );
	}

	public function test_cleanup_old_snapshots() {
		$this->repository->create_daily_snapshot(
			array(
				'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
			)
		);

		$deleted = $this->repository->cleanup_old_snapshots();

		$this->assertIsInt( $deleted );
	}

	public function test_multisite_context() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite not enabled' );
		}

		$result = $this->repository->store_issue( 'issue-1', $this->test_issue_data );

		$this->assertTrue( $result );
	}

	public function test_issue_data_validation() {
		$invalid_issue = array(
			'severity' => 'invalid-severity',
			'title'    => 'Test',
		);

		$result = $this->repository->store_issue( 'issue-1', $invalid_issue );

		$this->assertTrue( $result );
		$stored = $this->repository->get_issue( 'issue-1' );
		$this->assertEquals( 'medium', $stored['severity'] );
	}

	public function test_get_snapshots_between_dates() {
		$issues = array(
			'issue-1' => array_merge( $this->test_issue_data, array( 'id' => 'issue-1' ) ),
		);

		$this->repository->create_daily_snapshot( $issues );

		$today  = gmdate( 'Y-m-d' );
		$snapshots = $this->repository->get_snapshots_between( $today, $today );

		$this->assertIsArray( $snapshots );
	}

	public function test_json_serialization_of_all_data() {
		$complex_issue = array(
			'id'            => 'test-issue',
			'severity'      => 'critical',
			'title'         => 'Complex Test',
			'description'   => 'Test with special chars: "quotes" \'apostrophes\' \\backslash',
			'detected_at'   => time(),
			'data'          => array(
				'nested'     => array(
					'deep'   => 'value',
					'array'  => array( 1, 2, 3 ),
				),
			),
		);

		$this->repository->store_issue( 'test-issue', $complex_issue );

		$retrieved = $this->repository->get_issue( 'test-issue' );

		$this->assertIsArray( $retrieved );
		$this->assertEquals( 'Complex Test', $retrieved['title'] );
	}
}
