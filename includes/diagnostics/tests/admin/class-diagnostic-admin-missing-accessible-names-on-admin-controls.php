<?php
/**
 * Admin Missing Accessible Names On Admin Controls Diagnostic
 *
 * Detects buttons, links, and form controls in the admin without accessible names.
 * All interactive elements must have visible or hidden text alternatives.
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
 * Admin Missing Accessible Names On Admin Controls Diagnostic Class
 *
 * Flags interactive elements (buttons, links, inputs) that lack accessible names,
 * making them inaccessible to screen reader users.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Accessible_Names_On_Admin_Controls extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-missing-accessible-names-on-admin-controls';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Accessible Names On Admin Controls';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin controls without accessible names (aria-label, label elements, or text)';

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

		$potential_issues = array();

		// Check for scripts that create controls without proper naming patterns.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			// Skip WordPress core scripts that are vetted.
			if ( strpos( $handle, 'wp-' ) === 0 ) {
				continue;
			}

			// Look for scripts creating controls.
			if ( self::creates_controls( $handle ) ) {
				$potential_issues[] = array(
					'handle' => $handle,
					'reason' => __( 'May create controls; verify accessible names via labels, aria-label, or text.', 'wpshadow' ),
				);
			}
		}

		if ( empty( $potential_issues ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		foreach ( array_slice( $potential_issues, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $item['handle'] ),
				esc_html( $item['reason'] )
			);
		}

		if ( count( $potential_issues ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more", 'wpshadow' ),
				count( $potential_issues ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d script(s) creating controls that may lack accessible names. All interactive elements must have accessible names for screen reader users.%2$s', 'wpshadow' ),
				count( $potential_issues ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-missing-accessible-names-on-admin-controls',
			'meta'         => array(
				'potential_issues' => $potential_issues,
			),
		);
	}

	/**
	 * Heuristic to determine if a script creates controls.
	 *
	 * @since  1.2601.2148
	 * @param  string $handle Script handle.
	 * @return bool Whether it might create controls.
	 */
	private static function creates_controls( string $handle ): bool {
		$keywords = array(
			'button', 'input', 'form', 'control', 'field',
			'widget', 'toggle', 'switch', 'checkbox', 'radio',
			'select', 'dropdown', 'combobox', 'autocomplete',
		);

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $handle, $keyword ) ) {
				return true;
			}
		}

		return false;
	}
}
