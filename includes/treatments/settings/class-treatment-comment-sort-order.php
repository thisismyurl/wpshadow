<?php
/**
 * Comment Sort Order Treatment
 *
 * Verifies comment display order is configured for best user experience.
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
 * Comment Sort Order Treatment Class
 *
 * Checks comment sorting configuration.
 *
 * @since 1.6032.1755
 */
class Treatment_Comment_Sort_Order extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-sort-order';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Sort Order';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment sort order';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Sort_Order' );
	}
}
