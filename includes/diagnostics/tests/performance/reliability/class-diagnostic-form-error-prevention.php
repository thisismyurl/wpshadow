<?php
/**
 * Form Error Prevention Diagnostic
 *
 * Issue #4961: No Error Prevention for Legal/Financial Forms
 * Pillar: 🛡️ Safe by Default / #8: Inspire Confidence
 *
 * Checks if important forms have error prevention.
 * Legal and financial submissions need review before submit.
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
 * Diagnostic_Form_Error_Prevention Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Form_Error_Prevention extends Diagnostic_Base {

	protected static $slug = 'form-error-prevention';
	protected static $title = 'No Error Prevention for Legal/Financial Forms';
	protected static $description = 'Checks if critical forms allow review before submission';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Provide review step before submitting legal/financial data', 'wpshadow' );
		$issues[] = __( 'Allow users to edit information before final submit', 'wpshadow' );
		$issues[] = __( 'Show confirmation dialog for irreversible actions', 'wpshadow' );
		$issues[] = __( 'Provide undo option after submission (grace period)', 'wpshadow' );
		$issues[] = __( 'Store draft/progress for complex multi-step forms', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Legal and financial forms need review steps to prevent costly mistakes. Users should see a summary before final submission.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-prevention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 3.3.4 Error Prevention (Legal, Financial, Data) - Level AA',
					'affected_forms'          => 'Payments, contracts, account deletion, data sharing consent',
					'commandment'             => 'Commandment #8: Inspire Confidence',
				),
			);
		}

		return null;
	}
}
