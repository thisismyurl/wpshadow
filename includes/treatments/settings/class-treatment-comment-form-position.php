<?php
/**
 * Comment Form Position Treatment
 *
 * Verifies comment form placement is optimized for user engagement.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Form Position Treatment Class
 *
 * Checks comment form positioning relative to existing comments.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Form_Position extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-position';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form Position';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment form position';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Form_Position' );
	}
}
