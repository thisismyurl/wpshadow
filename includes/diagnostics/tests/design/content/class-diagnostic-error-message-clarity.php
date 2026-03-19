<?php
/**
 * Error Message Clarity Diagnostic
 *
 * Issue #4917: Error Messages Not Actionable
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if error messages explain what to do.
 * "Error 500" is useless. "Your password must be 12 characters" is helpful.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Error_Message_Clarity Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Error_Message_Clarity extends Diagnostic_Base {

	protected static $slug = 'error-message-clarity';
	protected static $title = 'Error Messages Not Actionable';
	protected static $description = 'Checks if error messages explain how to fix issues';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Explain WHAT failed in plain language', 'wpshadow' );
		$issues[] = __( 'Explain WHY it failed (invalid format, too long, etc)', 'wpshadow' );
		$issues[] = __( 'Explain HOW to fix it (clear next steps)', 'wpshadow' );
		$issues[] = __( 'Never show technical error codes without explanation', 'wpshadow' );
		$issues[] = __( 'Provide link to help documentation', 'wpshadow' );
		$issues[] = __( 'Use friendly tone: "Let\'s fix this" not "Error occurred"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Error messages should be helpful neighbors, not cryptic warnings. Tell users what happened and how to fix it in plain language.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-messages',
				'details'      => array(
					'recommendations'         => $issues,
					'bad_example'             => '"Error 500" or "Invalid input"',
					'good_example'            => '"Your password must be at least 12 characters. Try adding numbers and symbols."',
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
				),
			);
		}

		return null;
	}
}
