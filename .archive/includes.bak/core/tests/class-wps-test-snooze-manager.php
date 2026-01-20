<?php
/**
 * Test Suite for Snooze Manager
 *
 * @package WPShadow
 * @subpackage Guardian\Tests
 * @since 1.2.6
 */

declare( strict_types=1 );

namespace WPShadow\Tests;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Guardian\WPSHADOW_Snooze_Manager;
use WP_UnitTestCase;

/**
 * Test class for WPSHADOW_Snooze_Manager
 *
 * @since 1.2.6
 */
class WPSHADOW_Test_Snooze_Manager extends WP_UnitTestCase {

	/**
	 * Set up test environment before each test
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear options before each test
		delete_option( 'wpshadow_snoozed_issues' );
		delete_option( 'wpshadow_dismissed_issues' );
		delete_option( 'wpshadow_dismissal_history' );

		// Set current user to admin
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Test snoozing an issue
	 *
	 * @return void
	 */
	public function test_snooze_issue(): void {
		$issue_id = 'test-issue-001';

		$result = WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 24 );

		$this->assertTrue( $result );
		$this->assertTrue( WPSHADOW_Snooze_Manager::is_snoozed( $issue_id ) );
	}

	/**
	 * Test snoozing with custom duration
	 *
	 * @return void
	 */
	public function test_snooze_with_custom_duration(): void {
		$issue_id = 'test-issue-002';
		$custom_duration = 3600; // 1 hour in seconds

		$result = WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, $custom_duration );

		$this->assertTrue( $result );

		$snooze_info = WPSHADOW_Snooze_Manager::get_snooze_info( $issue_id );
		$this->assertNotNull( $snooze_info );
		$this->assertIsArray( $snooze_info );
	}

	/**
	 * Test snoozing with 'week' preset
	 *
	 * @return void
	 */
	public function test_snooze_with_week_preset(): void {
		$issue_id = 'test-issue-003';

		$result = WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 'week' );

		$this->assertTrue( $result );
		$this->assertTrue( WPSHADOW_Snooze_Manager::is_snoozed( $issue_id ) );
	}

	/**
	 * Test snoozing with 'month' preset
	 *
	 * @return void
	 */
	public function test_snooze_with_month_preset(): void {
		$issue_id = 'test-issue-004';

		$result = WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 'month' );

		$this->assertTrue( $result );
		$this->assertTrue( WPSHADOW_Snooze_Manager::is_snoozed( $issue_id ) );
	}

	/**
	 * Test dismissing an issue
	 *
	 * @return void
	 */
	public function test_dismiss_issue(): void {
		$issue_id = 'test-issue-005';

		$result = WPSHADOW_Snooze_Manager::dismiss_issue( $issue_id );

		$this->assertTrue( $result );
		$this->assertTrue( WPSHADOW_Snooze_Manager::is_dismissed( $issue_id ) );
	}

	/**
	 * Test dismissing with reason
	 *
	 * @return void
	 */
	public function test_dismiss_with_reason(): void {
		$issue_id = 'test-issue-006';
		$reason = 'Issue already fixed on production';

		$result = WPSHADOW_Snooze_Manager::dismiss_issue( $issue_id, $reason );

		$this->assertTrue( $result );

		$dismissed = WPSHADOW_Snooze_Manager::get_dismissed_issues();
		$this->assertSame( $reason, $dismissed[ $issue_id ]['reason'] );
	}

	/**
	 * Test restoring a dismissed issue
	 *
	 * @return void
	 */
	public function test_restore_issue(): void {
		$issue_id = 'test-issue-007';

		// First dismiss
		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_id );
		$this->assertTrue( WPSHADOW_Snooze_Manager::is_dismissed( $issue_id ) );

		// Then restore
		$result = WPSHADOW_Snooze_Manager::restore_issue( $issue_id );

		$this->assertTrue( $result );
		$this->assertFalse( WPSHADOW_Snooze_Manager::is_dismissed( $issue_id ) );
	}

	/**
	 * Test restore fails for non-existent dismissal
	 *
	 * @return void
	 */
	public function test_restore_non_existent_issue(): void {
		$issue_id = 'non-existent-issue';

		$result = WPSHADOW_Snooze_Manager::restore_issue( $issue_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test getting snooze info
	 *
	 * @return void
	 */
	public function test_get_snooze_info(): void {
		$issue_id = 'test-issue-008';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 48 );

		$snooze_info = WPSHADOW_Snooze_Manager::get_snooze_info( $issue_id );

		$this->assertIsArray( $snooze_info );
		$this->assertArrayHasKey( 'expiration', $snooze_info );
		$this->assertArrayHasKey( 'duration_label', $snooze_info );
		$this->assertArrayHasKey( 'snoozed_at', $snooze_info );
		$this->assertArrayHasKey( 'snoozed_by', $snooze_info );
	}

	/**
	 * Test getting snoozed issues
	 *
	 * @return void
	 */
	public function test_get_snoozed_issues(): void {
		$issue_1 = 'test-issue-009';
		$issue_2 = 'test-issue-010';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_1, 24 );
		WPSHADOW_Snooze_Manager::snooze_issue( $issue_2, 48 );

		$snoozed = WPSHADOW_Snooze_Manager::get_snoozed_issues();

		$this->assertCount( 2, $snoozed );
		$this->assertArrayHasKey( $issue_1, $snoozed );
		$this->assertArrayHasKey( $issue_2, $snoozed );
	}

	/**
	 * Test getting dismissed issues
	 *
	 * @return void
	 */
	public function test_get_dismissed_issues(): void {
		$issue_1 = 'test-issue-011';
		$issue_2 = 'test-issue-012';

		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_1 );
		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_2 );

		$dismissed = WPSHADOW_Snooze_Manager::get_dismissed_issues();

		$this->assertCount( 2, $dismissed );
		$this->assertArrayHasKey( $issue_1, $dismissed );
		$this->assertArrayHasKey( $issue_2, $dismissed );
	}

	/**
	 * Test filtering issues
	 *
	 * @return void
	 */
	public function test_filter_issues(): void {
		$issue_1 = 'test-issue-013';
		$issue_2 = 'test-issue-014';
		$issue_3 = 'test-issue-015';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_1, 24 );
		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_2 );

		$issues = array(
			array( 'id' => $issue_1, 'title' => 'Issue 1' ),
			array( 'id' => $issue_2, 'title' => 'Issue 2' ),
			array( 'id' => $issue_3, 'title' => 'Issue 3' ),
		);

		$filtered = WPSHADOW_Snooze_Manager::filter_issues( $issues );

		$this->assertCount( 1, $filtered );
		$this->assertSame( $issue_3, $filtered[2]['id'] );
	}

	/**
	 * Test snooze remaining time
	 *
	 * @return void
	 */
	public function test_snooze_remaining(): void {
		$issue_id = 'test-issue-016';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 3600 ); // 1 hour

		$remaining = WPSHADOW_Snooze_Manager::get_snooze_remaining( $issue_id );

		$this->assertGreaterThan( 0, $remaining );
		$this->assertLessThanOrEqual( 3600, $remaining );
	}

	/**
	 * Test snooze remaining for non-snoozed issue
	 *
	 * @return void
	 */
	public function test_snooze_remaining_not_snoozed(): void {
		$issue_id = 'test-issue-017';

		$remaining = WPSHADOW_Snooze_Manager::get_snooze_remaining( $issue_id );

		$this->assertSame( 0, $remaining );
	}

	/**
	 * Test snooze display text
	 *
	 * @return void
	 */
	public function test_snooze_display_text(): void {
		$issue_id = 'test-issue-018';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 3600 ); // 1 hour

		$display_text = WPSHADOW_Snooze_Manager::get_snooze_display_text( $issue_id );

		$this->assertNotEmpty( $display_text );
		$this->assertStringContainsString( 'hour', $display_text );
	}

	/**
	 * Test dismissal history is tracked
	 *
	 * @return void
	 */
	public function test_dismissal_history_tracked(): void {
		$issue_id = 'test-issue-019';

		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_id, 'Test reason' );

		$history = WPSHADOW_Snooze_Manager::get_dismissal_history();

		$this->assertCount( 1, $history );
		$this->assertSame( $issue_id, $history[0]['issue_id'] );
		$this->assertSame( 'permanent_dismiss', $history[0]['action'] );
	}

	/**
	 * Test history limit
	 *
	 * @return void
	 */
	public function test_history_limit(): void {
		// Create 15 dismissals
		for ( $i = 0; $i < 15; $i++ ) {
			WPSHADOW_Snooze_Manager::dismiss_issue( "issue-$i" );
		}

		$history = WPSHADOW_Snooze_Manager::get_dismissal_history( 10 );

		$this->assertCount( 10, $history );
	}

	/**
	 * Test snooze expiration check
	 *
	 * @return void
	 */
	public function test_snooze_expires(): void {
		$issue_id = 'test-issue-020';

		// Snooze for 1 second (will expire quickly for testing)
		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 1 );

		// Should be snoozed initially
		$this->assertTrue( WPSHADOW_Snooze_Manager::is_snoozed( $issue_id ) );

		// Wait for snooze to expire
		sleep( 2 );

		// Should no longer be snoozed
		$this->assertFalse( WPSHADOW_Snooze_Manager::is_snoozed( $issue_id ) );
	}

	/**
	 * Test cleanup expired snoozes cron
	 *
	 * @return void
	 */
	public function test_cleanup_expired_snoozes(): void {
		$issue_1 = 'test-issue-021';
		$issue_2 = 'test-issue-022';

		// Snooze for 1 second (will expire)
		WPSHADOW_Snooze_Manager::snooze_issue( $issue_1, 1 );
		// Snooze for 1 hour (won't expire)
		WPSHADOW_Snooze_Manager::snooze_issue( $issue_2, 3600 );

		// Wait for first to expire
		sleep( 2 );

		// Run cleanup
		WPSHADOW_Snooze_Manager::cleanup_expired_snoozes();

		$snoozed = WPSHADOW_Snooze_Manager::get_snoozed_issues();

		// First should be removed, second should remain
		$this->assertArrayNotHasKey( $issue_1, $snoozed );
		$this->assertArrayHasKey( $issue_2, $snoozed );
	}

	/**
	 * Test multiple snoozes on same issue (replaces previous)
	 *
	 * @return void
	 */
	public function test_snooze_replace(): void {
		$issue_id = 'test-issue-023';

		// First snooze
		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 24 );
		$snooze_info_1 = WPSHADOW_Snooze_Manager::get_snooze_info( $issue_id );

		// Sleep briefly
		sleep( 1 );

		// Second snooze
		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 48 );
		$snooze_info_2 = WPSHADOW_Snooze_Manager::get_snooze_info( $issue_id );

		// Should have different expiration times
		$this->assertNotSame(
			$snooze_info_1['expiration'],
			$snooze_info_2['expiration']
		);
	}

	/**
	 * Test clear all snoozes and dismissals
	 *
	 * @return void
	 */
	public function test_clear_all(): void {
		// Create multiple snoozes and dismissals
		WPSHADOW_Snooze_Manager::snooze_issue( 'issue-1', 24 );
		WPSHADOW_Snooze_Manager::snooze_issue( 'issue-2', 48 );
		WPSHADOW_Snooze_Manager::dismiss_issue( 'issue-3' );

		// Verify they exist
		$this->assertNotEmpty( WPSHADOW_Snooze_Manager::get_snoozed_issues() );
		$this->assertNotEmpty( WPSHADOW_Snooze_Manager::get_dismissed_issues() );

		// Clear all
		$result = WPSHADOW_Snooze_Manager::clear_all();

		$this->assertTrue( $result );
		$this->assertEmpty( WPSHADOW_Snooze_Manager::get_snoozed_issues() );
		$this->assertEmpty( WPSHADOW_Snooze_Manager::get_dismissed_issues() );
	}

	/**
	 * Test invalid issue ID
	 *
	 * @return void
	 */
	public function test_snooze_invalid_issue_id(): void {
		$result = WPSHADOW_Snooze_Manager::snooze_issue( '', 24 );

		$this->assertFalse( $result );
	}

	/**
	 * Test invalid duration
	 *
	 * @return void
	 */
	public function test_snooze_invalid_duration(): void {
		$result = WPSHADOW_Snooze_Manager::snooze_issue( 'test-issue', 'invalid-duration' );

		$this->assertFalse( $result );
	}

	/**
	 * Test snooze preset durations
	 *
	 * @return void
	 */
	public function test_snooze_all_presets(): void {
		$presets = array( 24, 48, 72, 'week', 'month' );

		foreach ( $presets as $preset ) {
			$issue_id = "issue-preset-$preset";

			$result = WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, $preset );

			$this->assertTrue( $result, "Failed to snooze with preset: $preset" );
		}
	}

	/**
	 * Test data persistence across calls
	 *
	 * @return void
	 */
	public function test_data_persistence(): void {
		$issue_1 = 'test-issue-024';
		$issue_2 = 'test-issue-025';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_1, 24 );
		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_2 );

		// Create new instance and check data is persisted
		$snoozed = WPSHADOW_Snooze_Manager::get_snoozed_issues();
		$dismissed = WPSHADOW_Snooze_Manager::get_dismissed_issues();

		$this->assertArrayHasKey( $issue_1, $snoozed );
		$this->assertArrayHasKey( $issue_2, $dismissed );
	}

	/**
	 * Test snooze info includes reason
	 *
	 * @return void
	 */
	public function test_snooze_includes_reason(): void {
		$issue_id = 'test-issue-026';
		$reason = 'Waiting for update';

		WPSHADOW_Snooze_Manager::snooze_issue( $issue_id, 24, $reason );

		$snooze_info = WPSHADOW_Snooze_Manager::get_snooze_info( $issue_id );

		$this->assertArrayHasKey( 'reason', $snooze_info );
		$this->assertSame( $reason, $snooze_info['reason'] );
	}

	/**
	 * Test dismissed issue info includes timestamp
	 *
	 * @return void
	 */
	public function test_dismissed_includes_timestamp(): void {
		$issue_id = 'test-issue-027';

		$before_time = time();
		WPSHADOW_Snooze_Manager::dismiss_issue( $issue_id );
		$after_time = time();

		$dismissed = WPSHADOW_Snooze_Manager::get_dismissed_issues();

		$this->assertArrayHasKey( 'dismissed_at', $dismissed[ $issue_id ] );
		$this->assertGreaterThanOrEqual( $before_time, $dismissed[ $issue_id ]['dismissed_at'] );
		$this->assertLessThanOrEqual( $after_time, $dismissed[ $issue_id ]['dismissed_at'] );
	}
}
