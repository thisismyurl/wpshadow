<?php
/**
 * Media Backup Status Treatment
 *
 * Checks whether media files are included in backup routines
 * and validates backup plugin coverage.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Backup_Status Class
 *
 * Detects whether a backup plugin is active and configured
 * to include the uploads directory.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_Backup_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-backup-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Backup Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media files are included in backup routines';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$backup_plugins = array(
			'updraftplus/updraftplus.php'                => 'UpdraftPlus',
			'backupwordpress/backupwordpress.php'        => 'BackUpWordPress',
			'backwpup/backwpup.php'                      => 'BackWPup',
			'wpvivid-backuprestore/wpvivid-backuprestore.php' => 'WPvivid',
			'duplicator/duplicator.php'                  => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'blogvault/blogvault.php'                    => 'BlogVault',
		);

		$active_plugins = array();
		foreach ( $backup_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_plugins[] = $plugin_name;
			}
		}

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No backup plugin detected; media files may not be protected', 'wpshadow' );
		}

		// Check for common upload-inclusion options in popular plugins.
		$uploads_included = false;
		if ( is_plugin_active( 'updraftplus/updraftplus.php' ) ) {
			$uploads_included = (bool) get_option( 'updraft_include_uploads', true );
		} elseif ( is_plugin_active( 'backwpup/backwpup.php' ) ) {
			$uploads_included = (bool) get_option( 'backwpup_cfg_uploads', true );
		} elseif ( is_plugin_active( 'wpvivid-backuprestore/wpvivid-backuprestore.php' ) ) {
			$uploads_included = (bool) get_option( 'wpvivid_include_uploads', true );
		}

		if ( ! empty( $active_plugins ) && ! $uploads_included ) {
			$issues[] = __( 'Backup plugin is active but uploads may not be included; verify media backups', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-backup-status',
			);
		}

		return null;
	}
}
