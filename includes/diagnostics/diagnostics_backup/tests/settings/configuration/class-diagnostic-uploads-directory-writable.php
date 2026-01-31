<?php
/**
 * Diagnostic: Uploads Directory Writable
 *
 * @since 1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_UploadsDirectoryWritable Class
 */
class Diagnostic_UploadsDirectoryWritable extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'uploads-directory-writable';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Uploads Directory Writable';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress can write files to /wp-content/uploads/ directory';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'configuration';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        $upload_dir = wp_upload_dir();
        
        if ( ! empty( $upload_dir['error'] ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: error message */
                    __( 'WordPress uploads directory has an error: %s. Users cannot upload images or media files.', 'wpshadow' ),
                    $upload_dir['error']
                ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/configuration-uploads-directory-writable',
                'manual_steps'  => array(
                    __( 'Check directory permissions (should be 755 or 775)', 'wpshadow' ),
                    __( 'Verify ownership (should match webserver user)', 'wpshadow' ),
                    __( 'Create /wp-content/uploads/ directory if missing', 'wpshadow' ),
                    __( 'Contact hosting support for permission changes', 'wpshadow' ),
                ),
                'impact'        => array(
                    'media'         => __( 'Cannot upload images, videos, or documents', 'wpshadow' ),
                    'users'         => __( 'Featured images and galleries will not work', 'wpshadow' ),
                    'functionality' => __( 'Plugins requiring file uploads will fail', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'error'         => $upload_dir['error'],
                    'basedir'       => $upload_dir['basedir'] ?? null,
                ),
            );
        }
        
        $basedir = $upload_dir['basedir'];
        
        // Check if directory exists
        if ( ! file_exists( $basedir ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: directory path */
                    __( 'Uploads directory does not exist: %s', 'wpshadow' ),
                    $basedir
                ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/configuration-uploads-directory-writable',
                'manual_steps'  => array(
                    __( 'Create the uploads directory', 'wpshadow' ),
                    __( 'Set permissions to 755 or 775', 'wpshadow' ),
                ),
                'impact'        => array(
                    'media' => __( 'Cannot upload any files', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'basedir' => $basedir,
                    'exists'  => false,
                ),
            );
        }
        
        // Test write permissions by creating a test file
        $test_file = trailingslashit( $basedir ) . '.wpshadow-test-' . time() . '.tmp';
        
        // Try to write test file
        $write_result = @file_put_contents( $test_file, 'test', LOCK_EX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
        
        if ( false === $write_result ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %s: directory path */
                    __( 'Cannot write to uploads directory: %s. Directory permissions do not allow file creation.', 'wpshadow' ),
                    $basedir
                ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/configuration-uploads-directory-writable',
                'manual_steps'  => array(
                    __( 'Change directory permissions to 755 or 775', 'wpshadow' ),
                    __( 'Verify webserver user owns the directory', 'wpshadow' ),
                    __( 'Check parent directory permissions', 'wpshadow' ),
                ),
                'impact'        => array(
                    'media' => __( 'File uploads will fail', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'basedir'     => $basedir,
                    'exists'      => true,
                    'writable'    => false,
                    'permissions' => substr( sprintf( '%o', fileperms( $basedir ) ), -4 ),
                ),
            );
        }
        
        // Clean up test file
        @unlink( $test_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
        
        // Directory is writable
        return null;
    }
}
