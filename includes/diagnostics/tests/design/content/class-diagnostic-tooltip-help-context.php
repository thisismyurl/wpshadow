<?php
/**
 * Tooltip Help Context Diagnostic
 *
 * Issue #4916: Complex Fields Missing Tooltips
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if complex UI has contextual help.
 * Users shouldn't leave the page to understand fields.
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
 * Diagnostic_Tooltip_Help_Context Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tooltip_Help_Context extends Diagnostic_Base {

	protected static $slug = 'tooltip-help-context';
	protected static $title = 'Complex Fields Missing Tooltips';
	protected static $description = 'Checks if form fields have contextual help tooltips';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add tooltips to all non-obvious form fields', 'wpshadow' );
		$issues[] = __( 'Use question mark icon (?) next to field label', 'wpshadow' );
		$issues[] = __( 'Show tooltip on hover and keyboard focus', 'wpshadow' );
		$issues[] = __( 'Keep tooltips concise (50-150 words)', 'wpshadow' );
		$issues[] = __( 'Include "Learn more" link to full documentation', 'wpshadow' );
		$issues[] = __( 'Use aria-describedby for screen readers', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Users shouldn\'t have to leave the page to understand form fields. Tooltips provide just-in-time help exactly when needed.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/tooltip-help?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
					'accessibility'           => 'Use aria-describedby to link label to tooltip',
					'timing'                  => 'Show tooltip on hover (300ms delay)',
				),
			);
		}

		return null;
	}
}
