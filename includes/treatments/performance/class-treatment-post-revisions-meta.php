<?php
/**
 * Database Post Revisions and Meta Cleanup
 *
 * Validates post revision limits and post metadata accumulation.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Post_Revisions_Meta Class
 *
 * Checks post revision management and postmeta table health.
 *
 * @since 1.6030.2148
 */
class Treatment_Post_Revisions_Meta extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-meta-cleanup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions and Meta Cleanup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates post revision limits and post metadata management';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Post_Revisions_Meta' );
	}
}
