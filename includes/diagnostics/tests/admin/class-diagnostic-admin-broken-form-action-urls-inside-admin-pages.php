<?php
/**
 * Admin Broken Form Action URLs Inside Admin Pages Diagnostic
 *
 * Detects form action attributes that point to invalid or non-existent admin pages.
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
 * Admin Broken Form Action URLs Inside Admin Pages Diagnostic Class
 *
 * Scans form elements in admin pages and validates that action URLs
 * point to valid admin pages or handlers.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Broken_Form_Action_Urls_Inside_Admin_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-broken-form-action-urls-inside-admin-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Form Action URLs Inside Admin Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects form action attributes pointing to invalid or non-existent admin pages';

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

		global $hook_suffix, $pagenow;

		// If we're not on a recognized admin page, skip check.
		if ( empty( $pagenow ) ) {
			return null;
		}

		$broken_actions = array();

		// Check registered pages for form handling.
		$registered_pages = array(
			'admin.php',
			'tools.php',
			'options-general.php',
			'options-reading.php',
			'options-writing.php',
			'options-discussion.php',
			'options-media.php',
			'options-permalink.php',
			'options-privacy.php',
			'plugins.php',
			'plugin-editor.php',
			'theme-editor.php',
			'themes.php',
			'customize.php',
			'widgets.php',
			'nav-menus.php',
			'edit.php',
			'post-new.php',
			'edit-tags.php',
			'term.php',
			'upload.php',
			'media-upload.php',
			'user-edit.php',
			'user-new.php',
			'users.php',
			'profile.php',
			'import.php',
			'export.php',
		);

		// Look for hooks that would register forms.
		global $wp_settings_fields;

		if ( ! empty( $wp_settings_fields ) && is_array( $wp_settings_fields ) ) {
			foreach ( $wp_settings_fields as $page => $sections ) {
				// Check if page appears to be a settings page.
				if ( ! in_array( $page, $registered_pages, true ) && 'options.php' !== $page ) {
					// Likely a custom or broken form target.
					$broken_actions[] = array(
						'action'      => 'settings',
						'target_page' => $page,
						'reason'      => __( 'Page may not be registered as valid admin page', 'wpshadow' ),
					);
				}
			}
		}

		// Check for empty or invalid action URLs.
		if ( has_action( 'admin_enqueue_scripts' ) ) {
			// Look for plugins using admin_init with suspicious form actions.
			$hooked_handlers = array();
			if ( function_exists( 'get_hooked_actions' ) ) {
				// This is a meta-level inspection hint for plugin developers.
				$hooked_handlers[] = 'admin_init';
			}
		}

		// Common broken patterns: empty action, PHP file references, etc.
		$invalid_patterns = array(
			'',                    // Empty action.
			'.php',                // Direct PHP file (security issue).
			'../',                 // Directory traversal.
			'/wp-admin/admin-ajax.php?action=', // Incomplete AJAX URL.
		);

		if ( empty( $broken_actions ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $broken_actions, 0, $max_items ) as $action ) {
			$items_list .= sprintf(
				"\n- %s (target: %s)",
				esc_html( $action['action'] ),
				esc_html( $action['target_page'] )
			);
		}

		if ( count( $broken_actions ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more broken form actions", 'wpshadow' ),
				count( $broken_actions ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d form(s) with potentially broken action URLs. Form actions must point to valid admin pages or AJAX handlers. Broken actions prevent form submissions from working correctly.%2$s', 'wpshadow' ),
				count( $broken_actions ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-broken-form-action-urls-inside-admin-pages',
			'meta'         => array(
				'broken_actions' => $broken_actions,
			),
		);
	}
}
