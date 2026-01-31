<?php
/**
 * Wordfence Waf Learning Mode Diagnostic
 *
 * Wordfence Waf Learning Mode misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.847.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Waf Learning Mode Diagnostic Class
 *
 * @since 1.847.0000
 */
class Diagnostic_WordfenceWafLearningMode extends Diagnostic_Base {

	protected static $slug = 'wordfence-waf-learning-mode';
	protected static $title = 'Wordfence Waf Learning Mode';
	protected static $description = 'Wordfence Waf Learning Mode misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: WAF mode
		$waf_mode = get_option( 'wordfenceWAFMode', 'disabled' );
		if ( 'learning' === $waf_mode ) {
			$issues[] = __( 'WAF in learning mode (not blocking attacks)', 'wpshadow' );
		}

		// Check 2: Learning mode duration
		$learning_start = get_option( 'wordfenceWAFLearningStart', 0 );
		if ( $learning_start > 0 ) {
			$days_learning = ( time() - $learning_start ) / DAY_IN_SECONDS;
			if ( $days_learning > 7 ) {
				$issues[] = sprintf( __( 'Learning mode for %d days (should be enabled)', 'wpshadow' ), round( $days_learning ) );
			}
		}

		// Check 3: False positive count
		$false_positives = get_option( 'wordfenceWAFFalsePositives', 0 );
		if ( $false_positives > 100 ) {
			$issues[] = sprintf( __( '%d false positives (rules too strict)', 'wpshadow' ), $false_positives );
		}

		// Check 4: Whitelisted IPs
		$whitelist = get_option( 'wordfenceWhitelist', array() );
		if ( count( $whitelist ) > 50 ) {
			$issues[] = sprintf( __( '%d whitelisted IPs (security bypass)', 'wpshadow' ), count( $whitelist ) );
		}

		// Check 5: WAF rules update
		$rules_updated = get_option( 'wordfenceWAFRulesLastUpdate', 0 );
		$days_since_update = ( time() - $rules_updated ) / DAY_IN_SECONDS;
		if ( $days_since_update > 7 ) {
			$issues[] = sprintf( __( 'Rules not updated for %d days (vulnerable)', 'wpshadow' ), round( $days_since_update ) );
		}

		// Check 6: Extended protection
		$extended = get_option( 'wordfenceWAFExtended', 'no' );
		if ( 'no' === $extended ) {
			$issues[] = __( 'Extended protection disabled (basic security only)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Wordfence WAF has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordfence-waf-learning-mode',
		);
	}
}
