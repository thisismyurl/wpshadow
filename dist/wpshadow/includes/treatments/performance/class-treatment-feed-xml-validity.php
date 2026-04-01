<?php
/**
 * Feed XML Validity Treatment
 *
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_XML_Validity Class
 *
 */
class Treatment_Feed_XML_Validity extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-xml-validity';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed XML Validity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the main feed XML is well-formed and valid.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Feed_XML_Validity' );
	}
}
