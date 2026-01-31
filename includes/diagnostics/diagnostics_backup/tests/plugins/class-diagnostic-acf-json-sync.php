<?php
/**
 * ACF JSON Sync Diagnostic
 *
 * ACF JSON sync not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.451.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF JSON Sync Diagnostic Class
 *
 * @since 1.451.0000
 */
class Diagnostic_AcfJsonSync extends Diagnostic_Base {

	protected static $slug = 'acf-json-sync';
	protected static $title = 'ACF JSON Sync';
	protected static $description = 'ACF JSON sync not enabled';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: JSON save path configured.
		$json_save_path = apply_filters( 'acf/settings/save_json', false );
		if ( false === $json_save_path ) {
			$issues[] = 'JSON sync save path not configured';
		}

		// Check 2: JSON directory exists and writable.
		if ( false !== $json_save_path ) {
			if ( ! is_dir( $json_save_path ) ) {
				$issues[] = 'JSON save directory does not exist';
			} elseif ( ! is_writable( $json_save_path ) ) {
				$issues[] = 'JSON save directory not writable';
			}
		}

		// Check 3: Field groups in database vs JSON files.
		global $wpdb;
		$db_groups = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'acf-field-group',
				'publish'
			)
		);
		$json_files = 0;
		if ( false !== $json_save_path && is_dir( $json_save_path ) ) {
			$json_files = count( glob( $json_save_path . '/group_*.json' ) );
		}
		if ( $db_groups > 0 && 0 === $json_files ) {
			$issues[] = "{$db_groups} field groups in database but no JSON files (sync not working)";
		} elseif ( $json_files > 0 && abs( $db_groups - $json_files ) > 2 ) {
			$issues[] = "field group count mismatch (DB: {$db_groups}, JSON: {$json_files})";
		}

		// Check 4: JSON load path configured.
		$json_load_paths = apply_filters( 'acf/settings/load_json', array() );
		if ( empty( $json_load_paths ) && false !== $json_save_path ) {
			$issues[] = 'JSON load path not configured (JSON files will not be loaded)';
		}

		// Check 5: Modified field groups not synced.
		$modified_groups = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_modified > %s",
				'acf-field-group',
				'publish',
				date( 'Y-m-d H:i:s', strtotime( '-1 hour' ) )
			)
		);
		if ( $modified_groups > 0 && false !== $json_save_path && is_dir( $json_save_path ) ) {
			$recent_json_files = 0;
			foreach ( glob( $json_save_path . '/group_*.json' ) as $file ) {
				if ( filemtime( $file ) > strtotime( '-1 hour' ) ) {
					++$recent_json_files;
				}
			}
			if ( $recent_json_files < $modified_groups ) {
				$issues[] = "{$modified_groups} recently modified groups but only {$recent_json_files} JSON files updated";
			}
		}

		// Check 6: JSON files in version control.
		if ( false !== $json_save_path && is_dir( $json_save_path ) ) {
			$git_dir = dirname( $json_save_path );
			while ( $git_dir !== '/' && ! is_dir( $git_dir . '/.git' ) ) {
				$git_dir = dirname( $git_dir );
			}
			if ( is_dir( $git_dir . '/.git' ) ) {
				$gitignore = $git_dir . '/.gitignore';
				if ( file_exists( $gitignore ) ) {
					$gitignore_content = file_get_contents( $gitignore );
					if ( false !== strpos( $gitignore_content, basename( $json_save_path ) ) ) {
						$issues[] = 'JSON directory in .gitignore (field groups not version controlled)';
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF JSON sync issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acf-json-sync',
			);
		}

		return null;
	}
}
