<?php

declare(strict_types=1);

namespace WPShadow\Detectors;

use WPShadow\CoreSupport\WPSHADOW_Issue_Detection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Detector_Backup_Plugin extends WPSHADOW_Issue_Detection {

	private const BACKUP_PLUGINS = array(
		'backwpup/backwpup.php',
		'duplicator/duplicator.php',
		'updraftplus/updraftplus.php',
		'blogvault-automated-backup-restoration/blogvault.php',
		'jetpack/jetpack.php',
		'vaultpress/vaultpress.php',
		'backupbuddy/backupbuddy.php',
	);

	public function __construct() {
		parent::__construct(
			'backup-plugin',
			'No Backup Plugin Detected',
			'No backup plugin is active on this site',
			self::SEVERITY_HIGH,
			false
		);
	}

	public function run(): int {
		if ( $this->has_backup_plugin() ) {
			return 0;
		}

		$this->add_issue(
			array(
				'id'            => 'backup-plugin-001',
				'detector_id'   => $this->detector_id,
				'severity'      => self::SEVERITY_HIGH,
				'title'         => 'No Backup Plugin Active',
				'description'   => 'No backup plugin is active on this site. Regular backups are critical for disaster recovery and security.',
				'resolution'    => 'Install and configure a backup plugin such as UpdraftPlus, BackWPup, or Duplicator. Set up automatic daily backups.',
				'confidence'    => 0.99,
				'auto_fixable'  => false,
				'data'          => array(
					'active_plugins' => get_option( 'active_plugins', array() ),
				),
			)
		);

		return 1;
	}

	public function get_issue_count(): int {
		return $this->has_backup_plugin() ? 0 : 1;
	}

	private function has_backup_plugin(): bool {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( self::BACKUP_PLUGINS as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return true;
			}
		}

		if ( is_multisite() ) {
			$network_active = get_site_option( 'active_sitewide_plugins', array() );
			foreach ( self::BACKUP_PLUGINS as $plugin_file ) {
				if ( isset( $network_active[ $plugin_file ] ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
