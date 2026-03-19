<?php
/**
 * Menu Item Post Associations Treatment
 *
 * Validates menu items correctly link to posts/pages. Tests menu-post relationship
 * integrity and detects broken menu item references.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Item Post Associations Treatment Class
 *
 * Checks for broken menu item to post/page associations.
 *
 * @since 1.6093.1200
 */
class Treatment_Menu_Item_Post_Associations extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'menu-item-post-associations';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Item Post Associations';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates menu items correctly link to posts/pages without broken references';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Menu_Item_Post_Associations' );
	}
}
