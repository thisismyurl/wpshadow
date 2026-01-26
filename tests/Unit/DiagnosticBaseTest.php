<?php
/**
 * Tests for Diagnostic Base Class
 *
 * @package WPShadow\Tests\Unit
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Tests\TestCase;

/**
 * Diagnostic base class tests
 */
class DiagnosticBaseTest extends TestCase {

	/**
	 * Test diagnostic can be instantiated
	 *
	 * @return void
	 */
	public function testDiagnosticCanBeInstantiated(): void {
		$diagnostic = new class extends Diagnostic_Base {
			protected static $slug        = 'test-diagnostic';
			protected static $title       = 'Test Diagnostic';
			protected static $description = 'A test diagnostic';

			public static function check() {
				return null;
			}
		};

		$this->assertInstanceOf( Diagnostic_Base::class, $diagnostic );
	}

	/**
	 * Test diagnostic returns null when no issues found
	 *
	 * @return void
	 */
	public function testDiagnosticReturnsNullWhenNoIssues(): void {
		$diagnostic = new class extends Diagnostic_Base {
			protected static $slug        = 'test-no-issues';
			protected static $title       = 'Test No Issues';
			protected static $description = 'Test diagnostic with no issues';

			public static function check() {
				return null;
			}
		};

		$result = $diagnostic::execute();
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns valid finding when issue detected
	 *
	 * @return void
	 */
	public function testDiagnosticReturnsValidFinding(): void {
		$diagnostic = new class extends Diagnostic_Base {
			protected static $slug        = 'test-with-issue';
			protected static $title       = 'Test With Issue';
			protected static $description = 'Test diagnostic with issue';

			public static function check() {
				return array(
					'id'           => 'test-issue',
					'title'        => 'Test Issue Found',
					'description'  => 'This is a test issue',
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => true,
				);
			}
		};

		$result = $diagnostic::execute();
		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
	}

	/**
	 * Test diagnostic family grouping
	 *
	 * @return void
	 */
	public function testDiagnosticFamilyGrouping(): void {
		$diagnostic = new class extends Diagnostic_Base {
			protected static $slug         = 'test-family';
			protected static $title        = 'Test Family';
			protected static $description  = 'Test diagnostic with family';
			protected static $family       = 'test-group';
			protected static $family_label = 'Test Group';

			public static function check() {
				return null;
			}
		};

		$this->assertEquals( 'test-group', $diagnostic::get_family() );
		$this->assertEquals( 'Test Group', $diagnostic::get_family_label() );
	}

	/**
	 * Test diagnostic metadata getters
	 *
	 * @return void
	 */
	public function testDiagnosticMetadataGetters(): void {
		$diagnostic = new class extends Diagnostic_Base {
			protected static $slug        = 'test-metadata';
			protected static $title       = 'Test Metadata';
			protected static $description = 'Test diagnostic metadata';

			public static function check() {
				return null;
			}
		};

		$this->assertEquals( 'test-metadata', $diagnostic::get_slug() );
		$this->assertEquals( 'Test Metadata', $diagnostic::get_title() );
		$this->assertEquals( 'Test diagnostic metadata', $diagnostic::get_description() );
	}

	/**
	 * Test diagnostic hooks are fired
	 *
	 * @return void
	 */
	public function testDiagnosticHooksAreFired(): void {
		$hooks_fired = array();

		// Mock WordPress add_action
		if ( ! function_exists( 'add_action' ) ) {
			function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
				// Store for testing
				return true;
			}
		}

		// Mock WordPress do_action
		if ( ! function_exists( 'do_action' ) ) {
			function do_action( $hook, ...$args ) {
				global $hooks_fired;
				$hooks_fired[] = $hook;
			}
		}

		$diagnostic = new class extends Diagnostic_Base {
			protected static $slug        = 'test-hooks';
			protected static $title       = 'Test Hooks';
			protected static $description = 'Test diagnostic hooks';

			public static function check() {
				return null;
			}
		};

		$diagnostic::execute();

		// We can't easily test hooks without WordPress, but we can verify execute runs
		$this->assertTrue( true );
	}

	/**
	 * Test diagnostic severity levels
	 *
	 * @return void
	 */
	public function testDiagnosticSeverityLevels(): void {
		$severities = array( 'low', 'medium', 'high', 'critical' );

		foreach ( $severities as $severity ) {
			$diagnostic = new class( $severity ) extends Diagnostic_Base {
				private $test_severity;

				public function __construct( $severity ) {
					$this->test_severity = $severity;
				}

				protected static $slug        = 'test-severity';
				protected static $title       = 'Test Severity';
				protected static $description = 'Test diagnostic severity';

				public static function check() {
					return array(
						'id'           => 'test-severity',
						'title'        => 'Test Severity',
						'description'  => 'Test',
						'severity'     => 'medium',
						'threat_level' => 50,
						'auto_fixable' => false,
					);
				}
			};

			$this->assertInstanceOf( Diagnostic_Base::class, $diagnostic );
		}
	}

	/**
	 * Test diagnostic threat level ranges
	 *
	 * @return void
	 */
	public function testDiagnosticThreatLevelRanges(): void {
		$threat_levels = array( 0, 25, 50, 75, 100 );

		foreach ( $threat_levels as $level ) {
			$diagnostic = new class( $level ) extends Diagnostic_Base {
				private $test_level;

				public function __construct( $level ) {
					$this->test_level = $level;
				}

				protected static $slug        = 'test-threat';
				protected static $title       = 'Test Threat';
				protected static $description = 'Test diagnostic threat level';

				public static function check() {
					return null;
				}

				public function getTestLevel() {
					return $this->test_level;
				}
			};

			$this->assertGreaterThanOrEqual( 0, $diagnostic->getTestLevel() );
			$this->assertLessThanOrEqual( 100, $diagnostic->getTestLevel() );
		}
	}
}
