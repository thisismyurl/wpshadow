<?php
/**
 * LinkedIn and Pinterest Rich Metadata
 *
 * Validates LinkedIn and Pinterest-specific rich metadata implementation.
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
 * Treatment_LinkedIn_Pinterest_Metadata Class
 *
 * Checks for LinkedIn and Pinterest-specific meta tag implementations.
 *
 * @since 1.6030.2148
 */
class Treatment_LinkedIn_Pinterest_Metadata extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'linkedin-pinterest-rich-metadata';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'LinkedIn & Pinterest Rich Metadata';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates LinkedIn and Pinterest-specific rich metadata';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_LinkedIn_Pinterest_Metadata' );
	}
}
