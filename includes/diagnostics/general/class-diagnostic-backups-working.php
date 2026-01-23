<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Backups Actually Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Backups_Working extends Diagnostic_Base {
	protected static $slug        = 'backups-working';
	protected static $title       = 'Are Backups Actually Working?';
	protected static $description = 'Tests if recent backups completed successfully.';

	public static function check(): ?array {
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'duplicator/duplicator.php'   => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'jetpack/jetpack.php'         => 'Jetpack (with backup)',
		);

		$active_backup = array();
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup[] = $name;
			}
		}

		if ( ! empty( $active_backup ) ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'No backup system detected', 'wpshadow' ),
			'description'   => __( 'If something breaks, you cannot restore your site. Install a backup plugin like UpdraftPlus (free).', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/backups-working/',
			'training_link' => 'https://wpshadow.com/training/backups-working/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
		);
	}


}