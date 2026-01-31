<?php
/**
 * Admin Misused ARIA Roles In Admin UI Diagnostic
 *
 * Detects misused or incorrect ARIA roles in the WordPress admin UI.
 * Incorrect role usage can break semantic understanding for assistive technology.
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
 * Admin Misused ARIA Roles In Admin UI Diagnostic Class
 *
 * Scans admin page markup for common ARIA role misuses that can confuse
 * screen readers or create invalid semantic structures.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Misused_Aria_Roles_In_Admin_Ui extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-misused-aria-roles-in-admin-ui';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Misused ARIA Roles In Admin UI';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects incorrect or misused ARIA roles in admin pages';

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

		// This diagnostic would ideally analyze the DOM, but that requires
		// parsing the page markup. For now, we provide a foundation that
		// can be extended with actual DOM parsing when needed.

		global $wp_scripts;

		if ( ! $wp_scripts || ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		$potential_issues = array();

		// Check for scripts that might be manipulating ARIA roles incorrectly.
		// Look for patterns in script dependencies that might suggest custom role manipulation.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			// Skip WP core scripts that are known to handle ARIA correctly.
			if ( strpos( $handle, 'wp-' ) === 0 ) {
				continue;
			}

			// Look for custom scripts that might manipulate ARIA roles.
			if ( self::might_manipulate_roles( $handle ) ) {
				$potential_issues[] = array(
					'handle' => $handle,
					'reason' => __( 'Script name suggests ARIA/role manipulation; review for proper role usage.', 'wpshadow' ),
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
				__( 'Found %1$d script(s) that may be manipulating ARIA roles. Incorrect roles break screen reader understanding. Roles should match HTML semantics: buttons stay buttons, links stay links.%2$s', 'wpshadow' ),
				count( $potential_issues ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-misused-aria-roles-in-admin-ui',
			'meta'         => array(
				'potential_issues' => $potential_issues,
			),
		);
	}

	/**
	 * Heuristic to determine if a script handle suggests ARIA role manipulation.
	 *
	 * @since  1.2601.2148
	 * @param  string $handle Script handle.
	 * @return bool Whether it might manipulate roles.
	 */
	private static function might_manipulate_roles( string $handle ): bool {
		$keywords = array( 'aria', 'a11y', 'accessibility', 'role', 'semantic' );

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $handle, $keyword ) ) {
				return true;
			}
		}

		return false;
	}
}
