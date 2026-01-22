<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Backups Set Up?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Backups_Configured extends Diagnostic_Base {
	protected static $slug        = 'backups-configured';
	protected static $title       = 'Are Backups Set Up?';
	protected static $description = 'Verifies automatic backup system is configured.';


	public static function check(): ?array {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'backup-backup/backup-backup.php',
			'duplicator/duplicator.php',
		);
		foreach ($backup_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				return null;
			}
		}
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'No backup plugin detected.',
			'color'         => '#f44336',
			'bg_color'      => '#ffebee',
			'kb_link'       => 'https://wpshadow.com/kb/backups-configured/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=backups-configured',
			'training_link' => 'https://wpshadow.com/training/backups-configured/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Core',
			'priority'      => 1,
		);
	}

}
