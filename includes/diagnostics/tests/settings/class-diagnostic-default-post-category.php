<?php
/**
 * Default Post Category Diagnostic
 *
 * Verifies that a default post category is set so that posts always have
 * a category and are not assigned to "Uncategorized".
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Post Category Diagnostic Class
 *
 * Ensures default post category is set appropriately.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Default_Post_Category extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-post-category';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default Post Category';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies default post category is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Default category is set to a valid category
	 * - Category is not the generic "Uncategorized"
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get default category.
		$default_category = (int) get_option( 'default_category', 1 );

		// Check if category exists.
		$category = get_term( $default_category, 'category' );

		if ( ! $category || is_wp_error( $category ) ) {
			$issues[] = __( 'Default post category does not exist; set a proper category', 'wpshadow' );
		} else {
			// Check if it's the generic "Uncategorized".
			if ( 'uncategorized' === strtolower( $category->slug ) ) {
				$issues[] = __( 'Default post category is "Uncategorized"; consider creating a more specific category', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/default-post-category',
			);
		}

		return null;
	}
}
