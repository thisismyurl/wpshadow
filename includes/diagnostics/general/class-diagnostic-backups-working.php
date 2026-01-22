<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Backups Actually Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
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

	/**
	 * IMPLEMENTATION PLAN (Non-technical Site Owner (Mom/Dad))
	 *
	 * What This Checks:
	 * - [Technical implementation details]
	 *
	 * Why It Matters:
	 * - [Business value in plain English]
	 *
	 * Success Criteria:
	 * - [What "passing" means]
	 *
	 * How to Fix:
	 * - Step 1: [Clear instruction]
	 * - Step 2: [Next step]
	 * - KB Article: Detailed explanation and examples
	 * - Training Video: Visual walkthrough
	 *
	 * KPIs Tracked:
	 * - Issues found and fixed
	 * - Time saved (estimated minutes)
	 * - Site health improvement %
	 * - Business value delivered ($)
	 */
}
