<?php
/**
 * Custom Fields and Post Meta Validation
 *
 * Validates custom field registration and post meta management.
 *
 * @since   1.2034.1145
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Custom_Fields_Meta Class
 *
 * Checks custom field and post meta configuration issues.
 *
 * @since 1.2034.1145
 */
class Treatment_Custom_Fields_Meta extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-fields-meta';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Fields and Post Meta';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom field registration and post meta management';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'custom-post-types';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.2034.1145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Custom_Fields_Meta' );
	}
}
