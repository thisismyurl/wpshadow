<?php
/**
 * Comment Author Whitelist Treatment
 *
 * Verifies that previously approved commenters can post without moderation delays.
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
 * Comment Author Whitelist Treatment Class
 *
 * Checks comment approval whitelist configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Author_Whitelist extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-whitelist';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Whitelist';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment approval whitelist configuration';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Author_Whitelist' );
	}
}
