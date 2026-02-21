<?php
/**
 * Comment Spam Filter Treatment
 *
 * Checks if effective spam filtering is active.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Spam Filter Treatment Class
 *
 * Verifies that effective spam filtering is active and that the site
 * is protected from comment spam.
 *
 * @since 1.6035.1300
 */
class Treatment_Comment_Spam_Filter extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-filter';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Filter';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if effective spam filtering is active';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the comment spam filter treatment check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if spam filter issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Spam_Filter' );
	}
}
