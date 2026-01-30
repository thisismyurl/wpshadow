<?php
/**
 * Wp Migrate Db Pro Profiles Diagnostic
 *
 * Wp Migrate Db Pro Profiles issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1063.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Migrate Db Pro Profiles Diagnostic Class
 *
 * @since 1.1063.0000
 */
class Diagnostic_WpMigrateDbProProfiles extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-pro-profiles';
	protected static $title = 'Wp Migrate Db Pro Profiles';
	protected static $description = 'Wp Migrate Db Pro Profiles issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WP Migrate DB Pro
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Saved profiles exist
		$profiles = get_option( 'wpmdb_saved_profiles', array() );
		$profile_count = is_array( $profiles ) ? count( $profiles ) : 0;
		
		if ( $profile_count === 0 ) {
			return null;
		}
		
		// Check 2: Credentials in profiles
		foreach ( $profiles as $profile ) {
			if ( isset( $profile['connection_info'] ) && ! empty( $profile['connection_info'] ) ) {
				$issues[] = __( 'Profile contains connection credentials (security risk)', 'wpshadow' );
				break;
			}
		}
		
		// Check 3: Backup before migration
		$backup_enabled = get_option( 'wpmdb_backup_before_migration', false );
		if ( ! $backup_enabled ) {
			$issues[] = __( 'No backup before migration (data loss risk)', 'wpshadow' );
		}
		
		// Check 4: Profile encryption
		$encrypt_profiles = get_option( 'wpmdb_encrypt_profiles', false );
		if ( ! $encrypt_profiles ) {
			$issues[] = __( 'Profile encryption disabled (credentials exposed)', 'wpshadow' );
		}
		
		// Check 5: Outdated profiles
		$outdated_count = 0;
		foreach ( $profiles as $profile ) {
			if ( isset( $profile['last_used'] ) && ( time() - $profile['last_used'] ) > ( 180 * DAY_IN_SECONDS ) ) {
				$outdated_count++;
			}
		}
		
		if ( $outdated_count > 3 ) {
			$issues[] = sprintf( __( '%d profiles not used in 6+ months (cleanup)', 'wpshadow' ), $outdated_count );
		}
		
		// Check 6: Default profile name
		foreach ( $profiles as $profile ) {
			if ( isset( $profile['name'] ) && ( 'default' === strtolower( $profile['name'] ) || 'profile 1' === strtolower( $profile['name'] ) ) ) {
				$issues[] = __( 'Generic profile names used (organization)', 'wpshadow' );
				break;
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of profile issues */
				__( 'WP Migrate DB Pro profiles have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-pro-profiles',
		);
	}
}
