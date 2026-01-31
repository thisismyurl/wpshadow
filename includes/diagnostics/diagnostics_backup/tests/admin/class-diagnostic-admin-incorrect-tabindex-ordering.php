<?php
/**
 * Admin Incorrect Tabindex Ordering Diagnostic
 *
 * Detects improper tabindex values in admin forms that can break logical
 * keyboard navigation flow for users relying on Tab key navigation.
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
 * Admin Incorrect Tabindex Ordering Diagnostic Class
 *
 * Checks for improper tabindex values (especially positive values > 0)
 * which can disrupt expected keyboard navigation order.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Incorrect_Tabindex_Ordering extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-incorrect-tabindex-ordering';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incorrect Tabindex Ordering';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects improper tabindex values that break keyboard navigation flow';

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

		// Check for scripts that might be setting tabindex values.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			// Skip WP core which manages tabindex correctly.
			if ( strpos( $handle, 'wp-' ) === 0 ) {
				continue;
			}

			// Look for scripts that might manipulate focus/tabindex.
			if ( self::might_manipulate_tabindex( $handle ) ) {
				$potential_issues[] = array(
					'handle' => $handle,
					'reason' => __( 'May manipulate tabindex; avoid positive values (>0) as they override natural tab order.', 'wpshadow' ),
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
				__( 'Found %1$d script(s) that may set improper tabindex values. Positive tabindex values (>0) can break natural keyboard navigation. Use tabindex="-1" for programmatic focus, or omit tabindex to follow DOM order.%2$s', 'wpshadow' ),
				count( $potential_issues ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-incorrect-tabindex-ordering',
			'meta'         => array(
				'potential_issues' => $potential_issues,
			),
		);
	}

	/**
	 * Heuristic to determine if a script might manipulate tabindex.
	 *
	 * @since  1.2601.2148
	 * @param  string $handle Script handle.
	 * @return bool Whether it might manipulate tabindex.
	 */
	private static function might_manipulate_tabindex( string $handle ): bool {
		$keywords = array( 'focus', 'tabindex', 'keyboard', 'navigation', 'menu', 'modal', 'dialog', 'trap' );

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $handle, $keyword ) ) {
				return true;
			}
		}

		return false;
	}
}
