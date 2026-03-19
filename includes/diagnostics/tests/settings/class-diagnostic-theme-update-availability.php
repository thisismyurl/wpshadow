<?php
/**
 * Theme Update Availability Diagnostic
 *
 * Validates that all installed themes are updated and that updates are
 * available and applied promptly to reduce security risks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Update Availability Diagnostic Class
 *
 * Checks for theme updates and outdated themes.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Update_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-update-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Update Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates installed themes are updated and update checks are healthy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all themes.
		$themes = wp_get_themes();

		if ( empty( $themes ) ) {
			$issues[] = __( 'No themes installed (system error)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No themes found on this installation.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Install at least one theme immediately.', 'wpshadow' ),
				),
			);
		}

		// Check update transient.
		$update_data = get_transient( 'update_themes' );

		if ( empty( $update_data ) || ! isset( $update_data->checked ) ) {
			$issues[] = __( 'Theme update checks appear to be disabled or failing', 'wpshadow' );
		}

		$themes_with_updates = array();
		$themes_outdated     = array();

		if ( ! empty( $update_data ) && isset( $update_data->response ) && is_array( $update_data->response ) ) {
			foreach ( $update_data->response as $theme_slug => $update ) {
				$themes_with_updates[] = $theme_slug;

				if ( isset( $themes[ $theme_slug ] ) ) {
					$theme = $themes[ $theme_slug ];
					$themes_outdated[ $theme->get( 'Name' ) ] = array(
						'current' => $theme->get( 'Version' ),
						'update'  => $update['new_version'] ?? '',
					);
				}
			}
		}

		if ( ! empty( $themes_with_updates ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of themes with updates */
				__( '%d theme(s) have updates available', 'wpshadow' ),
				count( $themes_with_updates )
			);
		}

		// Check for themes with no update checks (external themes).
		$external_themes = array();
		foreach ( $themes as $theme_slug => $theme ) {
			$theme_uri = $theme->get( 'ThemeURI' );
			if ( empty( $theme_uri ) || false === strpos( $theme_uri, 'wordpress.org' ) ) {
				$external_themes[] = $theme->get( 'Name' );
			}
		}

		if ( ! empty( $external_themes ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of external themes */
				__( '%d theme(s) are from external sources (updates may not be automatic)', 'wpshadow' ),
				count( $external_themes )
			);
		}

		// Check active theme update status.
		$active_theme = wp_get_theme();
		$active_slug  = $active_theme->get_stylesheet();

		if ( in_array( $active_slug, $themes_with_updates, true ) ) {
			$issues[] = __( 'Active theme has an update available (apply update to reduce risk)', 'wpshadow' );
		}

		// Check for auto-update configuration.
		$auto_updates_enabled = wp_is_auto_update_enabled_for_type( 'theme' );

		if ( ! $auto_updates_enabled ) {
			$issues[] = __( 'Theme auto-updates are disabled (consider enabling for security)', 'wpshadow' );
		}

		// Check update permissions.
		$current_user = wp_get_current_user();
		if ( $current_user->exists() && ! current_user_can( 'update_themes' ) ) {
			$issues[] = __( 'Current user cannot update themes (ensure admin role available)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of update issues */
					__( 'Found %d theme update availability issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'             => $issues,
					'themes_with_updates' => $themes_with_updates,
					'themes_outdated'     => $themes_outdated,
					'auto_updates'        => $auto_updates_enabled,
					'recommendation'      => __( 'Apply available theme updates promptly and consider enabling auto-updates for themes.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
