<?php
/**
 * MonsterInsights Demographics Diagnostic
 *
 * MonsterInsights demographics tracking not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.230.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Demographics Diagnostic Class
 *
 * @since 1.230.0000
 */
class Diagnostic_MonsterinsightsDemographics extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-demographics';
	protected static $title = 'MonsterInsights Demographics';
	protected static $description = 'MonsterInsights demographics tracking not enabled';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get MonsterInsights settings
		$mi_settings = get_option( 'monsterinsights_settings', array() );

		// Check demographics tracking
		$demographics = isset( $mi_settings['demographics'] ) ? $mi_settings['demographics'] : false;
		if ( ! $demographics ) {
			$issues[] = 'demographics_tracking_disabled';
			$threat_level += 15;
		}

		// Check remarketing
		$remarketing = isset( $mi_settings['remarketing'] ) ? $mi_settings['remarketing'] : false;
		if ( ! $remarketing ) {
			$issues[] = 'remarketing_disabled';
			$threat_level += 15;
		}

		// Check user ID tracking
		$userid = isset( $mi_settings['userid'] ) ? $mi_settings['userid'] : false;
		if ( ! $userid ) {
			$issues[] = 'user_id_tracking_disabled';
			$threat_level += 10;
		}

		// Check Google Signals
		$google_signals = isset( $mi_settings['google_signals'] ) ? $mi_settings['google_signals'] : false;
		if ( ! $google_signals ) {
			$issues[] = 'google_signals_disabled';
			$threat_level += 10;
		}

		// Check analytics connection
		$ua_code = isset( $mi_settings['ua_code'] ) ? $mi_settings['ua_code'] : '';
		if ( empty( $ua_code ) ) {
			$issues[] = 'analytics_not_connected';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of demographics tracking issues */
				__( 'MonsterInsights demographics tracking is incomplete: %s. This limits audience insights and targeting capabilities.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-demographics',
			);
		}
		
		return null;
	}
}
