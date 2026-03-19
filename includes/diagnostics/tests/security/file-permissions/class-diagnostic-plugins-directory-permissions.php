<?php
/**
 * Plugins Directory Permissions Diagnostic
 *
 * Checks if plugins directory has correct permissions.
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
 * Plugins Directory Permissions Diagnostic Class
 *
 * Verifies plugins folder has secure yet functional permissions.
 * Like checking that your software library has the right access controls.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugins_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugins-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugins Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins directory has correct permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the plugins directory permissions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permission issues detected, null otherwise.
	 */
	public static function check() {
		$plugins_dir = WP_PLUGIN_DIR;

		if ( ! is_dir( $plugins_dir ) ) {
			return array(
				'id'           => self::$slug . '-missing',
				'title'        => __( 'Plugins Directory Missing', 'wpshadow' ),
				'description'  => __( 'Your plugins directory is missing (like losing your entire software library). This is a critical issue that prevents WordPress from working properly. Contact your hosting provider immediately.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path' => $plugins_dir,
				),
			);
		}

		$perms = fileperms( $plugins_dir );
		$perms_octal = substr( sprintf( '%o', $perms ), -4 );

		$recommended = '0755';
		$acceptable = array( '0755', '0775' );
		$too_permissive = array( '0777', '0776' );

		// Check if permissions are too open.
		if ( in_array( $perms_octal, $too_permissive, true ) ) {
			return array(
				'id'           => self::$slug . '-too-permissive',
				'title'        => __( 'Plugins Directory Too Permissive', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current permissions, 2: recommended permissions */
					__( 'Your plugins directory permissions (%1$s) are too open (like letting anyone install software on your computer). This is a major security risk—attackers could upload malicious plugins. Change permissions to %2$s immediately using your hosting control panel or FTP client.', 'wpshadow' ),
					$perms_octal,
					$recommended
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $plugins_dir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
				),
			);
		}

		// Check if directory is not writable (needs context).
		if ( ! is_writable( $plugins_dir ) ) {
			// Being non-writable might be intentional for security.
			// Only warn if automatic updates are enabled.
			$auto_updates_enabled = get_site_option( 'auto_update_plugins', array() );

			if ( ! empty( $auto_updates_enabled ) ) {
				return array(
					'id'           => self::$slug . '-not-writable-auto-updates',
					'title'        => __( 'Plugins Directory Not Writable', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: current permissions */
						__( 'Your plugins directory isn\'t writable (permissions: %s) but you have automatic plugin updates enabled. WordPress won\'t be able to update plugins automatically. Either make the directory writable (0755) or disable automatic updates.', 'wpshadow' ),
						$perms_octal
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
					'context'      => array(
						'path'         => $plugins_dir,
						'permissions'  => $perms_octal,
						'recommended'  => $recommended,
						'writable'     => false,
						'auto_updates' => true,
					),
				);
			} else {
				// Non-writable without auto-updates is a security best practice.
				return null;
			}
		}

		// Check individual plugin directories for overly permissive settings.
		$plugins = get_plugins();
		$plugin_dirs_checked = 0;
		$overly_permissive_plugins = array();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );
			if ( is_dir( $plugin_dir ) && $plugin_dir !== $plugins_dir ) {
				++$plugin_dirs_checked;
				$plugin_perms = fileperms( $plugin_dir );
				$plugin_perms_octal = substr( sprintf( '%o', $plugin_perms ), -4 );

				if ( in_array( $plugin_perms_octal, $too_permissive, true ) ) {
					$overly_permissive_plugins[] = array(
						'name'        => $plugin_data['Name'],
						'dir'         => basename( $plugin_dir ),
						'permissions' => $plugin_perms_octal,
					);
				}

				// Limit checking to avoid performance issues.
				if ( $plugin_dirs_checked >= 20 ) {
					break;
				}
			}
		}

		if ( ! empty( $overly_permissive_plugins ) ) {
			return array(
				'id'           => self::$slug . '-plugin-dirs-permissive',
				'title'        => __( 'Individual Plugin Directories Too Permissive', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of plugins with wrong permissions */
					_n(
						'%d plugin directory has overly permissive permissions (like leaving software installation folders unlocked). Fix these using your FTP client or hosting control panel.',
						'%d plugin directories have overly permissive permissions (like leaving software installation folders unlocked). Fix these using your FTP client or hosting control panel.',
						count( $overly_permissive_plugins ),
						'wpshadow'
					),
					count( $overly_permissive_plugins )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'plugins' => $overly_permissive_plugins,
					'recommended' => $recommended,
				),
			);
		}

		return null; // Permissions are good.
	}
}
