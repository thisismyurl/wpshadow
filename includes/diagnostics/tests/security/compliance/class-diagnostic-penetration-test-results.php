<?php
/**
 * Penetration Test Results Diagnostic
 *
 * Checks if latest penetration test results are on file.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Penetration Test Results Diagnostic Class
 *
 * Verifies that enterprise penetration testing has been performed
 * and results are documented for compliance audits.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Penetration_Test_Results extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'penetration-test-results';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Penetration Test Results';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Latest penetration test results on file';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the penetration test results check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if pen test results missing, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Check for pen test documentation in options.
		$pen_test_data = get_option( 'wpshadow_pen_test_results' );
		$pen_test_date = get_option( 'wpshadow_pen_test_date' );
		$pen_test_vendor = get_option( 'wpshadow_pen_test_vendor' );

		$stats['pen_test_documented'] = ! empty( $pen_test_data );

		if ( ! $pen_test_data ) {
			$issues[] = __( 'No penetration test results documented', 'wpshadow' );
			$stats['pen_test_date'] = null;
			$stats['pen_test_vendor'] = null;
		} else {
			$stats['pen_test_date'] = $pen_test_date;
			$stats['pen_test_vendor'] = $pen_test_vendor;

			// Check if test is recent (within 12 months).
			if ( ! empty( $pen_test_date ) ) {
				$test_timestamp = strtotime( $pen_test_date );
				$current_time = current_time( 'timestamp' );
				$days_old = ( $current_time - $test_timestamp ) / ( 60 * 60 * 24 );

				$stats['days_since_pen_test'] = round( $days_old );

				if ( $days_old > 365 ) {
					$issues[] = sprintf(
						/* translators: %d: days */
						__( 'Last penetration test was %d days ago - recommend annual testing', 'wpshadow' ),
						round( $days_old )
					);
				} elseif ( $days_old > 180 ) {
					$issues[] = sprintf(
						/* translators: %d: days */
						__( 'Penetration test is %d days old - consider updating', 'wpshadow' ),
						round( $days_old )
					);
				}
			}
		}

		// Check for critical findings remediation status.
		$critical_findings = get_option( 'wpshadow_pen_test_critical' );
		if ( ! empty( $critical_findings ) ) {
			$critical_count = is_array( $critical_findings ) ? count( $critical_findings ) : 1;
			$remediated = get_option( 'wpshadow_pen_test_critical_remediated', 0 );

			$stats['critical_findings'] = $critical_count;
			$stats['critical_remediated'] = $remediated;

			if ( $remediated < $critical_count ) {
				$outstanding = $critical_count - $remediated;
				$issues[] = sprintf(
					/* translators: %d: number of findings */
					__( '%d critical findings still open from penetration test', 'wpshadow' ),
					$outstanding
				);
			}
		}

		// Check for test scope documentation.
		$test_scope = get_option( 'wpshadow_pen_test_scope' );
		if ( ! $test_scope ) {
			$issues[] = __( 'Penetration test scope not documented', 'wpshadow' );
			$stats['scope_documented'] = false;
		} else {
			$stats['scope_documented'] = true;
			$stats['test_scope'] = sanitize_text_field( $test_scope );
		}

		// Check for professional certification.
		$tester_certified = get_option( 'wpshadow_pen_tester_oscp' );
		$stats['tester_certified'] = boolval( $tester_certified );

		if ( ! $tester_certified && ! empty( $pen_test_data ) ) {
			$issues[] = __( 'Tester certification status not documented - recommend OSCP/CEH certified tester', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Penetration test issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/penetration-testing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // Penetration test results properly documented.
	}
}
