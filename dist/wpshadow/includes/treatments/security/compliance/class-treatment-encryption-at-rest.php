<?php
/**
 * Encryption at Rest Treatment
 *
 * Checks whether data-at-rest encryption is configured.
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
 * Treatment_Encryption_At_Rest Class
 *
 * Validates that encryption at rest is enabled via configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Encryption_At_Rest extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'encryption-at-rest';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Encryption at Rest';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether data-at-rest encryption is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Encryption_At_Rest' );
	}
}