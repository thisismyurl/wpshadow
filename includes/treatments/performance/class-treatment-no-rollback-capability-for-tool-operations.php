<?php
/**
 * No Rollback Capability for Tool Operations Treatment
 *
 * Tests for operation rollback support.
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
 * No Rollback Capability for Tool Operations Treatment Class
 *
 * Tests for operation rollback support.
 *
 * @since 0.6093.1200
 */
class Treatment_No_Rollback_Capability_For_Tool_Operations extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-rollback-capability-for-tool-operations';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Rollback Capability for Tool Operations';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for operation rollback support';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Rollback_Capability_For_Tool_Operations' );
	}
}
