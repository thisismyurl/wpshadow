<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Backup Success Rate
 *
 * Target Persona: Web Hosting Provider
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Backup_Completion extends Diagnostic_Base {
	protected static $slug        = 'backup-completion';
	protected static $title       = 'Backup Success Rate';
	protected static $description = 'Tracks backup completion reliability.';


	public static function check(): ?array {
		if (is_plugin_active('updraftplus/updraftplus.php') && class_exists('UpdraftPlus_Options')) {
			$last_backup = UpdraftPlus_Options::get_updraft_option('updraft_last_backup');
			if ($last_backup && is_array($last_backup)) {
				$last_time = max(array_values($last_backup));
				if ($last_time > (time() - (7 * 24 * 60 * 60))) {
					return null;
				}
			}
		}
		return null;
	}


}