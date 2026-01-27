<?php
/**
 * Admin Inline JS Inserted By Plugins In Admin Pages Diagnostic
 *
 * Detects inline JavaScript inserted directly by plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Inline JS Inserted By Plugins In Admin Pages Diagnostic Class
 *
 * Scans for inline JavaScript blocks inserted by plugins in admin pages,
 * which indicates improper asset loading practices.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Inline_Js_Inserted_By_Plugins_In_Admin_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-inline-js-inserted-by-plugins-in-admin-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline JS Inserted By Plugins In Admin Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inline JavaScript inserted directly by plugins in admin pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( 'index.php' );
		
		if ( false === $html ) {
			return null;
		}

		$inline_scripts_count = preg_match_all( '/<script[^>]*>(.*?)<\/script>/is', $html, $script_matches );

		if ( $inline_scripts_count > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d inline script blocks in admin. This can slow page load and cause security issues.', 'wpshadow' ),
					$inline_scripts_count
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		$plugin_inline_scripts = array();

		// Check for inline scripts registered by plugins (not core).
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			$core_handles = array(
				'jquery',
				'jquery-core',
				'jquery-migrate',
				'wp-hooks',
				'wp-util',
				'wp-i18n',
				'wp-dom-ready',
				'wp-element',
				'wp-blocks',
				'wp-editor',
				'wp-data',
				'wp-notices',
				'wp-api-fetch',
				'common',
				'user-profile',
				'password-strength-meter',
				'user-suggest',
				'admin-bar',
				'nav-menu',
				'postbox',
				'dashboard',
				'customize-controls',
				'customize-preview',
				'customize-loader',
			);

			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				// Skip core WordPress scripts.
				if ( in_array( $handle, $core_handles, true ) ) {
					continue;
				}

				// Skip scripts without inline content.
				$has_inline = false;

				if ( isset( $script_obj->extra['before'] ) && is_array( $script_obj->extra['before'] ) && ! empty( $script_obj->extra['before'] ) ) {
					$has_inline = true;
				}

				if ( isset( $script_obj->extra['after'] ) && is_array( $script_obj->extra['after'] ) && ! empty( $script_obj->extra['after'] ) ) {
					$has_inline = true;
				}

				if ( $has_inline ) {
					// Get size of inline content.
					$size = 0;

					if ( isset( $script_obj->extra['before'] ) && is_array( $script_obj->extra['before'] ) ) {
						foreach ( $script_obj->extra['before'] as $code ) {
							$size += strlen( (string) $code );
						}
					}

					if ( isset( $script_obj->extra['after'] ) && is_array( $script_obj->extra['after'] ) ) {
						foreach ( $script_obj->extra['after'] as $code ) {
							$size += strlen( (string) $code );
						}
					}

					$plugin_inline_scripts[] = array(
						'handle'  => $handle,
						'size'    => $size,
						'size_kb' => round( $size / 1024, 2 ),
						'reason'  => __( 'Plugin registered script with inline code', 'wpshadow' ),
					);
				}
			}
		}

		if ( empty( $plugin_inline_scripts ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $plugin_inline_scripts, 0, $max_items ) as $script ) {
			$items_list .= sprintf(
				"\n- %s: %s KB",
				esc_html( $script['handle'] ),
				$script['size_kb']
			);
		}

		if ( count( $plugin_inline_scripts ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more plugin scripts with inline code", 'wpshadow' ),
				count( $plugin_inline_scripts ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d plugin script(s) with inline JavaScript. Plugins should use wp_enqueue_script() with external files instead of inserting inline code. This improves caching, performance, and security.%2$s', 'wpshadow' ),
				count( $plugin_inline_scripts ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-inline-js-inserted-by-plugins-in-admin-pages',
			'meta'         => array(
				'plugin_scripts' => $plugin_inline_scripts,
			),
		);
	}
}
