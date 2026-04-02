<?php
/**
 * Social Image Alt Text Treatment
 *
 * Checks if social media share images have proper alt text for accessibility.
 *
 * @package    WPShadow
 * @subpackage Treatments/Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Accessibility;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Social Image Alt Text Treatment Class
 *
 * Validates that all social media share images have descriptive alt text.
 *
 * @since 1.6093.1200
 */
class Treatment_Social_Image_Alt_Text extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'social-image-alt-text';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Social Image Alt Text';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'Alt text validation for social images';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'accessibility';

    /**
     * Run the treatment check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
    	return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Accessibility\Diagnostic_Social_Image_Alt_Text' );
    }
}
