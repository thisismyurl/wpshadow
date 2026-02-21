<?php
/**
 * Comment Nesting Depth Treatment
 *
 * Verifies comment threading depth is configured for readability.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Nesting Depth Treatment Class
 *
 * Checks comment threading/nesting configuration.
 *
 * @since 1.6032.1755
 */
class Treatment_Comment_Nesting_Depth extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-nesting-depth';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Nesting Depth';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment threading depth';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Nesting_Depth' );
	}
}
