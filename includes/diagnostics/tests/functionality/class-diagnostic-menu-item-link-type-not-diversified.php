<?php
/**
 * Menu Item Link Type Not Diversified Diagnostic
 *
 * Checks if menu links use different link types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Item Link Type Not Diversified Diagnostic Class
 *
 * Detects menu using single link type.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Menu_Item_Link_Type_Not_Diversified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'menu-item-link-type-not-diversified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Item Link Type Not Diversified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if menu items use diverse link types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check menu item types
		$menu_items = $wpdb->get_results(
			"SELECT pm.meta_value as type FROM {$wpdb->postmeta} pm
			 WHERE pm.meta_key = '_menu_item_type'
			 GROUP BY pm.meta_value"
		);

		if ( count( $menu_items ) === 1 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Menu items use only a single link type. Diversify with posts, pages, categories, and custom URLs for better navigation.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/menu-item-link-type-not-diversified',
			);
		}

		return null;
	}
}
