<?php
/**
 * CPT Meta Box Support Treatment
 *
 * Checks if custom post types support meta boxes correctly and validates
 * add_meta_box functionality for custom fields.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Meta Box Support Class
 *
 * Verifies custom post types properly support meta boxes and that
 * registered meta boxes are accessible in the editor.
 *
 * @since 0.6093.1200
 */
class Treatment_CPT_Meta_Box_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-meta-box-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Meta Box Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CPTs support meta boxes correctly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * Validates CPT meta box support and checks for common
	 * configuration issues preventing meta boxes from displaying.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if meta box issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CPT_Meta_Box_Support' );
	}
}
