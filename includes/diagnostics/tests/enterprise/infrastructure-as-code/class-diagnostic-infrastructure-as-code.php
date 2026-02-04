<?php
/**
 * Infrastructure as Code Diagnostic
 *
 * Checks if infrastructure is defined and validated as code.
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
 * Infrastructure as Code Diagnostic Class
 *
 * Validates that infrastructure is defined and validated as code (IaC).
 *
 * @since 1.6050.0000
 */
class Diagnostic_Infrastructure_As_Code extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'infrastructure-as-code';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Infrastructure as Code';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'IaC validated';

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
        // Check for IaC configuration files in common locations
        $iac_indicators = array(
            ABSPATH . '../terraform.tf'           => 'Terraform',
            ABSPATH . '../main.tf'                => 'Terraform',
            ABSPATH . '../cloudformation.yaml'    => 'CloudFormation',
            ABSPATH . '../ansible.yml'            => 'Ansible',
            ABSPATH . '../ansible.yaml'           => 'Ansible',
            ABSPATH . '../pulumi.yaml'            => 'Pulumi',
            ABSPATH . '../.terraform/'            => 'Terraform',
        );

        $found_iac = array();
        foreach ( $iac_indicators as $path => $tool ) {
            if ( file_exists( $path ) ) {
                $found_iac[] = $tool;
            }
        }

        // Check for IaC state marker (site owner can set this if IaC is in separate repo)
        $iac_declared = get_option( 'wpshadow_iac_tool' );
        if ( $iac_declared ) {
            $found_iac[] = $iac_declared;
        }

        if ( empty( $found_iac ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Your server setup could be documented in files (like recipes) instead of just remembered. This means if something breaks, you can rebuild everything exactly as it was. Popular tools include Terraform and CloudFormation.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/infrastructure-as-code',
                'persona'       => 'enterprise-corp',
                'meta'          => array(
                    'checked_paths' => array_keys( $iac_indicators ),
                ),
            );
        }

        return null; // IaC detected
    }
}
