<?php
/**
 * Hidden Meta Field Bloat Treatment
 *
 * Identifies excessive hidden meta fields that bloat the postmeta table. Measures
 * meta table size and detects plugins creating unnecessary hidden fields.
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
 * Hidden Meta Field Bloat Treatment Class
 *
 * Checks for excessive hidden meta field bloat in the postmeta table.
 *
 * @since 1.6030.2148
 */
class Treatment_Hidden_Meta_Field_Bloat extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'hidden-meta-field-bloat';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Hidden Meta Field Bloat';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies excessive hidden meta fields bloating the database';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Hidden_Meta_Field_Bloat' );
	}
}
