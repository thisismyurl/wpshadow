<?php
/**
 * Success Confirmation Messages Diagnostic
 *
 * Issue #4918: No Confirmation After Actions
 * Pillar: #8: Inspire Confidence / ⚙️ Murphy's Law
 *
 * Checks if actions provide success feedback.
 * Users need to know their actions succeeded.
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
 * Diagnostic_Success_Confirmation_Messages Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Success_Confirmation_Messages extends Diagnostic_Base {

	protected static $slug = 'success-confirmation-messages';
	protected static $title = 'No Confirmation After Actions';
	protected static $description = 'Checks if successful actions show clear feedback';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show success message after every form submission', 'wpshadow' );
		$issues[] = __( 'Display confirmation for 3-5 seconds, then auto-dismiss', 'wpshadow' );
		$issues[] = __( 'Use green color and checkmark icon for success', 'wpshadow' );
		$issues[] = __( 'Be specific: "Settings saved" not just "Success"', 'wpshadow' );
		$issues[] = __( 'Show what changed: "Email changed to new@example.com"', 'wpshadow' );
		$issues[] = __( 'Announce to screen readers with role="status"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Users need confirmation that their actions succeeded. Without feedback, they wonder if their click worked and click again (duplicate actions).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/success-messages',
				'details'      => array(
					'recommendations'         => $issues,
					'commandment'             => 'Commandment #8: Inspire Confidence',
					'html_pattern'            => '<div role="status" aria-live="polite">Settings saved!</div>',
					'timing'                  => 'Show immediately, auto-dismiss after 5 seconds',
				),
			);
		}

		return null;
	}
}
