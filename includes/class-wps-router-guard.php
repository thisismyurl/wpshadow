<?php
/**
 * Centralized router guard for disabled modules.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Router guard to block direct access to disabled modules and provide friendly redirects.
 */
class WPS_Router_Guard {
    /**
     * Execute guard checks; performs redirects and exits when needed.
     *
     * @return void
     */
    public static function execute(): void {
        if ( ! is_admin() ) {
            return;
        }

        // Guard when module is specified directly via URL.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $raw_module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
        if ( ! empty( $raw_module ) ) {
            $module_slug = str_contains( $raw_module, '-support-thisismyurl' ) ? $raw_module : $raw_module . '-support-thisismyurl';
            if ( ! WPS_Module_Registry::is_enabled( $module_slug ) ) {
                // Add one-time admin notice after redirect.
                if ( function_exists( __NAMESPACE__ . '\wp_support_add_admin_notice' ) ) {
                    wp_support_add_admin_notice( sprintf( __( '%s is disabled. Redirected to Support dashboard.', 'plugin-wp-support-thisismyurl' ), ucfirst( $raw_module ) ), 'warning' );
                }
                $parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support' ) : admin_url( 'admin.php?page=wp-support' );
                wp_safe_redirect( $parent_url );
                exit;
            }
        }

        // Context-based guard for hub/spoke.
        $context = WPS_Tab_Navigation::get_current_context();
        $hub     = $context['hub'];
        $spoke   = $context['spoke'];

        if ( ! empty( $hub ) ) {
            $hub_slug = $hub . '-support-thisismyurl';
            if ( ! WPS_Module_Registry::is_enabled( $hub_slug ) ) {
                if ( function_exists( __NAMESPACE__ . '\wp_support_add_admin_notice' ) ) {
                    wp_support_add_admin_notice( sprintf( __( '%s hub is disabled. Redirected to Support dashboard.', 'plugin-wp-support-thisismyurl' ), ucfirst( $hub ) ), 'warning' );
                }
                $parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support' ) : admin_url( 'admin.php?page=wp-support' );
                wp_safe_redirect( $parent_url );
                exit;
            }

            if ( ! empty( $spoke ) ) {
                $spoke_slug = $spoke . '-support-thisismyurl';
                if ( ! WPS_Module_Registry::is_enabled( $spoke_slug ) ) {
                    if ( function_exists( __NAMESPACE__ . '\wp_support_add_admin_notice' ) ) {
                        wp_support_add_admin_notice( sprintf( __( '%1$s format is disabled. Redirected to %2$s hub.', 'plugin-wp-support-thisismyurl' ), strtoupper( $spoke ), ucfirst( $hub ) ), 'warning' );
                    }
                    $parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wp-support&module=' . $hub ) : admin_url( 'admin.php?page=wp-support&module=' . $hub );
                    wp_safe_redirect( $parent_url );
                    exit;
                }
            }
        }
    }
}

/* @changelog Introduce WPS_Router_Guard to centralize disabled-module redirects and notices. */
