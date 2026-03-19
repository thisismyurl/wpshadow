<?php
/**
 * Media Cloud Offload Missing Treatment
 *
 * Detects when media files are stored only locally without cloud offload,
 * increasing hosting costs and risking data loss.
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
 * Media Cloud Offload Missing Treatment Class
 *
 * Checks if media files are offloaded to cloud storage. Cloud offload
 * reduces hosting costs, improves performance, and increases reliability.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Cloud_Offload_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cloud-offload-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Files Not Offloaded to Cloud Storage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects media files stored only locally without cloud offload';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks if media is offloaded to cloud storage (S3, R2, GCS, etc.).
	 * Cloud offload reduces server load and hosting costs.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Cloud_Offload_Missing' );
	}
}
