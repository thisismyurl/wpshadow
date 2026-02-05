<?php
/**
 * Loading State Indicators Diagnostic
 *
 * Issue #4980: No Loading State Indicators
 * Pillar: #8: Inspire Confidence / #1: Helpful Neighbor
 *
 * Checks if long operations show progress.
 * Silent operations make users think site is broken.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Loading_State_Indicators Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Loading_State_Indicators extends Diagnostic_Base {

	protected static $slug = 'loading-state-indicators';
	protected static $title = 'No Loading State Indicators';
	protected static $description = 'Checks if long-running operations show progress';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show spinner/animation during AJAX requests', 'wpshadow' );
		$issues[] = __( 'Disable button during submission (prevent double-submit)', 'wpshadow' );
		$issues[] = __( 'Show progress bar for uploads (percentage)', 'wpshadow' );
		$issues[] = __( 'Show estimated time for long operations', 'wpshadow' );
		$issues[] = __( 'Provide cancel button for long operations', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'When operations take more than 1 second, show loading indicators. Users need feedback that something is happening.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/loading-states',
				'details'      => array(
					'recommendations'         => $issues,
					'ux_principle'            => 'Users abandon operations without feedback',
					'1_second_rule'           => 'Show feedback for operations > 1 second',
					'commandment'             => 'Commandment #8: Inspire Confidence',
				),
			);
		}

		return null;
	}
}
