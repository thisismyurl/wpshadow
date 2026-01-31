<?php
/**
 * Admin Missing ARIA Label Attributes On Admin Icons Diagnostic
 *
 * Detects icon-only buttons in the admin without aria-label attributes.
 * Icons without accessible names are unusable by screen reader users.
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
 * Admin Missing ARIA Label Attributes On Admin Icons Diagnostic Class
 *
 * Scans for buttons/controls that use icons without aria-label, making them
 * inaccessible to screen reader users.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Aria_Label_Attributes_On_Admin_Icons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-missing-aria-label-attributes-on-admin-icons';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing ARIA Label Attributes On Admin Icons';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects icon-only buttons without aria-label attributes in admin UI';

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

		// This diagnostic would ideally parse DOM to check for icon buttons
		// without aria-label. For foundation, check for known patterns.

		global $wp_scripts;

		if ( ! $wp_scripts || ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		$potential_issues = array();

		// Look for scripts that create icon buttons but might not add aria-label.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			// Skip WP core which handles this correctly.
			if ( strpos( $handle, 'wp-' ) === 0 ) {
				continue;
			}

			// Look for scripts that create interactive elements.
			if ( self::creates_interactive_elements( $handle ) ) {
				$potential_issues[] = array(
					'handle' => $handle,
					'reason' => __( 'May create icon buttons; verify aria-label is present.', 'wpshadow' ),
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
				__( 'Found %1$d script(s) that may create icon buttons without accessible names. Icon-only buttons must have aria-label or equivalent text alternative.%2$s', 'wpshadow' ),
				count( $potential_issues ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-missing-aria-label-attributes-on-admin-icons',
			'meta'         => array(
				'potential_issues' => $potential_issues,
			),
		);
	}

	/**
	 * Heuristic to determine if a script creates interactive elements.
	 *
	 * @since  1.2601.2148
	 * @param  string $handle Script handle.
	 * @return bool Whether it might create interactive elements.
	 */
	private static function creates_interactive_elements( string $handle ): bool {
		$keywords = array( 'button', 'toggle', 'control', 'interactive', 'modal', 'dialog', 'menu', 'dropdown' );

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $handle, $keyword ) ) {
				return true;
			}
		}

		return false;
	}
}
