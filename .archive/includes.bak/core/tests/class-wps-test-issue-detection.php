<?php

declare(strict_types=1);

namespace WPShadow\Tests;

use WPShadow\CoreSupport\WPSHADOW_Issue_Detection;
use WPShadow\CoreSupport\WPSHADOW_Issue_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Test_Issue_Detection extends \PHPUnit\Framework\TestCase {

	private $mock_detector;

	protected function setUp(): void {
		$this->mock_detector = new class extends WPSHADOW_Issue_Detection {
			public function run(): bool {
				return true;
			}

			public function get_issue_count(): int {
				return count( $this->detected_issues );
			}
		};

		$this->mock_detector->__construct( 'test_detector', 'Test Detector', 'A test detector' );
	}

	public function test_detector_initialization(): void {
		$this->assertEquals( 'test_detector', $this->mock_detector->get_detector_id() );
		$this->assertEquals( 'Test Detector', $this->mock_detector->get_detector_name() );
		$this->assertEquals( 'A test detector', $this->mock_detector->get_detector_description() );
	}

	public function test_add_issue_with_default_parameters(): void {
		$result = $this->mock_detector->add_issue(
			'issue_1',
			'Test Issue',
			'This is a test issue'
		);

		$this->assertTrue( $result );
		$this->assertEquals( 1, $this->mock_detector->get_issue_count() );
	}

	public function test_add_issue_with_all_parameters(): void {
		$result = $this->mock_detector->add_issue(
			'issue_1',
			'Test Issue',
			'This is a test issue',
			WPSHADOW_Issue_Detection::SEVERITY_HIGH,
			0.85,
			array( 'key' => 'value' ),
			true,
			array( 'fix_key' => 'fix_value' )
		);

		$this->assertTrue( $result );

		$issue = $this->mock_detector->get_issue_by_id( 'issue_1' );
		$this->assertNotNull( $issue );
		$this->assertEquals( 'Test Issue', $issue['title'] );
		$this->assertEquals( WPSHADOW_Issue_Detection::SEVERITY_HIGH, $issue['severity'] );
		$this->assertEquals( 0.85, $issue['confidence'] );
		$this->assertTrue( $issue['auto_fixable'] );
	}

	public function test_add_issue_invalid_severity(): void {
		$result = $this->mock_detector->add_issue(
			'issue_1',
			'Test Issue',
			'This is a test issue',
			'invalid_severity'
		);

		$this->assertFalse( $result );
		$this->assertEquals( 0, $this->mock_detector->get_issue_count() );
	}

	public function test_add_issue_confidence_out_of_range(): void {
		$result = $this->mock_detector->add_issue(
			'issue_1',
			'Test Issue',
			'This is a test issue',
			WPSHADOW_Issue_Detection::SEVERITY_MEDIUM,
			1.5
		);

		$this->assertTrue( $result );
		$issue = $this->mock_detector->get_issue_by_id( 'issue_1' );
		$this->assertEquals( 0.5, $issue['confidence'] );
	}

	public function test_add_issue_with_resolution(): void {
		$result = $this->mock_detector->add_issue_with_resolution(
			'issue_1',
			'Test Issue',
			'This is a test issue',
			'Here is the resolution'
		);

		$this->assertTrue( $result );

		$issue = $this->mock_detector->get_issue_by_id( 'issue_1' );
		$this->assertEquals( 'Here is the resolution', $issue['resolution'] );
	}

	public function test_get_critical_issues(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Critical', 'Critical issue', WPSHADOW_Issue_Detection::SEVERITY_CRITICAL );
		$this->mock_detector->add_issue( 'issue_2', 'High', 'High issue', WPSHADOW_Issue_Detection::SEVERITY_HIGH );
		$this->mock_detector->add_issue( 'issue_3', 'Critical2', 'Another critical', WPSHADOW_Issue_Detection::SEVERITY_CRITICAL );

		$critical = $this->mock_detector->get_critical_issues();
		$this->assertEquals( 2, count( $critical ) );
	}

	public function test_get_auto_fixable_issues(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Issue 1', 'Fixable', auto_fixable: true );
		$this->mock_detector->add_issue( 'issue_2', 'Issue 2', 'Not fixable', auto_fixable: false );
		$this->mock_detector->add_issue( 'issue_3', 'Issue 3', 'Fixable', auto_fixable: true );

		$fixable = $this->mock_detector->get_auto_fixable_issues();
		$this->assertEquals( 2, count( $fixable ) );
	}

	public function test_get_high_confidence_issues(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Issue 1', 'High confidence', confidence: 0.9 );
		$this->mock_detector->add_issue( 'issue_2', 'Issue 2', 'Low confidence', confidence: 0.3 );
		$this->mock_detector->add_issue( 'issue_3', 'Issue 3', 'High confidence', confidence: 0.8 );

		$high_conf = $this->mock_detector->get_high_confidence_issues( 0.75 );
		$this->assertEquals( 2, count( $high_conf ) );
	}

	public function test_has_critical_issues(): void {
		$this->assertFalse( $this->mock_detector->has_critical_issues() );

		$this->mock_detector->add_issue( 'issue_1', 'Issue', 'Test', WPSHADOW_Issue_Detection::SEVERITY_CRITICAL );

		$this->assertTrue( $this->mock_detector->has_critical_issues() );
	}

	public function test_get_severity_distribution(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Issue 1', 'Test', WPSHADOW_Issue_Detection::SEVERITY_CRITICAL );
		$this->mock_detector->add_issue( 'issue_2', 'Issue 2', 'Test', WPSHADOW_Issue_Detection::SEVERITY_HIGH );
		$this->mock_detector->add_issue( 'issue_3', 'Issue 3', 'Test', WPSHADOW_Issue_Detection::SEVERITY_HIGH );
		$this->mock_detector->add_issue( 'issue_4', 'Issue 4', 'Test', WPSHADOW_Issue_Detection::SEVERITY_MEDIUM );

		$distribution = $this->mock_detector->get_severity_distribution();
		$this->assertEquals( 1, $distribution[ WPSHADOW_Issue_Detection::SEVERITY_CRITICAL ] );
		$this->assertEquals( 2, $distribution[ WPSHADOW_Issue_Detection::SEVERITY_HIGH ] );
		$this->assertEquals( 1, $distribution[ WPSHADOW_Issue_Detection::SEVERITY_MEDIUM ] );
		$this->assertEquals( 0, $distribution[ WPSHADOW_Issue_Detection::SEVERITY_LOW ] );
	}

	public function test_get_average_confidence(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Issue 1', 'Test', confidence: 0.5 );
		$this->mock_detector->add_issue( 'issue_2', 'Issue 2', 'Test', confidence: 0.7 );
		$this->mock_detector->add_issue( 'issue_3', 'Issue 3', 'Test', confidence: 0.9 );

		$avg = $this->mock_detector->get_average_confidence();
		$this->assertAlmostEquals( 0.7, $avg, 0.01 );
	}

	public function test_clear_issues(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Issue 1', 'Test' );
		$this->mock_detector->add_issue( 'issue_2', 'Issue 2', 'Test' );

		$this->assertEquals( 2, $this->mock_detector->get_issue_count() );

		$this->mock_detector->clear_issues();
		$this->assertEquals( 0, $this->mock_detector->get_issue_count() );
	}

	public function test_get_issue_by_id(): void {
		$this->mock_detector->add_issue( 'issue_1', 'Test Issue', 'A test' );

		$issue = $this->mock_detector->get_issue_by_id( 'issue_1' );
		$this->assertNotNull( $issue );
		$this->assertEquals( 'Test Issue', $issue['title'] );

		$nonexistent = $this->mock_detector->get_issue_by_id( 'nonexistent' );
		$this->assertNull( $nonexistent );
	}

	public function test_timestamp_is_set(): void {
		$before = time();
		$this->mock_detector->add_issue( 'issue_1', 'Test', 'Test' );
		$after = time();

		$issue = $this->mock_detector->get_issue_by_id( 'issue_1' );
		$this->assertGreaterThanOrEqual( $before, $issue['timestamp'] );
		$this->assertLessThanOrEqual( $after, $issue['timestamp'] );
	}
}

