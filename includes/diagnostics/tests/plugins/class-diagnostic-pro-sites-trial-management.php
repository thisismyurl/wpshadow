<?php
/**
 * Pro Sites Trial Management Diagnostic
 *
 * Pro Sites Trial Management misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.954.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro Sites Trial Management Diagnostic Class
 *
 * @since 1.954.0000
 */
class Diagnostic_ProSitesTrialManagement extends Diagnostic_Base {

	protected static $slug = 'pro-sites-trial-management';
	protected static $title = 'Pro Sites Trial Management';
	protected static $description = 'Pro Sites Trial Management misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ProSites' ) || ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify trial period is configured
		$trial_days = get_site_option( 'psts_trial_days', 0 );
		if ( empty( $trial_days ) ) {
			$issues[] = 'Trial period not configured';
		}
		
		// Check 2: Verify trial limitations are set
		$trial_level = get_site_option( 'psts_trial_level', 0 );
		if ( empty( $trial_level ) ) {
			$issues[] = 'Trial level restrictions not defined';
		}
		
		// Check 3: Check for trial expiration notifications
		$trial_email = get_site_option( 'psts_trial_email_enable', false );
		if ( ! $trial_email ) {
			$issues[] = 'Trial expiration emails not enabled';
		}
		
		// Check 4: Verify automatic downgrade after trial
		$auto_downgrade = get_site_option( 'psts_trial_auto_downgrade', false );
		if ( ! $auto_downgrade ) {
			$issues[] = 'Automatic trial downgrade not enabled';
		}
		
		// Check 5: Check for abuse prevention (limiting trials per user)
		$prevent_multiple = get_site_option( 'psts_prevent_multiple_trials', false );
		if ( ! $prevent_multiple ) {
			$issues[] = 'Multiple trial prevention not enabled';
		}
		
		// Check 6: Verify trial sites are being tracked
		$trial_sites = get_sites( array(
			'meta_key'   => 'psts_trial',
			'meta_value' => '1',
			'count'      => true,
		) );
		if ( $trial_sites > 10 && empty( $trial_days ) ) {
			$issues[] = 'Sites on trial but trial settings not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Pro Sites trial management issue(s): %s',
					$issue_count,
					impode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/pro-sites-trial-management',
			);
		}
		
		return null;
	}
}
