<?php
/**
 * Wordfence License Status Diagnostic
 *
 * Validates Wordfence Premium license.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence License Status Class
 *
 * Checks if Wordfence Premium features are active.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_License extends Diagnostic_Base {

	protected static $slug        = 'wordfence-license';
	protected static $title       = 'Wordfence License Status';
	protected static $description = 'Validates Wordfence Premium license';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_license';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if premium license is active.
		$is_premium = wfConfig::get( 'isPaid', 0 );
		
		if ( ! $is_premium ) {
			// Using free version - check if premium would be beneficial.
			$issues[] = array(
				'issue' => 'Using Wordfence Free',
				'impact' => 'Delayed firewall rules, no real-time IP blocking, no country blocking',
				'recommendation' => 'Consider Premium for real-time threat protection',
			);
		} else {
			// Check license expiration.
			$license_exp = wfConfig::get( 'keyExpDays', 0 );
			if ( $license_exp < 30 && $license_exp >= 0 ) {
				$issues[] = array(
					'issue' => sprintf( 'Premium license expires in %d days', $license_exp ),
					'impact' => 'Will lose real-time protection features',
					'recommendation' => 'Renew license before expiration',
				);
			} elseif ( $license_exp < 0 ) {
				$issues[] = array(
					'issue' => 'Premium license has expired',
					'impact' => 'Critical - real-time protection disabled',
					'recommendation' => 'Renew immediately to restore protection',
				);
			}

			// Check if firewall rules are updating.
			$rules_last_updated = wfConfig::get( 'rulesLastUpdated', 0 );
			if ( $rules_last_updated ) {
				$hours_since_update = ( time() - $rules_last_updated ) / HOUR_IN_SECONDS;
				if ( $hours_since_update > 48 ) {
					$issues[] = array(
						'issue' => sprintf( 'Firewall rules not updated in %.1f hours', $hours_since_update ),
						'impact' => 'Missing latest threat definitions',
						'recommendation' => 'Check license connectivity',
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$has_expired = false;
			foreach ( $issues as $issue ) {
				if ( strpos( $issue['issue'], 'expired' ) !== false ) {
					$has_expired = true;
					break;
				}
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $has_expired
					? __( 'Wordfence Premium license expired - protection degraded!', 'wpshadow' )
					: sprintf(
						/* translators: %d: count */
						__( '%d licensing issues detected. Review Wordfence configuration.', 'wpshadow' ),
						count( $issues )
					),
				'severity'     => $has_expired ? 'high' : 'medium',
				'threat_level' => $has_expired ? 65 : 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-license',
				'data'         => array(
					'license_issues' => $issues,
					'total_issues' => count( $issues ),
					'is_premium' => $is_premium,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
