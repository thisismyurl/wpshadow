<?php
/**
 * Comment Pagination Settings Treatment
 *
 * Verifies comment pagination is configured for performance and usability.
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
 * Comment Pagination Settings Treatment Class
 *
 * Checks comment pagination configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Pagination_Settings extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-pagination-settings';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Pagination Settings';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment pagination configuration';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Pagination_Settings' );
	}
}