class WPSHADOW_Test_Issue_Registry extends \PHPUnit\Framework\TestCase {

	private $registry;

	private $detector1;

	private $detector2;

	protected function setUp(): void {
		$this->registry = WPSHADOW_Issue_Registry::get_instance();
		$this->registry->clear_all_issues();

		$this->detector1 = new class extends WPSHADOW_Issue_Detection {
			public function run(): bool {
				$this->add_issue( 'issue_1', 'Issue 1', 'Test' );
				return true;
			}

			public function get_issue_count(): int {
				return count( $this->detected_issues );
			}
		};

		$this->detector1->__construct( 'detector1', 'Detector 1' );

		$this->detector2 = new class extends WPSHADOW_Issue_Detection {
			public function run(): bool {
				$this->add_issue( 'issue_2', 'Issue 2', 'Test', WPSHADOW_Issue_Detection::SEVERITY_HIGH );
				return true;
			}

			public function get_issue_count(): int {
				return count( $this->detected_issues );
			}
		};

		$this->detector2->__construct( 'detector2', 'Detector 2' );
	}

	public function test_singleton_instance(): void {
		$instance1 = WPSHADOW_Issue_Registry::get_instance();
		$instance2 = WPSHADOW_Issue_Registry::get_instance();

		$this->assertSame( $instance1, $instance2 );
	}

