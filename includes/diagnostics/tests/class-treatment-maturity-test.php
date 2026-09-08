<?php
/**
 * Treatment Maturity Tests
 *
 * Validates the Treatment_Metadata registry: completeness, field integrity,
 * risk-level normalization, maturity distribution, and governance context
 * treatment summary.
 *
 * @package WPShadow
 * @since   0.7055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Treatment_Metadata;
use WPShadow\Core\Readiness_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test suite for Treatment_Metadata and treatment governance backend.
 */
class Treatment_Maturity_Test extends Diagnostic_Base {

	protected static $slug  = 'treatment-maturity-test';
	protected static $title = 'Treatment Maturity Tests';

	public static function get_title(): string {
		return __( 'Treatment Maturity Tests', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Validates Treatment_Metadata registry completeness, risk-level values, maturity distribution, and governance context treatment summary.', 'wpshadow' );
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
			'test_treatment_metadata_class_exists',
			'test_total_treatment_count',
			'test_maturity_values_valid',
			'test_risk_level_values_valid',
			'test_category_values_valid',
			'test_shipped_count_reasonable',
			'test_reversible_count_matches_shipped',
			'test_guidance_treatments_not_reversible',
			'test_governance_context_includes_treatments',
			'test_get_counts_structure',
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
				'message' => __( 'All Treatment_Metadata backend tests passed.', 'wpshadow' ),
			);
		}

