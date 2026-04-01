<?php
/**
 * Change Management Process Diagnostic
 *
 * Checks if formal change control procedures are active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Enterprise
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Enterprise;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Change Management Process Diagnostic Class
 *
 * Validates that formal change control procedures are in place.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Change_Management_Process extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'change-management-process';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Change Management Process';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Formal change control active';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'enterprise';

    /**
     * Run the diagnostic check.
     *
     * @since 0.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if site is under version control
        $git_dir = ABSPATH . '.git';
        $has_git = is_dir( $git_dir );

        // Check for deployment/change management plugins
        $change_mgmt_plugins = array(
            'mainwp/mainwp.php'                         => 'MainWP (change tracking)',
            'wp-deployment/wp-deployment.php'           => 'WP Deployment',
            'simple-history/index.php'                  => 'Simple History',
            'activity-log/activity-log.php'             => 'Activity Log',
        );

        $found_change_mgmt = array();
        foreach ( $change_mgmt_plugins as $plugin_path => $name ) {
            if ( is_plugin_active( $plugin_path ) ) {
                $found_change_mgmt[] = $name;
            }
        }

        // Check for deployment tracking option
        $deployment_log = get_option( 'wpshadow_deployment_log' );
        $change_approval_required = get_option( 'wpshadow_change_approval_required' );

        if ( $has_git ) {
            $found_change_mgmt[] = 'Version control (Git)';
        }

        if ( $deployment_log ) {
            $found_change_mgmt[] = 'Deployment logging enabled';
        }

        if ( empty( $found_change_mgmt ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No change management process detected. Implement version control, deployment logging, and change approval workflow for accountability.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/change-management-process?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        // If we have basic change tracking but no approval workflow
        if ( ! $change_approval_required && ! empty( $found_change_mgmt ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Change tracking active but no approval workflow. Consider implementing change approval for production deployments.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 40,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/change-management-process?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // Change management detected
    }
}
