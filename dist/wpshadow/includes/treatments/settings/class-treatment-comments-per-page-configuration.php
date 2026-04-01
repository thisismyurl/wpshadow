<?php
/**
 * Comments Per Page Configuration Treatment
 *
 * Verifies the number of comments displayed per page is optimized.
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
 * Comments Per Page Configuration Treatment Class
 *
 * Checks comments per page setting for optimal balance.
 *
 * @since 0.6093.1200
 */
class Treatment_Comments_Per_Page_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comments-per-page-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comments Per Page Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comments per page setting';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comments_Per_Page_Configuration' );
	}
}
