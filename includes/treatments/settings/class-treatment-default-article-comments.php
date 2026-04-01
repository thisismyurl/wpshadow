<?php
/**
 * Default Article Comments Treatment
 *
 * Verifies comments are appropriately enabled/disabled by default.
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
 * Default Article Comments Treatment Class
 *
 * Checks default comment status configuration for articles.
 *
 * @since 0.6093.1200
 */
class Treatment_Default_Article_Comments extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-article-comments';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Default Article Comments';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies default comment status for articles';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Default_Article_Comments' );
	}
}