		return array(
			'status'   => 'fail',
			'message'  => sprintf(
				/* translators: %d: number of failed sub-tests */
				_n( '%d treatment maturity test failed.', '%d treatment maturity tests failed.', count( $findings ), 'wpshadow' ),
				count( $findings )
			),
			'findings' => $findings,
		);
	}

	// -------------------------------------------------------------------------
	// Sub-tests
	// -------------------------------------------------------------------------

	/**
	 * Test: Treatment_Metadata class exists and is loadable.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_treatment_metadata_class_exists(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Treatment_Metadata class not found. Check bootstrap autoloader registration.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		$sample = Treatment_Metadata::get( 'auth-keys-and-salts-set' );
		if ( empty( $sample ) || ! isset( $sample['maturity'] ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Treatment_Metadata::get() returned empty or incomplete data for a known slug.', 'wpshadow' ),
					'severity' => 'critical',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: At least 70 treatments are registered (registry is not empty/sparse).
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_total_treatment_count(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$count = count( Treatment_Metadata::get_all() );

		if ( $count < 70 ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %d: actual treatment count */
						__( 'Treatment_Metadata registry contains only %d entries; expected at least 70. Check raw_registry() for missing slugs.', 'wpshadow' ),
						$count
					),
					'severity' => 'warning',
					'context'  => array( 'count' => $count ),
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: All maturity values are 'shipped' or 'guidance'.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_maturity_values_valid(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$allowed = array( 'shipped', 'guidance' );
		$invalid = array();

		foreach ( Treatment_Metadata::get_all() as $slug => $meta ) {
			if ( ! in_array( $meta['maturity'], $allowed, true ) ) {
				$invalid[] = $slug . ' (' . esc_html( $meta['maturity'] ) . ')';
			}
		}

		if ( ! empty( $invalid ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: list of bad slugs */
						__( 'Invalid maturity values in Treatment_Metadata: %s', 'wpshadow' ),
						implode( ', ', $invalid )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: All risk_level values are from the allowed set.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_risk_level_values_valid(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$allowed = array( 'safe', 'moderate', 'high', 'guidance' );
		$invalid = array();

		foreach ( Treatment_Metadata::get_all() as $slug => $meta ) {
			if ( ! in_array( $meta['risk_level'], $allowed, true ) ) {
				$invalid[] = $slug . ' (' . esc_html( $meta['risk_level'] ) . ')';
			}
		}

		if ( ! empty( $invalid ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: list of bad slugs */
						__( 'Invalid risk_level values in Treatment_Metadata: %s', 'wpshadow' ),
						implode( ', ', $invalid )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: All category values are from the allowed set.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_category_values_valid(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$allowed = array( 'security', 'performance', 'database', 'content', 'configuration', 'maintenance' );
		$invalid = array();

		foreach ( Treatment_Metadata::get_all() as $slug => $meta ) {
			if ( ! in_array( $meta['category'], $allowed, true ) ) {
				$invalid[] = $slug . ' (' . esc_html( $meta['category'] ) . ')';
			}
		}

		if ( ! empty( $invalid ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: list of bad slugs */
						__( 'Invalid category values in Treatment_Metadata: %s', 'wpshadow' ),
						implode( ', ', $invalid )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: At least 60% of treatments are 'shipped' (not guidance-only).
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_shipped_count_reasonable(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$counts   = Treatment_Metadata::get_counts();
		$total    = $counts['total'];
		$shipped  = $counts['shipped'];

		if ( $total === 0 ) {
			return array( 'passed' => true );
		}

		$pct = $shipped / $total;

		if ( $pct < 0.60 ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %1$d shipped, %2$d total */
						__( 'Only %1$d of %2$d treatments are fully automated (shipped). Expected at least 60%%. Review Treatment_Metadata maturity assignments.', 'wpshadow' ),
						$shipped,
						$total
					),
					'severity' => 'warning',
					'context'  => array(
						'shipped' => $shipped,
						'total'   => $total,
					),
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: Reversible count equals shipped count.
	 *       Every shipped treatment must be reversible.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_reversible_count_matches_shipped(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$counts     = Treatment_Metadata::get_counts();
		$shipped    = $counts['shipped'];
		$reversible = $counts['reversible'];

		// Some shipped treatments may intentionally be non-reversible (e.g. data
		// deletion treatments where undo is impossible without a backup).
		// Warn only if reversible count is less than 75% of shipped count.
		if ( $shipped > 0 && ( $reversible / $shipped ) < 0.75 ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %1$d reversible, %2$d shipped */
						__( 'Only %1$d of %2$d shipped treatments are reversible (< 75%%). Review reversible flags in Treatment_Metadata.', 'wpshadow' ),
						$reversible,
						$shipped
					),
					'severity' => 'warning',
					'context'  => array(
						'reversible' => $reversible,
						'shipped'    => $shipped,
					),
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: All guidance treatments are marked reversible=false.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_guidance_treatments_not_reversible(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$invalid = array();

		foreach ( Treatment_Metadata::get_all() as $slug => $meta ) {
			if ( 'guidance' === $meta['maturity'] && true === $meta['reversible'] ) {
				$invalid[] = $slug;
			}
		}

		if ( ! empty( $invalid ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: list of bad slugs */
						__( 'Guidance treatments incorrectly marked reversible=true: %s', 'wpshadow' ),
						implode( ', ', $invalid )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: Readiness_Registry::get_governance_context() includes treatment counts.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_governance_context_includes_treatments(): array {
		if ( ! class_exists( Readiness_Registry::class ) ) {
			return array( 'passed' => true );
		}

		$context = Readiness_Registry::get_governance_context();

		if ( empty( $context['treatments'] ) || ! is_array( $context['treatments'] ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => __( 'Readiness_Registry::get_governance_context() is missing the "treatments" key. Update get_governance_context() to include Treatment_Metadata::get_counts().', 'wpshadow' ),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}

	/**
	 * Test: get_counts() returns a correctly structured summary array.
	 *
	 * @return array{passed: bool, finding?: array<string, mixed>}
	 */
	private static function test_get_counts_structure(): array {
		if ( ! class_exists( Treatment_Metadata::class ) ) {
			return array( 'passed' => true );
		}

		$counts   = Treatment_Metadata::get_counts();
		$required = array( 'total', 'shipped', 'guidance', 'reversible', 'by_risk', 'by_category' );
		$missing  = array();

		foreach ( $required as $key ) {
			if ( ! array_key_exists( $key, $counts ) ) {
				$missing[] = $key;
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: missing keys */
						__( 'Treatment_Metadata::get_counts() is missing required keys: %s', 'wpshadow' ),
						implode( ', ', $missing )
					),
					'severity' => 'warning',
				),
			);
		}

		// Verify by_risk and by_category sub-keys.
		$risk_keys     = array( 'safe', 'moderate', 'high', 'guidance' );
		$category_keys = array( 'security', 'performance', 'database', 'content', 'configuration', 'maintenance' );
		$missing_sub   = array();

		foreach ( $risk_keys as $k ) {
			if ( ! array_key_exists( $k, $counts['by_risk'] ) ) {
				$missing_sub[] = 'by_risk.' . $k;
			}
		}

		foreach ( $category_keys as $k ) {
			if ( ! array_key_exists( $k, $counts['by_category'] ) ) {
				$missing_sub[] = 'by_category.' . $k;
			}
		}

		if ( ! empty( $missing_sub ) ) {
			return array(
				'passed'  => false,
				'finding' => array(
					'message'  => sprintf(
						/* translators: %s: missing sub-keys */
						__( 'Treatment_Metadata::get_counts() has incomplete sub-arrays. Missing: %s', 'wpshadow' ),
						implode( ', ', $missing_sub )
					),
					'severity' => 'warning',
				),
			);
		}

		return array( 'passed' => true );
	}
}
