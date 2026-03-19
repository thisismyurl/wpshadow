<?php
/**
 * Data Encryption At Rest Treatment
 *
 * Checks whether sensitive data is protected with encryption at rest.
 *
 * @package    WPShadow
 * @subpackage Treatments\Privacy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Encryption At Rest Treatment Class
 *
 * Verifies that encryption tools are present for sensitive data.
 *
 * @since 1.6093.1200
 */
class Treatment_Data_Encryption_At_Rest extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'data-encryption-at-rest';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'User Data Not Encrypted At Rest';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if encryption tools protect sensitive data at rest';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Data_Encryption_At_Rest' );
	}
}
