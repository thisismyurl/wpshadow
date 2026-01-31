<?php
/**
 * Quantum Computing Readiness Assessment Not Performed Diagnostic
 *
 * Checks if quantum computing readiness is assessed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quantum Computing Readiness Assessment Not Performed Diagnostic Class
 *
 * Detects unassessed quantum readiness.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Quantum_Computing_Readiness_Assessment_Not_Performed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'quantum-computing-readiness-assessment-not-performed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Quantum Computing Readiness Assessment Not Performed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if quantum computing readiness is assessed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if quantum-safe algorithms are considered
		if ( ! get_option( 'quantum_readiness_assessment_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Quantum computing readiness has not been assessed. Plan for quantum-resistant cryptography and post-quantum algorithms as threat becomes realized.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 5,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/quantum-computing-readiness-assessment-not-performed',
			);
		}

		return null;
	}
}