	public function test_register_detector(): void {
		$result = $this->registry->register_detector( $this->detector1 );
		$this->assertTrue( $result );
	}

	public function test_register_duplicate_detector(): void {
		$this->registry->register_detector( $this->detector1 );
		$result = $this->registry->register_detector( $this->detector1 );

		$this->assertFalse( $result );
	}

	public function test_get_detector(): void {
		$this->registry->register_detector( $this->detector1 );

		$detector = $this->registry->get_detector( 'detector1' );
		$this->assertNotNull( $detector );
		$this->assertEquals( 'Detector 1', $detector->get_detector_name() );
	}

	public function test_detector_exists(): void {
		$this->registry->register_detector( $this->detector1 );

		$this->assertTrue( $this->registry->detector_exists( 'detector1' ) );
		$this->assertFalse( $this->registry->detector_exists( 'nonexistent' ) );
	}

	public function test_get_all_detectors(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );

		$detectors = $this->registry->get_all_detectors();
		$this->assertEquals( 2, count( $detectors ) );
	}

	public function test_run_detector(): void {
		$this->registry->register_detector( $this->detector1 );
		$result = $this->registry->run_detector( 'detector1' );

		$this->assertTrue( $result );
		$this->assertEquals( 1, $this->registry->get_issue_count() );
	}

	public function test_run_nonexistent_detector(): void {
		$result = $this->registry->run_detector( 'nonexistent' );
		$this->assertFalse( $result );
	}

	public function test_run_all_detectors(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );

		$results = $this->registry->run_all_detectors();

		$this->assertEquals( 2, count( $results ) );
		$this->assertTrue( $results['detector1'] );
		$this->assertTrue( $results['detector2'] );
		$this->assertEquals( 2, $this->registry->get_issue_count() );
	}

	public function test_get_issues_by_detector(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$issues = $this->registry->get_issues_by_detector( 'detector1' );
		$this->assertEquals( 1, count( $issues ) );
	}

	public function test_get_critical_issues(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$critical = $this->registry->get_critical_issues();
		$this->assertEquals( 1, count( $critical ) );
	}

	public function test_get_high_severity_issues(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$high = $this->registry->get_high_severity_issues();
		$this->assertEquals( 1, count( $high ) );
	}

	public function test_has_critical_issues(): void {
		$this->assertFalse( $this->registry->has_critical_issues() );

		$this->registry->register_detector( $this->detector1 );
		$this->registry->run_detector( 'detector1' );

		$this->assertFalse( $this->registry->has_critical_issues() );

		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_detector( 'detector2' );

		$this->assertFalse( $this->registry->has_critical_issues() );
	}

	public function test_has_issues(): void {
		$this->assertFalse( $this->registry->has_issues() );

		$this->registry->register_detector( $this->detector1 );
		$this->registry->run_detector( 'detector1' );

		$this->assertTrue( $this->registry->has_issues() );
	}

	public function test_get_issue_count(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$this->assertEquals( 2, $this->registry->get_issue_count() );
	}

	public function test_get_issue_count_by_detector(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->run_detector( 'detector1' );

		$this->assertEquals( 1, $this->registry->get_issue_count_by_detector( 'detector1' ) );
		$this->assertEquals( 0, $this->registry->get_issue_count_by_detector( 'nonexistent' ) );
	}

	public function test_clear_all_issues(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->run_detector( 'detector1' );

		$this->assertEquals( 1, $this->registry->get_issue_count() );

		$this->registry->clear_all_issues();
		$this->assertEquals( 0, $this->registry->get_issue_count() );
	}

	public function test_clear_issues_by_detector(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$this->assertEquals( 2, $this->registry->get_issue_count() );

		$this->registry->clear_issues_by_detector( 'detector1' );

		$this->assertEquals( 1, $this->registry->get_issue_count() );
	}

	public function test_get_severity_distribution(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$distribution = $this->registry->get_severity_distribution();

		$this->assertEquals( 1, $distribution[ WPSHADOW_Issue_Detection::SEVERITY_MEDIUM ] );
		$this->assertEquals( 1, $distribution[ WPSHADOW_Issue_Detection::SEVERITY_HIGH ] );
	}

	public function test_get_statistics(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->register_detector( $this->detector2 );
		$this->registry->run_all_detectors();

		$stats = $this->registry->get_statistics();

		$this->assertEquals( 2, $stats['total_issues'] );
		$this->assertEquals( 2, $stats['total_detectors'] );
		$this->assertFalse( $stats['has_critical_issues'] );
	}

	public function test_record_detection_history(): void {
		$this->registry->register_detector( $this->detector1 );
		$this->registry->run_detector( 'detector1' );

		$history = $this->registry->get_detection_history( 'detector1' );
		$this->assertGreaterThan( 0, count( $history ) );
		$this->assertTrue( $history[0]['success'] );
	}
}
