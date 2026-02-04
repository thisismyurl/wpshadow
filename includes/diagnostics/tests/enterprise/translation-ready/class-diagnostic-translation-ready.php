<?php
/**
 * Translation Ready Diagnostic
 *
 * Checks if the theme is properly marked for translation and supports multiple languages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Enterprise
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Enterprise;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Translation Ready Diagnostic Class
 *
 * Validates that theme is properly marked for translation with text domain
 * and language files present.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Translation_Ready extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'translation-ready';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Translation Ready';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Theme properly marked for translation';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'enterprise';

    /**
     * Run the diagnostic check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        $theme = wp_get_theme();

        // Check for text domain
        $text_domain = $theme->get( 'TextDomain' );
        if ( empty( $text_domain ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Theme has no text domain defined in style.css', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 35,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/translation-ready',
                'persona'       => 'developer',
            );
        }

        // Check for domain path
        $domain_path = $theme->get( 'DomainPath' );
        if ( empty( $domain_path ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Theme has no Domain Path defined for language files', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 30,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/translation-ready',
                'persona'       => 'developer',
            );
        }

        // Check if .pot file exists
        $theme_dir = get_theme_root() . '/' . get_template();
        $pot_file  = $theme_dir . $domain_path . '/' . $text_domain . '.pot';

        if ( ! file_exists( $pot_file ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: .pot file path */
                    __( 'Translation file not found at %s', 'wpshadow' ),
                    $pot_file
                ),
                'severity'      => 'low',
                'threat_level'  => 20,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/translation-ready',
                'persona'       => 'developer',
            );
        }

        return null; // No issue found
    }
}
