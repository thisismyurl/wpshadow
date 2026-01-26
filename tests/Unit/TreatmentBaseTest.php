<?php
/**
 * Tests for Treatment Base Class
 *
 * @package WPShadow\Tests\Unit
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Core\Treatment_Base;
use WPShadow\Tests\TestCase;

/**
 * Treatment base class tests
 */
class TreatmentBaseTest extends TestCase {

	/**
	 * Test treatment can be instantiated
	 *
	 * @return void
	 */
	public function testTreatmentCanBeInstantiated(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-finding';
			}

			public static function apply() {
				return array(
					'success' => true,
					'message' => 'Treatment applied successfully',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone successfully',
				);
			}
		};

		$this->assertInstanceOf( Treatment_Base::class, $treatment );
	}

	/**
	 * Test treatment returns valid result
	 *
	 * @return void
	 */
	public function testTreatmentReturnsValidResult(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-finding';
			}

			public static function apply() {
				return array(
					'success' => true,
					'message' => 'Treatment applied successfully',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		$result = $treatment::execute();
		$this->assertValidTreatmentResult( $result );
	}

	/**
	 * Test treatment success result
	 *
	 * @return void
	 */
	public function testTreatmentSuccessResult(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-success';
			}

			public static function apply() {
				return array(
					'success' => true,
					'message' => 'Successfully applied treatment',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		$result = $treatment::execute();
		$this->assertTrue( $result['success'] );
		$this->assertEquals( 'Successfully applied treatment', $result['message'] );
	}

	/**
	 * Test treatment failure result
	 *
	 * @return void
	 */
	public function testTreatmentFailureResult(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-failure';
			}

			public static function apply() {
				return array(
					'success' => false,
					'message' => 'Failed to apply treatment',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		$result = $treatment::execute();
		$this->assertFalse( $result['success'] );
		$this->assertEquals( 'Failed to apply treatment', $result['message'] );
	}

	/**
	 * Test treatment dry run mode
	 *
	 * @return void
	 */
	public function testTreatmentDryRun(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-dry-run';
			}

			public static function apply() {
				return array(
					'success' => true,
					'message' => 'Treatment applied',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		// Dry run should return result without applying changes
		$result = $treatment::execute( true );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
	}

	/**
	 * Test treatment with backup
	 *
	 * @return void
	 */
	public function testTreatmentWithBackup(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-backup';
			}

			public static function apply() {
				// Simulate backup creation
				return array(
					'success' => true,
					'message' => 'Treatment applied with backup',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		$result = $treatment::execute();
		$this->assertTrue( $result['success'] );
	}

	/**
	 * Test treatment capability check
	 *
	 * @return void
	 */
	public function testTreatmentCapabilityCheck(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-capability';
			}

			public static function apply() {
				return array(
					'success' => true,
					'message' => 'Treatment applied',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}

			public static function can_apply() {
				return false; // Simulate insufficient permissions
			}
		};

		$can_apply = $treatment::can_apply();
		$this->assertIsBool( $can_apply );
	}

	/**
	 * Test treatment with additional data
	 *
	 * @return void
	 */
	public function testTreatmentWithAdditionalData(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-data';
			}

			public static function apply() {
				return array(
					'success' => true,
					'message' => 'Treatment applied',
					'data'    => array(
						'before' => 'old_value',
						'after'  => 'new_value',
					),
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		$result = $treatment::execute();
		$this->assertArrayHasKey( 'data', $result );
		$this->assertIsArray( $result['data'] );
		$this->assertEquals( 'old_value', $result['data']['before'] );
		$this->assertEquals( 'new_value', $result['data']['after'] );
	}

	/**
	 * Test treatment error handling
	 *
	 * @return void
	 */
	public function testTreatmentErrorHandling(): void {
		$treatment = new class extends Treatment_Base {
			public static function get_finding_id() {
				return 'test-error';
			}

			public static function apply() {
				// Simulate error
				return array(
					'success' => false,
					'message' => 'An error occurred',
					'error'   => 'Detailed error message',
				);
			}

			public static function undo() {
				return array(
					'success' => true,
					'message' => 'Treatment undone',
				);
			}
		};

		$result = $treatment::execute();
		$this->assertFalse( $result['success'] );
		$this->assertArrayHasKey( 'message', $result );
	}
}
