<?php
/**
 * Restrict Content Pro Membership Levels Diagnostic
 *
 * RCP membership levels poorly structured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.327.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Membership Levels Diagnostic Class
 *
 * @since 1.327.0000
 */
class Diagnostic_RestrictContentProMembershipLevels extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-membership-levels';
	protected static $title = 'Restrict Content Pro Membership Levels';
	protected static $description = 'RCP membership levels poorly structured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		// Check if RCP is active
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) && ! class_exists( 'RCP_Levels' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check membership levels table
		$table_name = $wpdb->prefix . 'restrict_content_pro';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
		if ( ! $table_exists ) {
			$issues[] = 'membership_table_missing';
			$threat_level += 40;
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'RCP membership levels database table is missing. This prevents membership functionality from working.', 'wpshadow' ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-membership-levels',
			);
		}

		// Check membership levels
		$levels = $wpdb->get_results( "SELECT * FROM {$table_name}" );
		if ( empty( $levels ) ) {
			$issues[] = 'no_membership_levels';
			$threat_level += 30;
		}

		// Check pricing configuration
		if ( ! empty( $levels ) ) {
			foreach ( $levels as $level ) {
				if ( empty( $level->price ) || $level->price == 0 ) {
					$free_levels = isset( $free_levels ) ? $free_levels + 1 : 1;
				}
			}
			if ( isset( $free_levels ) && $free_levels === count( $levels ) ) {
				$issues[] = 'all_levels_free';
				$threat_level += 15;
			}
		}

		// Check access restrictions
		$restricted_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'rcp_subscription_level_id'"
		);
		if ( $restricted_posts === 0 && ! empty( $levels ) ) {
			$issues[] = 'no_content_restricted';
			$threat_level += 20;
		}

		// Check expiration settings
		$auto_renew = get_option( 'rcp_auto_renew', 0 );
		if ( ! $auto_renew ) {
			$issues[] = 'auto_renew_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of membership issues */
				__( 'Restrict Content Pro membership levels have structure issues: %s. This affects member access and revenue.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-membership-levels',
			);
		}
		
		return null;
	}
}
