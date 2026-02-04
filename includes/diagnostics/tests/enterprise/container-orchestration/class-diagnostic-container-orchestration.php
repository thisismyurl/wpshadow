<?php
/**
 * Container Orchestration Diagnostic
 *
 * Checks if container orchestration (Kubernetes) is running.
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
 * Container Orchestration Diagnostic Class
 *
 * Validates that container orchestration platform (Kubernetes/similar) is running.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Container_Orchestration extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'container-orchestration';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Container Orchestration';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Kubernetes or similar running';

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
        // Check if running in container
        $is_containerized = (
            file_exists( '/.dockerenv' ) ||
            file_exists( '/run/.containerenv' ) ||
            getenv( 'KUBERNETES_SERVICE_HOST' ) !== false
        );

        if ( ! $is_containerized ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Not running in container orchestration. Containerization required for scale-out architecture.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/container-orchestration',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if Kubernetes is available
        $k8s_available = getenv( 'KUBERNETES_SERVICE_HOST' ) !== false;

        if ( ! $k8s_available ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Running in container but not connected to Kubernetes. Connect for orchestration.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 40,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/container-orchestration',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if orchestration config is set
        $orchestration_configured = get_option( 'wpshadow_orchestration_configured' );

        if ( ! $orchestration_configured ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Kubernetes connected but WPShadow orchestration not configured. Set up for auto-scaling.', 'wpshadow' ),
                'severity'      => 'low',
                'threat_level'  => 20,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/container-orchestration',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}
