<?php
/**
 * Comment Display and Threading Configuration
 *
 * Validates comment display settings and threaded reply functionality.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Comment_Threading Class
 *
 * Checks comment display settings and threading configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Threading extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-threading';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Threading and Display';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment display settings and threaded replies';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Threading' );
	}
}
