<?php
/**
 * Post Meta Serialization Issues Treatment
 *
 * Detects improperly serialized post meta. Tests for serialization errors that cause
 * data corruption and identifies meta values with broken serialization.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Meta Serialization Issues Treatment Class
 *
 * Checks for serialization issues in post meta data.
 *
 * @since 1.6030.2148
 */
class Treatment_Post_Meta_Serialization_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-serialization-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Serialization Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects improperly serialized post meta and data corruption issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Post_Meta_Serialization_Issues' );
	}
}
