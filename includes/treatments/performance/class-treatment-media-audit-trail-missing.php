<?php
/**
 * Media Audit Trail Missing Treatment
 *
 * Detects when media operations (uploads, deletions, modifications)
 * are not logged, lacking accountability and compliance audit trails.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Audit Trail Missing Treatment Class
 *
 * Checks if media file operations are logged for audit purposes.
 * Required for compliance (SOC 2, ISO 27001) and security investigations.
 *
 * @since 1.6033.1430
 */
class Treatment_Media_Audit_Trail_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-audit-trail-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Audit Trail for Media Operations';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing audit logging for media uploads, deletions, and modifications';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Checks if media operations are logged. Audit trails are critical
	 * for accountability, security investigations, and compliance.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Audit_Trail_Missing' );
	}
}
