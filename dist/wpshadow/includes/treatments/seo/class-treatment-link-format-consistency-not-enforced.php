<?php
/**
 * Link Format Consistency Not Enforced Treatment
 *
 * Checks if link formats are consistent.
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
 * Link Format Consistency Not Enforced Treatment Class
 *
 * Detects inconsistent link formats.
 *
 * @since 1.6093.1200
 */
class Treatment_Link_Format_Consistency_Not_Enforced extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'link-format-consistency-not-enforced';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Link Format Consistency Not Enforced';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if link formats are consistent';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Link_Format_Consistency_Not_Enforced' );
	}
}
