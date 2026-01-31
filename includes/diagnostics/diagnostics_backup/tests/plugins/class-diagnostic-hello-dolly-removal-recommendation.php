<?php
/**
 * Hello Dolly Removal Recommendation Diagnostic
 *
 * Hello Dolly Removal Recommendation issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1442.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hello Dolly Removal Recommendation Diagnostic Class
 *
 * @since 1.1442.0000
 */
class Diagnostic_HelloDollyRemovalRecommendation extends Diagnostic_Base {

	protected static $slug = 'hello-dolly-removal-recommendation';
	protected static $title = 'Hello Dolly Removal Recommendation';
	protected static $description = 'Hello Dolly Removal Recommendation issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Hello Dolly is installed or active
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$issues = array();

		// Check 1: Verify Hello Dolly is not active
		if ( is_plugin_active( 'hello-dolly/hello.php' ) || is_plugin_active( 'hello.php' ) ) {
			$issues[] = 'Hello Dolly plugin is active (no functional benefit)';
		}

		// Check 2: Check if plugin is installed but inactive
		$all_plugins = get_plugins();
		if ( isset( $all_plugins['hello-dolly/hello.php'] ) || isset( $all_plugins['hello.php'] ) ) {
			$issues[] = 'Hello Dolly plugin installed (should be removed)';
		}

		// Check 3: Verify no custom modifications to Hello Dolly
		if ( file_exists( WP_PLUGIN_DIR . '/hello-dolly/hello.php' ) || file_exists( WP_PLUGIN_DIR . '/hello.php' ) ) {
			$issues[] = 'Hello Dolly files present in plugins directory';
		}

		// Check 4: Check for hello function existence
		if ( function_exists( 'hello_dolly' ) ) {
			$issues[] = 'Hello Dolly functions detected in codebase';
		}

		// Check 5: Verify admin dashboard widget not present
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['hello-dolly'] ) ) {
			$issues[] = 'Hello Dolly dashboard widget registered';
		}

		// Check 6: Check for plugin updates (waste of resources)
		if ( ! empty( $all_plugins ) ) {
			foreach ( $all_plugins as $plugin_path => $plugin_data ) {
				if ( strpos( $plugin_path, 'hello' ) !== false ) {
					$update_check = get_site_transient( 'update_plugins' );
					if ( isset( $update_check->response[ $plugin_path ] ) ) {
						$issues[] = 'System checking for Hello Dolly updates unnecessarily';
						break;
					}
				}
			}
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Hello Dolly issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hello-dolly-removal-recommendation',
			);
		}

		return null;
	}
}
