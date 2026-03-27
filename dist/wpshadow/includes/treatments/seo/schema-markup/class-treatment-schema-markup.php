<?php
/**
 * Schema Markup Treatment
 *
 * Checks if JSON-LD schema markup is properly implemented for SEO.
 *
 * @package    WPShadow
 * @subpackage Treatments/SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\SEO;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Schema Markup Treatment Class
 *
 * Validates JSON-LD schema markup implementation for structured data.
 *
 * @since 1.6093.1200
 */
class Treatment_Schema_Markup extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'schema-markup';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Schema Markup';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'JSON-LD schema properly implemented';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'seo';

    /**
     * Run the treatment check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
    	return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\SEO\Diagnostic_Schema_Markup' );
    }
}
