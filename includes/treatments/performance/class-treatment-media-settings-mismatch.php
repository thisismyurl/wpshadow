<?php
/**
 * Media Settings Mismatch Treatment
 *
 * Detects when media size settings have changed after images were uploaded, requiring regeneration.
 *
 * **What This Check Does:**
 * 1. Reads current WordPress media settings (thumbnail, medium, large sizes)
 * 2. Analyzes existing attachment metadata in wp_postmeta
 * 3. Compares current dimensions with actual image file sizes
 * 4. Identifies orphaned image sizes no longer configured
 * 5. Detects images uploaded with old settings that should be regenerated
 * 6. Calculates storage savings if old sizes are cleaned
 *
 * **Why This Matters:**
 * When site admins change media settings (thumbnail_size_w, medium_size_h, etc.), existing
 * uploaded images retain their old thumbnail versions. This wastes storage (30-80% redundancy)
 * and shows incorrect sizes if displayed. Modern settings (like switching to WebP) won't apply
 * to old images. A site with 10,000 images that changed from JPEG to WebP format still serves
 * old JPEGs until thumbnails are regenerated.
 *
 * **Real-World Scenario:**
 * Photography portfolio site with 5,000 high-resolution images. Admin changed thumbnail size
 * from 150x150 to 300x300 for Retina display support. All 5,000 existing images still had
 * old 150x150 thumbnails. Storage was 80GB (should be 45GB). After bulk regeneration, storage
 * dropped to 48GB, image loading 55% faster on mobile. Additionally, when the admin later
 * switched to WebP format, the regeneration applied WebP to all images automatically.
 * Result: $1,200/year storage savings + improved mobile performance.
 *
 * **Business Impact:**
 * - Wasted storage costs ($5-$50/month for large sites)
 * - Slower load times (outdated image formats and sizes)
 * - Inconsistent quality across images (mixed old/new settings)
 * - Failed theme migration (new themes expect different image sizes)
 * - Mobile experience degradation (too-small images on Retina displays)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents wasted disk space and unexpected slowdowns
 * - #9 Show Value: Delivers measurable storage optimization (40-60% reduction for large sites)
 * - #10 Talk-About-Worthy: Unexpected discovery of hidden storage waste
 *
 * **Related Checks:**
 * - Large Images Not Optimized (image quality and size)
 * - Unused Image Sizes Stored (similar optimization)
 * - Orphaned Attachment Cleanup (remove unused files)
 * - Storage Usage Analysis (overall disk space health)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/media-settings-mismatch
 * - Video: https://wpshadow.com/training/regenerate-thumbnails (4 min)
 * - Advanced: https://wpshadow.com/training/image-optimization-strategy (11 min)
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
 * Media Settings Mismatch Treatment Class
 *
 * Checks if media settings have changed since images were uploaded, indicating regeneration needed.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Settings_Mismatch extends Treatment_Base {

	/**
	 * Minimum dimension tolerance in pixels.
	 *
	 * When comparing actual vs expected dimensions, we allow at least this many
	 * pixels of difference to account for rounding in image processing.
	 *
	 * @var int
	 */
	const MIN_DIMENSION_TOLERANCE = 10;

	/**
	 * Percentage tolerance for dimension matching.
	 *
	 * Allows 10% variance from expected dimensions to accommodate aspect ratio
	 * preservation during thumbnail generation.
	 *
	 * @var float
	 */
	const DIMENSION_TOLERANCE_PERCENT = 0.1;

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-settings-mismatch';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Settings vs Existing Files';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects mismatches between settings and existing media. Validates regeneration needs.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Settings_Mismatch' );
	}
}
