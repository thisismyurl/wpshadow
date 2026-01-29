<?php
/**
 * Wordfence Country Blocking Diagnostic
 *
 * Validates country blocking configuration.
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
 * Wordfence Country Blocking Class
 *
 * Checks if country blocking is properly configured.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_Country_Blocking extends Diagnostic_Base {

	protected static $slug        = 'wordfence-country-blocking';
	protected static $title       = 'Wordfence Country Blocking';
	protected static $description = 'Validates country blocking setup';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		// Country blocking is Premium-only.
		$is_premium = wfConfig::get( 'isPaid', 0 );
		if ( ! $is_premium ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_country';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if country blocking is enabled.
		$cbl_action = wfConfig::get( 'cbl_action', '' );
		
		if ( empty( $cbl_action ) ) {
			// Not using country blocking - this is optional, not necessarily an issue.
			return null;
		}

		// Check blocked countries list.
		$blocked_countries = wfConfig::get( 'cbl_countries', array() );
		
		if ( empty( $blocked_countries ) ) {
			$issues[] = 'Country blocking enabled but no countries selected';
		} else {
			$count = is_array( $blocked_countries ) ? count( $blocked_countries ) : 0;
			
			// Check if blocking too many countries.
			if ( $count > 100 ) {
				$issues[] = sprintf( 'Blocking %d countries - may impact legitimate traffic', $count );
			}
			
			// Check if whitelisting own country.
			$server_country = wfConfig::get( 'serverCountry', '' );
			if ( $server_country && in_array( $server_country, $blocked_countries, true ) ) {
				$issues[] = sprintf( 'Blocking server\'s own country (%s) - may cause issues', $server_country );
			}
		}

		// Check redirection settings.
		$redirect_url = wfConfig::get( 'cbl_redirURL', '' );
		if ( ! empty( $redirect_url ) && ! filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {
			$issues[] = 'Invalid redirect URL configured';
		}

		// Check if bypasses are configured.
		$bypass_enabled = wfConfig::get( 'cbl_loggedInBlocked', '' );
		if ( 'on' === $bypass_enabled ) {
			$issues[] = 'Logged-in users bypass country blocking - reduces effectiveness';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d country blocking configuration issues. Review settings.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-country-blocking',
				'data'         => array(
					'blocking_issues' => $issues,
					'total_issues' => count( $issues ),
					'blocked_countries_count' => is_array( $blocked_countries ) ? count( $blocked_countries ) : 0,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
