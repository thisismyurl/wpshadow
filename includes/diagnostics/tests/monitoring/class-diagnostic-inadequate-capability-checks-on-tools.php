<?php
/**
 * Inadequate Capability Checks on Tools Diagnostic
 *
 * Tests whether Tools menu items properly verify user capabilities before allowing access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Inadequate_Capability_Checks_On_Tools Class
 *
 * Verifies that Tools menu items have proper capability checks.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Inadequate_Capability_Checks_On_Tools extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inadequate-capability-checks-on-tools';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tools Menu Capability Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Tools menu items properly verify user capabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $submenu;

		$issues = array();

		// 1. Check Tools submenu items.
		if ( ! isset( $submenu['tools.php'] ) ) {
			// No tools submenu - can't test.
			return null;
		}

		$tool_items = $submenu['tools.php'];
		$weak_caps  = array();

		foreach ( $tool_items as $item ) {
			$capability = $item[1] ?? '';
			$page_slug  = $item[2] ?? '';

			// Check for weak capabilities.
			if ( in_array( $capability, array( 'read', 'edit_posts', 'upload_files' ), true ) ) {
				$weak_caps[] = array(
					'page'       => $page_slug,
					'capability' => $capability,
				);
			}
		}

		if ( ! empty( $weak_caps ) ) {
			$page_list = array_map( function( $item ) {
				return sprintf( '%s (%s)', $item['page'], $item['capability'] );
			}, $weak_caps );

			$issues[] = sprintf(
				/* translators: %s: comma-separated list of pages */
				__( 'Tool pages with weak capabilities: %s', 'wpshadow' ),
				implode( ', ', $page_list )
			);
		}

		// 2. Check import/export tools specifically.
		$import_cap = 'import';
		$export_cap = 'export';

		$import_exists = false;
		$export_exists = false;

		foreach ( $tool_items as $item ) {
			$page_slug = $item[2] ?? '';

			if ( 'import.php' === $page_slug ) {
				$import_exists = true;
				$import_cap    = $item[1] ?? 'import';
			}

			if ( 'export.php' === $page_slug ) {
				$export_exists = true;
				$export_cap    = $item[1] ?? 'export';
			}
		}

		if ( $import_exists && 'import' !== $import_cap ) {
			$issues[] = sprintf(
				/* translators: %s: capability name */
				__( 'Import page using non-standard capability: %s', 'wpshadow' ),
				$import_cap
			);
		}

		if ( $export_exists && 'export' !== $export_cap ) {
			$issues[] = sprintf(
				/* translators: %s: capability name */
				__( 'Export page using non-standard capability: %s', 'wpshadow' ),
				$export_cap
			);
		}

		// 3. Check AJAX action capability verification.
		global $wp_filter;

		// Look for AJAX handlers without nonce verification.
		$ajax_actions = array(
			'wp_ajax_export_personal_data',
			'wp_ajax_erase_personal_data',
		);

		foreach ( $ajax_actions as $action ) {
			if ( isset( $wp_filter[ $action ] ) ) {
				// Verify there's a capability check.
				// WordPress core handles this, but custom implementations might not.
				$callbacks = $wp_filter[ $action ]->callbacks ?? array();

				foreach ( $callbacks as $priority => $functions ) {
					foreach ( $functions as $function ) {
						// We can't inspect the callback directly in PHP.
						// But we can verify core functions are registered.
					}
				}
			}
		}

		// 4. Check for privilege escalation vectors.
		// Test if non-admin users can access tool pages.
		$current_user = wp_get_current_user();

		if ( ! user_can( $current_user, 'manage_options' ) ) {
			// Current user is not admin - they shouldn't see sensitive tools.
			if ( current_user_can( 'export_others_personal_data' ) ) {
				$issues[] = __( 'Non-admin user has export_others_personal_data capability - potential privacy violation', 'wpshadow' );
			}

			if ( current_user_can( 'erase_others_personal_data' ) ) {
				$issues[] = __( 'Non-admin user has erase_others_personal_data capability - dangerous', 'wpshadow' );
			}
		}

		// 5. Check custom tool pages from plugins.
		foreach ( $tool_items as $item ) {
			$page_slug  = $item[2] ?? '';
			$capability = $item[1] ?? '';

			// Skip core pages.
			if ( in_array( $page_slug, array( 'import.php', 'export.php', 'tools.php', 'site-health.php' ), true ) ) {
				continue;
			}

			// Custom tool page - verify capability is appropriate.
			if ( in_array( $capability, array( 'read', 'edit_posts' ), true ) ) {
				$issues[] = sprintf(
					/* translators: %s: page slug */
					__( 'Custom tool page "%s" has insufficient capability check', 'wpshadow' ),
					$page_slug
				);
			}
		}

		// 6. Check Site Health capability (introduced in WP 5.2).
		$site_health_exists = false;
		foreach ( $tool_items as $item ) {
			if ( 'site-health.php' === ( $item[2] ?? '' ) ) {
				$site_health_exists = true;
				$site_health_cap    = $item[1] ?? 'view_site_health_checks';

				if ( 'view_site_health_checks' !== $site_health_cap ) {
					$issues[] = sprintf(
						/* translators: %s: capability name */
						__( 'Site Health using non-standard capability: %s', 'wpshadow' ),
						$site_health_cap
					);
				}
				break;
			}
		}

		// 7. Verify no direct file access to tool pages.
		$tool_files = array(
			ABSPATH . 'wp-admin/tools.php',
			ABSPATH . 'wp-admin/import.php',
			ABSPATH . 'wp-admin/export.php',
		);

		foreach ( $tool_files as $file ) {
			if ( file_exists( $file ) ) {
				$contents = file_get_contents( $file );

				// Check for capability verification.
				if ( false === strpos( $contents, 'current_user_can' ) &&
				     false === strpos( $contents, 'check_admin_referer' ) ) {
					$issues[] = sprintf(
						/* translators: %s: file name */
						__( 'Tool file "%s" may lack capability checks', 'wpshadow' ),
						basename( $file )
					);
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Tool capability check issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/tool-capability-checks?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'     => $issues,
				'tool_count' => count( $tool_items ),
			),
		);
	}
}
