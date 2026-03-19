<?php
/**
 * Themes Directory Permissions Diagnostic
 *
 * Checks if themes directory has correct permissions.
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
 * Themes Directory Permissions Diagnostic Class
 *
 * Verifies themes folder has secure yet functional permissions.
 * Like checking that your design templates have the right access controls.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Themes_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'themes-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Themes Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if themes directory has correct permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the themes directory permissions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permission issues detected, null otherwise.
	 */
	public static function check() {
		$themes_dir = get_theme_root();

		if ( ! is_dir( $themes_dir ) ) {
			return array(
				'id'           => self::$slug . '-missing',
				'title'        => __( 'Themes Directory Missing', 'wpshadow' ),
				'description'  => __( 'Your themes directory is missing (like losing all your design templates). This is a critical issue that prevents WordPress from displaying your site. Contact your hosting provider immediately.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path' => $themes_dir,
				),
			);
		}

		$perms = fileperms( $themes_dir );
		$perms_octal = substr( sprintf( '%o', $perms ), -4 );

		$recommended = '0755';
		$acceptable = array( '0755', '0775' );
		$too_permissive = array( '0777', '0776' );

		// Check if permissions are too open.
		if ( in_array( $perms_octal, $too_permissive, true ) ) {
			return array(
				'id'           => self::$slug . '-too-permissive',
				'title'        => __( 'Themes Directory Too Permissive', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current permissions, 2: recommended permissions */
					__( 'Your themes directory permissions (%1$s) are too open (like letting anyone modify your website design). Attackers could upload malicious theme files. Change permissions to %2$s using your hosting control panel or FTP client.', 'wpshadow' ),
					$perms_octal,
					$recommended
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $themes_dir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
				),
			);
		}

		// Check if directory is not writable (may be intentional).
		if ( ! is_writable( $themes_dir ) ) {
			// Check if automatic theme updates are enabled.
			$auto_updates_enabled = get_site_option( 'auto_update_themes', array() );

			if ( ! empty( $auto_updates_enabled ) ) {
				return array(
					'id'           => self::$slug . '-not-writable-auto-updates',
					'title'        => __( 'Themes Directory Not Writable', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: current permissions */
						__( 'Your themes directory isn\'t writable (permissions: %s) but you have automatic theme updates enabled. WordPress won\'t be able to update themes automatically. Either make the directory writable (0755) or disable automatic theme updates.', 'wpshadow' ),
						$perms_octal
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
					'context'      => array(
						'path'         => $themes_dir,
						'permissions'  => $perms_octal,
						'recommended'  => $recommended,
						'writable'     => false,
						'auto_updates' => true,
					),
				);
			} else {
				// Non-writable without auto-updates is okay for security.
				return null;
			}
		}

		// Check individual theme directories.
		$themes = wp_get_themes();
		$themes_checked = 0;
		$overly_permissive_themes = array();

		foreach ( $themes as $theme_slug => $theme ) {
			$theme_dir = $theme->get_stylesheet_directory();
			if ( is_dir( $theme_dir ) ) {
				++$themes_checked;
				$theme_perms = fileperms( $theme_dir );
				$theme_perms_octal = substr( sprintf( '%o', $theme_perms ), -4 );

				if ( in_array( $theme_perms_octal, $too_permissive, true ) ) {
					$overly_permissive_themes[] = array(
						'name'        => $theme->get( 'Name' ),
						'dir'         => basename( $theme_dir ),
						'permissions' => $theme_perms_octal,
					);
				}

				// Limit checking to avoid performance issues.
				if ( $themes_checked >= 20 ) {
					break;
				}
			}
		}

		if ( ! empty( $overly_permissive_themes ) ) {
			return array(
				'id'           => self::$slug . '-theme-dirs-permissive',
				'title'        => __( 'Individual Theme Directories Too Permissive', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of themes with wrong permissions */
					_n(
						'%d theme directory has overly permissive permissions (like leaving design templates unlocked). Fix these using your FTP client or hosting control panel.',
						'%d theme directories have overly permissive permissions (like leaving design templates unlocked). Fix these using your FTP client or hosting control panel.',
						count( $overly_permissive_themes ),
						'wpshadow'
					),
					count( $overly_permissive_themes )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'themes'      => $overly_permissive_themes,
					'recommended' => $recommended,
				),
			);
		}

		return null; // Permissions are good.
	}
}
