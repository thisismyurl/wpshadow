<?php
/**
 * Orphaned Media Detection Treatment
 *
 * Identifies media files not attached to any posts.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Orphaned_Media_Detection Class
 *
 * Detects attachments that are not attached to any post and not referenced
 * in post content. Unused media increases storage costs and slows backups.
 *
 * @since 1.6093.1200
 */
class Treatment_Orphaned_Media_Detection extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-media-detection';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Media Detection';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Identifies media files not attached to any posts';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Unattached attachments
	 * - Orphaned attachments (missing parent)
	 * - Content references
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Orphaned_Media_Detection' );
	}
}
