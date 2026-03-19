<?php
/**
 * Mobile Image Lazy Loading Treatment
 *
 * Ensures below-fold images load on demand to reduce bandwidth.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Image Lazy Loading Treatment Class
 *
 * Ensures below-fold images load on demand using loading="lazy" or Intersection Observer,
 * reducing initial page weight by up to 30%.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Image_Lazy_Loading extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-lazy-loading';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Lazy Loading';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure below-fold images load on demand to reduce bandwidth consumption';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Image_Lazy_Loading' );
	}
}
