<?php
/**
 * Get Finding Family AJAX Handler
 *
 * Fetches family information for a finding so Kanban board can offer
 * family-aware fixing options (Philosophy #9: Show Value)
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Get_Finding_Family_Handler extends AJAX_Handler_Base {
    /**
     * Register AJAX hook
     */
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_get_finding_family', [ __CLASS__, 'handle' ] );
    }

    /**
     * Handle request to get family info for a finding
     */
    public static function handle() : void {
        self::verify_request( 'wpshadow_kanban', 'manage_options', 'nonce' );

        $finding_id = self::get_post_param( 'finding_id', 'text', '', true );

        // Get the finding from the database
        $findings = \wpshadow_get_site_findings();
        $finding = null;

        // Search for the finding in quick/deep findings
        foreach ( $findings as $f ) {
            if ( isset( $f['id'] ) && $f['id'] === $finding_id ) {
                $finding = $f;
                break;
            }
        }

        if ( ! $finding ) {
            self::send_error( __( 'Finding not found.', 'wpshadow' ) );
        }

        // Get family info from registry
        $family_slug = $finding['family'] ?? '';
        $family_label = $finding['family_label'] ?? '';

        if ( empty( $family_slug ) ) {
            // No family - this is a standalone finding
            self::send_success( [
                'has_family'         => false,
                'family_slug'        => '',
                'family_label'       => '',
                'family_members'     => [],
                'family_member_count' => 0,
            ] );
            return;
        }

        // Get all family members from registry
        $registry = new \WPShadow\Diagnostics\Diagnostic_Registry();
        $family_members = $registry->get_family_members( $family_slug );

        // Get findings that are in this family
        $family_findings = [];
        foreach ( $findings as $f ) {
            if ( isset( $f['family'] ) && $f['family'] === $family_slug && $f['id'] !== $finding_id ) {
                $family_findings[] = [
                    'id'    => $f['id'],
                    'title' => $f['title'],
                ];
            }
        }

        self::send_success( [
            'has_family'          => true,
            'family_slug'         => $family_slug,
            'family_label'        => $family_label,
            'family_members'      => $family_findings,
            'family_member_count' => count( $family_findings ),
        ] );
    }
}
