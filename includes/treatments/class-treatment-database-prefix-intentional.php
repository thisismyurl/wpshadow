<?php
/**
 * Treatment: Database Prefix
 *
 * Provides step-by-step manual guidance for changing the WordPress database
 * table prefix from the default "wp_" to a custom value.
 *
 * WPShadow does not perform this operation automatically because a failed or
 * interrupted prefix change can break the entire WordPress installation. The
 * instructions below must be executed manually by a developer with database
 * access.
 *
 * Risk level: n/a (guidance only — DO NOT AUTOMATE)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns manual SQL guidance for changing the WordPress database prefix.
 */
class Treatment_Database_Prefix_Intentional extends Treatment_Base {

	/** @var string */
	protected static $slug = 'database-prefix-intentional';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'none';
	}

	/**
	 * Return detailed manual guidance for changing the DB prefix.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		global $wpdb;

		$current_prefix = $wpdb->prefix;

		return [
			'success' => false,
			'message' => sprintf(
				/* translators: %s: current database prefix */
				__(
					"WARNING: Changing the database prefix is a high-risk operation. A mistake can permanently break your site. Follow these steps EXACTLY and have a database backup before you begin.\n\n"
					. "Current prefix: '%s'\n\n"
					. "STEP 0 — Create a full database backup.\n"
					. "  Do NOT skip this step.\n\n"
					. "STEP 1 — Choose a new prefix (use letters, numbers, and underscores; end with _).\n"
					. "  Example: mywp7_\n\n"
					. "STEP 2 — Rename all tables in phpMyAdmin or via MySQL CLI.\n"
					. "  Run one SQL statement per table, replacing 'NEW_' with your chosen prefix:\n\n"
					. "  RENAME TABLE `%%s`posts TO `NEW_`posts;\n"
					. "  RENAME TABLE `%%s`postmeta TO `NEW_`postmeta;\n"
					. "  RENAME TABLE `%%s`comments TO `NEW_`comments;\n"
					. "  RENAME TABLE `%%s`commentmeta TO `NEW_`commentmeta;\n"
					. "  RENAME TABLE `%%s`users TO `NEW_`users;\n"
					. "  RENAME TABLE `%%s`usermeta TO `NEW_`usermeta;\n"
					. "  RENAME TABLE `%%s`terms TO `NEW_`terms;\n"
					. "  RENAME TABLE `%%s`term_taxonomy TO `NEW_`term_taxonomy;\n"
					. "  RENAME TABLE `%%s`term_relationships TO `NEW_`term_relationships;\n"
					. "  RENAME TABLE `%%s`termmeta TO `NEW_`termmeta;\n"
					. "  RENAME TABLE `%%s`options TO `NEW_`options;\n"
					. "  RENAME TABLE `%%s`links TO `NEW_`links;\n\n"
					. "STEP 3 — Update options table rows that reference the old prefix.\n\n"
					. "  UPDATE `NEW_`options SET option_name = REPLACE(option_name, '%%s', 'NEW_') WHERE option_name LIKE '%%s%%';\n\n"
					. "STEP 4 — Update usermeta rows that reference the old prefix.\n\n"
					. "  UPDATE `NEW_`usermeta SET meta_key = REPLACE(meta_key, '%%s', 'NEW_') WHERE meta_key LIKE '%%s%%';\n\n"
					. "STEP 5 — Edit wp-config.php.\n"
					. "  Change: \$table_prefix = '%%s';\n"
					. "  To:     \$table_prefix = 'NEW_';\n\n"
					. "STEP 6 — Test your site.\n"
					. "  Visit your site and wp-admin. Check for any errors.\n"
					. "  If the site breaks, restore your database backup.\n\n"
					. "ALTERNATIVE:\n"
					. "  Several plugins automate this process with rollback support:\n"
					. "  'Brozzme DB Prefix & Tools Addons' or 'Change DB Prefix'.\n"
					. "  These plugins handle all renaming steps and are less error-prone.\n\n"
					. "Re-run the WPShadow scan after completing the prefix change.",
					'wpshadow'
				),
				$current_prefix,
				$current_prefix, $current_prefix, // rows 2–3 (RENAME TABLE)
				$current_prefix, $current_prefix,
				$current_prefix, $current_prefix,
				$current_prefix, $current_prefix,
				$current_prefix, $current_prefix,
				$current_prefix, $current_prefix,
				$current_prefix,                  // UPDATE options
				$current_prefix, $current_prefix, // WHERE LIKE
				$current_prefix,                  // UPDATE usermeta
				$current_prefix, $current_prefix, // WHERE LIKE
				$current_prefix                   // STEP 5 wp-config
			),
		];
	}

	/**
	 * No state to undo (guidance only).
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		return [
			'success' => true,
			'message' => __( 'This is a guidance-only treatment — no changes were made by WPShadow.', 'wpshadow' ),
		];
	}
}
