<?php
/**
 * Post Revisions Count Treatment
 *
 * Checks for excessive post revisions bloating the database.
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
 * Post Revisions Count Treatment Class
 *
 * Detects excessive post revisions. Each revision stores full
 * post content, significantly bloating the database.
 *
 * @since 0.6093.1200
 */
class Treatment_Post_Revisions_Count extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-count';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions Count';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive post revisions';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Counts post revisions and calculates database impact.
	 * Threshold: >1000 revisions or >10MB
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Post_Revisions_Count' );
	}
}
