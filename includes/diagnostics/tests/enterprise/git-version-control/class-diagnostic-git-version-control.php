<?php
/**
 * Git/Version Control Diagnostic
 *
 * Checks if version control is properly configured for development.
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
 * Git/Version Control Diagnostic Class
 *
 * Validates that version control (Git) is properly configured for code management.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Git_Version_Control extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'git-version-control';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Git/Version Control';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Version control properly configured';

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
        // Check if .git directory exists
        $wp_root = ABSPATH;
        $git_dir = $wp_root . '.git';

        if ( ! is_dir( $git_dir ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Git repository not found. Initialize version control for code management.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 40,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/git-version-control',
                'persona'       => 'developer',
            );
        }

        // Check if .gitignore exists
        $gitignore = $wp_root . '.gitignore';
        if ( ! file_exists( $gitignore ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( '.gitignore not configured. Add it to avoid committing sensitive files.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 35,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/git-version-control',
                'persona'       => 'developer',
            );
        }

        return null; // No issue found
    }
}
