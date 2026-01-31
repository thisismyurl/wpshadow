<?php
/**
 * Post Revisions Limit Not Set Diagnostic
 *
 * Checks if post revisions limit is set.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revisions Limit Not Set Diagnostic Class
 *
 * Detects unlimited post revisions.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Post_Revisions_Limit_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-limit-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions Limit Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post revisions limit is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check post revisions limit
		if ( ! defined( 'WP_POST_REVISIONS' ) || WP_POST_REVISIONS === true ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Post revisions limit is not set. Add define( \'WP_POST_REVISIONS\', 3 ); to wp-config.php to limit database growth.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/post-revisions-limit-not-set',
			);
		}

		return null;
	}
}
