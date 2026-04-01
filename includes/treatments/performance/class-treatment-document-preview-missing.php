<?php
/**
 * Document Preview Missing Treatment
 *
 * Detects when document files lack in-browser preview capability,
 * requiring users to download files to view them.
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
 * Document Preview Missing Treatment Class
 *
 * Checks if documents can be previewed in-browser. WordPress doesn't
 * provide document previews, creating friction and security concerns.
 *
 * @since 0.6093.1200
 */
class Treatment_Document_Preview_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'document-preview-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Document Files Lack Preview Capability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects documents requiring download to view instead of in-browser preview';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-optimization';

	/**
	 * Run the treatment check.
	 *
	 * Checks if documents have preview capability. In-browser previews
	 * improve UX and allow viewing without downloading.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Document_Preview_Missing' );
	}
}
