<?php
/**
 * Diagnostic Metadata & Environment Tests
 *
 * Validates the Phase 7-9 backend systems: Diagnostic_Metadata correctness,
 * Environment_Detector logic, Readiness_Registry environment-awareness, and
 * Diagnostic_Registry confidence/core-set filtering.
 *
 * @package WPShadow
 * @since   0.7055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Metadata;
use WPShadow\Core\Environment_Detector;
use WPShadow\Core\Readiness_Registry;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test suite for Diagnostic_Metadata, Environment_Detector, and the
 * confidence/core-set features added to Diagnostic_Registry.
 */
class Diagnostic_Metadata_Test extends Diagnostic_Base {

	protected static $slug  = 'diagnostic-metadata-test';
	protected static $title = 'Diagnostic Metadata & Environment Tests';

	public static function get_title(): string {
		return __( 'Diagnostic Metadata & Environment Tests', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Validates Core 50 membership, confidence tiers, environment detection, and registry filtering (Phase 7-9 backend).', 'wpshadow' );
	}

	public static function get_family(): string {
		return 'governance';
	}

	// -------------------------------------------------------------------------
	// Entry point
	// -------------------------------------------------------------------------

	/**
	 * Run all sub-tests.
	 *
	 * @return array<string, mixed>
	 */
	public static function run(): array {
		$findings = array();

		$tests = array(
			'test_diagnostic_metadata_class_exists',
			'test_core_50_count',
			'test_confidence_tiers_valid',
			'test_core_slugs_have_high_or_standard_confidence',
			'test_auto_fix_safe_only_for_high_or_standard',
			'test_environment_detector_returns_known_value',
			'test_environment_policy_structure',
			'test_readiness_registry_governance_context',
			'test_diagnostic_registry_core_method',
			'test_diagnostic_registry_confidence_filter',
		);

		foreach ( $tests as $test ) {
			$result = self::$test();
			if ( ! $result['passed'] ) {
				$findings[] = $result['finding'];
			}
		}

		if ( empty( $findings ) ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'All Diagnostic_Metadata and environment backend tests passed.', 'wpshadow' ),
			);
		}

