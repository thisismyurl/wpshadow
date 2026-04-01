<?php
/**
 * Plugin Admin Page Security Diagnostic
 *
 * Checks security of plugin admin pages and settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Admin Page Security Diagnostic Class
 *
 * Analyzes plugin admin pages for security vulnerabilities.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Admin_Page_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-admin-page-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Admin Page Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin admin page security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $menu, $submenu;

		$issues = array();
		$insecure_pages = array();

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		// Check registered admin pages for capability requirements.
		if ( ! empty( $menu ) ) {
			foreach ( $menu as $item ) {
				if ( isset( $item[1] ) && isset( $item[2] ) ) {
					$capability = $item[1];
					$slug = $item[2];

					// Check if page allows read capability (too permissive).
					if ( in_array( $capability, array( 'read', 'subscriber', 'contributor' ), true ) ) {
						// Check if it's a plugin page.
						if ( strpos( $slug, 'admin.php?page=' ) !== false || strpos( $slug, '.php' ) !== false ) {
							$insecure_pages[] = array(
								'page'       => $item[0],
								'capability' => $capability,
								'slug'       => $slug,
							);
						}
					}
				}
			}
		}

		// Check submenus too.
		if ( ! empty( $submenu ) ) {
			foreach ( $submenu as $parent => $items ) {
				foreach ( $items as $item ) {
					if ( isset( $item[1] ) && isset( $item[2] ) ) {
						$capability = $item[1];
						$slug = $item[2];

						if ( in_array( $capability, array( 'read', 'subscriber', 'contributor' ), true ) ) {
							$insecure_pages[] = array(
								'page'       => $item[0],
								'capability' => $capability,
								'slug'       => $slug,
							);
						}
					}
				}
			}
		}

		if ( ! empty( $insecure_pages ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of insecure pages */
				_n(
					'%d plugin admin page has overly permissive capabilities',
					'%d plugin admin pages have overly permissive capabilities',
					count( $insecure_pages ),
					'wpshadow'
				),
				count( $insecure_pages )
			);
		}

		// Check for plugins with AJAX handlers without nonce checks.
		$ajax_actions = array();
		if ( isset( $GLOBALS['wp_filter']['wp_ajax_nopriv_*'] ) ) {
			foreach ( $GLOBALS['wp_filter'] as $hook => $callbacks ) {
				if ( strpos( $hook, 'wp_ajax_' ) === 0 ) {
					$ajax_actions[] = str_replace( 'wp_ajax_', '', $hook );
				}
			}
		}

		if ( count( $ajax_actions ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of AJAX actions */
				__( '%d AJAX actions registered (review for security)', 'wpshadow' ),
				count( $ajax_actions )
			);
		}

		// Check for publicly accessible plugin files.
		$plugin_dir = WP_PLUGIN_DIR;
		$exposed_files = array();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_folder = dirname( $plugin_file );
			$config_files = glob( $plugin_dir . '/' . $plugin_folder . '/{config,settings,credentials}*.{php,json,xml,yml,yaml}', GLOB_BRACE );

			foreach ( $config_files as $file ) {
				$content = file_get_contents( $file, false, null, 0, 200 );
				// Check if file lacks ABSPATH check.
				if ( ! preg_match( '/defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)|ABSPATH.*die/i', $content ) ) {
					$exposed_files[] = basename( dirname( $file ) ) . '/' . basename( $file );
				}
			}

			if ( count( $exposed_files ) > 5 ) {
				break; // Limit check.
			}
		}

		if ( ! empty( $exposed_files ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of exposed files */
				_n(
					'%d plugin config file lacks direct access protection',
					'%d plugin config files lack direct access protection',
					count( $exposed_files ),
					'wpshadow'
				),
				count( $exposed_files )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Plugin admin pages may have security vulnerabilities', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'     => array(
					'insecure_pages' => array_slice( $insecure_pages, 0, 10 ),
					'exposed_files'  => $exposed_files,
					'ajax_count'     => count( $ajax_actions ),
					'issues'         => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/plugin-admin-page-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
