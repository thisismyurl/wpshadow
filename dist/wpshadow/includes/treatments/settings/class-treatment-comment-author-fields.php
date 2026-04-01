<?php
/**
 * Comment Author Information and Requirements
 *
 * Validates comment form author field requirements and email verification.
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
 * Treatment_Comment_Author_Fields Class
 *
 * Checks comment form author field requirements and verification.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Author_Fields extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-fields';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Fields and Requirements';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment author field requirements and email collection';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Author_Fields' );
	}
}