		return array(
			'status'   => 'fail',
			'message'  => sprintf(
				/* translators: %d: number of failed sub-tests */
				_n( '%d governance backend test failed.', '%d governance backend tests failed.', count( $findings ), 'wpshadow' ),
				count( $findings )
			),
			'findings' => $findings,
		);
	}

	// -------------------------------------------------------------------------
	// Sub-tests: Diagnostic_Metadata
	// -------------------------------------------------------------------------

	/**
	 * Test: Diagnostic_Metadata class exists and is loadable.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_diagnostic_metadata_class_exists(): array {
		if ( ! class_exists( Diagnostic_Metadata::class ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Diagnostic_Metadata class not found. Check bootstrap autoloader registration.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		$sample = Diagnostic_Metadata::get( 'auth-keys-and-salts-set' );
		if ( empty( $sample ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Diagnostic_Metadata::get() returned empty array for a known Core 50 slug.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: Exactly 50 Core diagnostics are registered.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_core_50_count(): array {
		if ( ! class_exists( Diagnostic_Metadata::class ) ) {
			return array( 'passed' => true ); // Covered by previous test.
		}

		$core_slugs = Diagnostic_Metadata::get_core_slugs();
		$count      = count( $core_slugs );

		if ( $count !== 50 ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %d: actual count */
						__( 'Expected 50 Core diagnostics; found %d. Review Diagnostic_Metadata built-in registry.', 'wpshadow' ),
						$count
					),
					'severity' => 'warning',
					'context'  => array(
						'core_slug_count' => $count,
					),
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: All confidence values in Diagnostic_Metadata are one of the three
	 *       allowed tiers.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_confidence_tiers_valid(): array {
		if ( ! class_exists( Diagnostic_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$allowed  = array( 'high', 'standard', 'low' );
		$invalid  = array();
		$all      = Diagnostic_Metadata::get_all();

		foreach ( $all as $slug => $meta ) {
			$tier = $meta['confidence'] ?? null;
			if ( null !== $tier && ! in_array( $tier, $allowed, true ) ) {
				$invalid[] = $slug . ' (' . esc_html( (string) $tier ) . ')';
			}
		}

		if ( ! empty( $invalid ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: comma-separated list of bad entries */
						__( 'Invalid confidence tier values: %s', 'wpshadow' ),
						implode( ', ', $invalid )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: Every Core 50 diagnostic has high or standard confidence (never
	 *       low — low-confidence items should not be in the trusted core).
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_core_slugs_have_high_or_standard_confidence(): array {
		if ( ! class_exists( Diagnostic_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$core_slugs = Diagnostic_Metadata::get_core_slugs();
		$bad        = array();

		foreach ( $core_slugs as $slug ) {
			$meta       = Diagnostic_Metadata::get( $slug );
			$confidence = $meta['confidence'] ?? 'standard';
			if ( $confidence === 'low' ) {
				$bad[] = $slug;
			}
		}

		if ( ! empty( $bad ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s slugs */
						__( 'Core 50 diagnostics must not have low confidence: %s', 'wpshadow' ),
						implode( ', ', $bad )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: auto_fix_safe is only set to true on high or standard confidence
	 *       items — never on low-confidence diagnostics.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_auto_fix_safe_only_for_high_or_standard(): array {
		if ( ! class_exists( Diagnostic_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$bad = array();
		$all = Diagnostic_Metadata::get_all();

		foreach ( $all as $slug => $meta ) {
			$auto_fix   = ! empty( $meta['auto_fix_safe'] );
			$confidence = $meta['confidence'] ?? 'standard';
			if ( $auto_fix && $confidence === 'low' ) {
				$bad[] = $slug;
			}
		}

		if ( ! empty( $bad ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s slugs */
						__( 'auto_fix_safe must not be true for low-confidence diagnostics: %s', 'wpshadow' ),
						implode( ', ', $bad )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	// -------------------------------------------------------------------------
	// Sub-tests: Environment_Detector
	// -------------------------------------------------------------------------

	/**
	 * Test: Environment_Detector returns a known value.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_environment_detector_returns_known_value(): array {
		if ( ! class_exists( Environment_Detector::class ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Environment_Detector class not found. Check bootstrap autoloader registration.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		$env = Environment_Detector::get_environment();

		$known = array(
			Environment_Detector::ENV_PRODUCTION,
			Environment_Detector::ENV_STAGING,
			Environment_Detector::ENV_DEVELOPMENT,
			Environment_Detector::ENV_LOCAL,
		);

		if ( ! in_array( $env, $known, true ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: unexpected value */
						__( 'Environment_Detector returned unexpected value: "%s"', 'wpshadow' ),
						esc_html( $env )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: Environment_Detector::get_policy() structure is complete.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_environment_policy_structure(): array {
		if ( ! class_exists( Environment_Detector::class ) ) {
			return array( 'passed' => true ); // Covered by previous test.
		}

		$policy       = Environment_Detector::get_policy();
		$required     = array( 'readiness_states', 'confidence_min', 'auto_fix', 'include_beta', 'include_planned', 'schedule' );
		$missing_keys = array();

		foreach ( $required as $key ) {
			if ( ! array_key_exists( $key, $policy ) ) {
				$missing_keys[] = $key;
			}
		}

		if ( ! empty( $missing_keys ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: missing keys */
						__( 'Environment policy missing required keys: %s', 'wpshadow' ),
						implode( ', ', $missing_keys )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	// -------------------------------------------------------------------------
	// Sub-tests: Readiness_Registry governance context
	// -------------------------------------------------------------------------

	/**
	 * Test: Readiness_Registry::get_governance_context() returns a valid payload.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_readiness_registry_governance_context(): array {
		$context = Readiness_Registry::get_governance_context();

		$required = array( 'environment', 'policy', 'readiness_states', 'core_diagnostic_count', 'generated_at' );
		$missing  = array();

		foreach ( $required as $key ) {
			if ( ! array_key_exists( $key, $context ) ) {
				$missing[] = $key;
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: missing keys */
						__( 'Readiness_Registry::get_governance_context() missing keys: %s', 'wpshadow' ),
						implode( ', ', $missing )
					),
					'severity' => 'warning',
				),
			);
		}

		if ( (int) $context['core_diagnostic_count'] !== 50 ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %d: count */
						__( 'Governance context reports %d core diagnostics; expected 50.', 'wpshadow' ),
						(int) $context['core_diagnostic_count']
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	// -------------------------------------------------------------------------
	// Sub-tests: Diagnostic_Registry new methods
	// -------------------------------------------------------------------------

	/**
	 * Test: Diagnostic_Registry::get_core_diagnostics() returns an array and
	 *       all items have is_core === true.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_diagnostic_registry_core_method(): array {
		if ( ! method_exists( Diagnostic_Registry::class, 'get_core_diagnostics' ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Diagnostic_Registry::get_core_diagnostics() method not found.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		$core = Diagnostic_Registry::get_core_diagnostics();

		if ( ! is_array( $core ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'get_core_diagnostics() did not return an array.', 'wpshadow' ),
					'severity' => 'warning',
				),
			);
		}

		$non_core = array_filter( $core, static fn( array $d ) => empty( $d['is_core'] ) );
		if ( ! empty( $non_core ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %d count */
						__( 'get_core_diagnostics() returned %d items without is_core flag.', 'wpshadow' ),
						count( $non_core )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: Diagnostic_Registry::get_by_confidence() returns only items
	 *       matching the requested tier.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_diagnostic_registry_confidence_filter(): array {
		if ( ! method_exists( Diagnostic_Registry::class, 'get_by_confidence' ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Diagnostic_Registry::get_by_confidence() method not found.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		foreach ( array( 'high', 'standard', 'low' ) as $tier ) {
			$items = Diagnostic_Registry::get_by_confidence( $tier );

			if ( ! is_array( $items ) ) {
				return array(
					'passed'  => false,
					'finding' => array(
						'message'  => sprintf(
							/* translators: %s: tier */
							__( 'get_by_confidence("%s") did not return an array.', 'wpshadow' ),
							$tier
						),
						'severity' => 'warning',
					),
				);
			}

			$wrong_tier = array_filter(
				$items,
				static fn( array $d ) => ( $d['confidence'] ?? 'standard' ) !== $tier
			);

			if ( ! empty( $wrong_tier ) ) {
				return array(
					'passed'  => false,
					'finding' => array(
						'message'  => sprintf(
							/* translators: %1$d: bad count, %2$s: requested tier */
							__( 'get_by_confidence("%2$s") returned %1$d items with incorrect confidence tier.', 'wpshadow' ),
							count( $wrong_tier ),
							$tier
						),
						'severity' => 'warning',
					),
				);
			}
		}

		return array( 'passed' => true );
	}
}
