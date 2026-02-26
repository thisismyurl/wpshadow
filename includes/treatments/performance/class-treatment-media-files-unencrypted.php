<?php
/**
 * Media Files Unencrypted Treatment
 *
 * Detects when media files are stored without encryption at rest,
 * posing compliance and security risks for sensitive content.
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
 * Media Files Unencrypted Treatment Class
 *
 * Checks if media files are encrypted at rest. For sites handling
 * sensitive content (medical, legal, financial), unencrypted storage
 * violates compliance requirements (HIPAA, GDPR, PCI-DSS).
 *
 * @since 1.6033.1430
 */
class Treatment_Media_Files_Unencrypted extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-files-unencrypted';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Files Stored Without Encryption';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unencrypted media files at rest that may contain sensitive content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Checks if media files are encrypted. For compliance-sensitive sites,
	 * encryption at rest is required for sensitive file types (PDFs, DOCX, etc.).
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Files_Unencrypted' );
	}
}
