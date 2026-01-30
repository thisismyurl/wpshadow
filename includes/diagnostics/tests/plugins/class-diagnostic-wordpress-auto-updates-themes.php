<?php
/**
 * Wordpress Auto Updates Themes Diagnostic
 *
 * Wordpress Auto Updates Themes issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1255.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Auto Updates Themes Diagnostic Class
 *
 * @since 1.1255.0000
 */
class Diagnostic_WordpressAutoUpdatesThemes extends Diagnostic_Base {

	protected static $slug = 'wordpress-auto-updates-themes';
	protected static $title = 'Wordpress Auto Updates Themes';
	protected static $description = 'Wordpress Auto Updates Themes issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Theme auto-updates enabled globally
		$auto_updates_enabled = get_site_option( 'auto_update_themes', array() );
		if ( empty( $auto_updates_enabled ) ) {
			return null;
		}
		
		// Check 2: Active theme in auto-update list
		$active_theme = get_stylesheet();
		if ( in_array( $active_theme, $auto_updates_enabled, true ) ) {
			// Check if it's a child theme
			$theme = wp_get_theme();
			if ( ! $theme->parent() ) {
				$issues[] = __( 'Active theme auto-updates enabled (not a child theme, customization risk)', 'wpshadow' );
			}
		}
		
		// Check 3: Staging environment
		$is_staging = defined( 'WP_ENV' ) && 'staging' === WP_ENV;
		$is_production = ! $is_staging && ! defined( 'WP_LOCAL_DEV' );
		
		if ( $is_production && count( $auto_updates_enabled ) > 0 ) {
			$issues[] = sprintf( __( '%d themes auto-updating on production (test first)', 'wpshadow' ), count( $auto_updates_enabled ) );
		}
		
		// Check 4: Backup before update
		$backup_before = get_option( 'auto_update_backup', 'no' );
		if ( 'no' === $backup_before ) {
			$issues[] = __( 'No backup before auto-update (recovery risk)', 'wpshadow' );
		}
		
		// Check 5: Update notifications
		$notify_on_update = get_option( 'auto_update_notify', 'yes' );
		if ( 'no' === $notify_on_update ) {
			$issues[] = __( 'Update notifications disabled (unaware of changes)', 'wpshadow' );
		}
		
		// Check 6: Parent themes with child themes
		foreach ( $auto_updates_enabled as $theme_slug ) {
			$theme = wp_get_theme( $theme_slug );
			if ( $theme->exists() ) {
				// Check if any active theme is a child of this
				$active = wp_get_theme();
				if ( $active->get_template() === $theme_slug ) {
					$issues[] = sprintf(
						/* translators: %s: theme name */
						__( 'Parent theme %s auto-updating (may break child theme)', 'wpshadow' ),
						$theme->get( 'Name' )
					);
					break;
				}
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
				/* translators: %s: list of auto-update issues */
				__( 'Theme auto-updates have %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-auto-updates-themes',
		);
	}
}
