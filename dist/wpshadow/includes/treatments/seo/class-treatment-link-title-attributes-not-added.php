<?php
/**
 * Link Title Attributes Not Added Treatment
 *
 * Checks if link title attributes are added.
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
 * Link Title Attributes Not Added Treatment Class
 *
 * Detects missing link title attributes.
 *
 * @since 0.6093.1200
 */
class Treatment_Link_Title_Attributes_Not_Added extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'link-title-attributes-not-added';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Link Title Attributes Not Added';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if link title attributes are added';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Link_Title_Attributes_Not_Added' );
	}
}
