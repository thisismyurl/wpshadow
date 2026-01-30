<?php
/**
 * Admin Plugins Injecting Custom Modals Without Accessibility Features Diagnostic
 *
 * Detects custom modal scripts enqueued in the admin that do not depend on
 * WordPress accessibility helpers. Modals without proper a11y support can
 * trap keyboard users and break screen reader flows.
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
 * Admin Plugins Injecting Custom Modals Without Accessibility Features Class
 *
 * Looks for admin-enqueued scripts that likely add modal UIs but do not
 * depend on WordPress accessibility utilities (wp-a11y), suggesting the
 * modals may lack focus management and ARIA markup.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Plugins_Injecting_Custom_Modals_Without_Accessibility_Features extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-plugins-injecting-custom-modals-without-accessibility-features';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugins Injecting Custom Modals Without Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects custom modal scripts enqueued in admin without wp-a11y dependency';

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

		global $wp_scripts;

		if ( ! $wp_scripts || ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		$problematic = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			// Identify likely modal scripts by handle naming.
			if ( ! self::handle_suggests_modal( $handle ) ) {
				continue;
			}

			$deps = isset( $script->deps ) ? (array) $script->deps : array();

			// Skip native WP modal stacks.
			if ( in_array( 'thickbox', $deps, true ) || in_array( 'media-views', $deps, true ) ) {
				continue;
			}

			// If not depending on wp-a11y, flag for review.
			if ( ! in_array( 'wp-a11y', $deps, true ) ) {
				$problematic[] = array(
					'handle' => $handle,
					'deps'   => $deps,
					'src'    => isset( $script->src ) ? $script->src : '',
				);
			}
		}

		if ( empty( $problematic ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		$show       = array_slice( $problematic, 0, $max_items );

		foreach ( $show as $item ) {
			$items_list .= sprintf(
				"\n- %s (%s)",
				esc_html( $item['handle'] ),
				esc_html( ! empty( $item['src'] ) ? $item['src'] : __( 'local script', 'wpshadow' ) )
			);
		}

		if ( count( $problematic ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more scripts", 'wpshadow' ),
				count( $problematic ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of scripts, 2: list */
					__( 'Found %1$d admin script(s) that likely create custom modals but are not depending on wp-a11y. Modals should include focus management, ARIA labels, and trap focus for keyboard users.%2$s', 'wpshadow' ),
					count( $problematic ),
					$items_list
				),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-plugins-injecting-custom-modals-without-accessibility-features',
			'meta'         => array(
				'scripts' => $problematic,
			),
		);
	}

	/**
	 * Heuristic: decide if a handle suggests modal behavior.
	 *
	 * @since  1.2601.2148
	 * @param  string $handle Script handle.
	 * @return bool Whether the handle suggests a modal implementation.
	 */
	private static function handle_suggests_modal( string $handle ): bool {
		$keywords = array( 'modal', 'popup', 'dialog', 'lightbox' );

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $handle, $keyword ) ) {
				return true;
			}
		}

		return false;
	}
}
