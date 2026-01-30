<?php
/**
 * Diagnostic: Plugin Conflicts Detection
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
 * Diagnostic_PluginConflictsDetected Class
 */
class Diagnostic_PluginConflictsDetected extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'plugin-conflicts-detected';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Plugin Conflicts Detection';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if active plugins are conflicting with each other or causing fatal errors';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'plugins';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        $issues = array();
        
        // Check for PHP fatal errors in error log
        $error_log = ini_get( 'error_log' );
        
        if ( ! empty( $error_log ) && file_exists( $error_log ) && is_readable( $error_log ) ) {
            // Read last 100 lines of error log
            $log_lines = @file( $error_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file
            
            if ( ! empty( $log_lines ) ) {
                $recent_lines = array_slice( $log_lines, -100 );
                $plugin_errors = array();
                
                foreach ( $recent_lines as $line ) {
                    // Look for plugin-related fatal errors
                    if ( preg_match( '/Fatal error.*\/plugins\/([^\/]+)\//i', $line, $matches ) ) {
                        $plugin_slug = $matches[1];
                        if ( ! isset( $plugin_errors[ $plugin_slug ] ) ) {
                            $plugin_errors[ $plugin_slug ] = 0;
                        }
                        ++$plugin_errors[ $plugin_slug ];
                    }
                }
                
                if ( ! empty( $plugin_errors ) ) {
                    arsort( $plugin_errors );
                    foreach ( $plugin_errors as $plugin => $count ) {
                        $issues[] = sprintf(
                            /* translators: 1: plugin name, 2: error count */
                            __( '%1$s: %2$d fatal errors detected', 'wpshadow' ),
                            $plugin,
                            $count
                        );
                    }
                }
            }
        }
        
        // Check for known conflicting plugin combinations
        $active_plugins = get_option( 'active_plugins', array() );
        $plugin_basenames = array_map( 'plugin_basename', $active_plugins );
        
        $known_conflicts = array(
            array(
                'plugins' => array( 'wp-super-cache', 'w3-total-cache', 'wp-fastest-cache' ),
                'message' => __( 'Multiple caching plugins detected (WP Super Cache, W3 Total Cache, WP Fastest Cache). This can cause conflicts.', 'wpshadow' ),
            ),
            array(
                'plugins' => array( 'seo-by-rank-math', 'wordpress-seo' ),
                'message' => __( 'Multiple SEO plugins detected (Rank Math, Yoast SEO). This can cause duplicate meta tags.', 'wpshadow' ),
            ),
            array(
                'plugins' => array( 'wordfence', 'better-wp-security', 'sucuri-scanner' ),
                'message' => __( 'Multiple security plugins detected. This can cause conflicts and performance issues.', 'wpshadow' ),
            ),
        );
        
        foreach ( $known_conflicts as $conflict ) {
            $active_conflicting = array();
            
            foreach ( $conflict['plugins'] as $plugin_slug ) {
                foreach ( $plugin_basenames as $active_plugin ) {
                    if ( false !== strpos( $active_plugin, $plugin_slug ) ) {
                        $active_conflicting[] = $plugin_slug;
                    }
                }
            }
            
            if ( count( $active_conflicting ) > 1 ) {
                $issues[] = $conflict['message'];
            }
        }
        
        // Check for duplicate functionality by analyzing active plugins
        $functionality_categories = array(
            'cache'    => array( 'cache', 'caching' ),
            'seo'      => array( 'seo', 'sitemap' ),
            'security' => array( 'security', 'firewall', 'malware' ),
            'backup'   => array( 'backup', 'restore' ),
            'minify'   => array( 'minify', 'optimize', 'compress' ),
        );
        
        $plugins_by_category = array();
        
        foreach ( $active_plugins as $plugin_file ) {
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false, false );
            $plugin_name = $plugin_data['Name'];
            $plugin_desc = $plugin_data['Description'];
            $combined_text = strtolower( $plugin_name . ' ' . $plugin_desc );
            
            foreach ( $functionality_categories as $category => $keywords ) {
                foreach ( $keywords as $keyword ) {
                    if ( false !== strpos( $combined_text, $keyword ) ) {
                        if ( ! isset( $plugins_by_category[ $category ] ) ) {
                            $plugins_by_category[ $category ] = array();
                        }
                        $plugins_by_category[ $category ][] = $plugin_name;
                        break;
                    }
                }
            }
        }
        
        foreach ( $plugins_by_category as $category => $plugins ) {
            if ( count( $plugins ) > 2 ) {
                $issues[] = sprintf(
                    /* translators: 1: category name, 2: plugin count */
                    __( 'Multiple %1$s plugins active (%2$d): %3$s', 'wpshadow' ),
                    $category,
                    count( $plugins ),
                    implode( ', ', array_slice( $plugins, 0, 3 ) )
                );
            }
        }
        
        if ( empty( $issues ) ) {
            return null; // No conflicts detected
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: list of issues */
                __( 'Potential plugin conflicts detected: %s', 'wpshadow' ),
                implode( '; ', $issues )
            ),
            'severity'      => 'high',
            'threat_level'  => 65,
            'auto_fixable'  => false,
            'kb_link'       => 'https://wpshadow.com/kb/plugins-plugin-conflicts',
            'manual_steps'  => array(
                __( 'Deactivate plugins one by one to identify the conflict', 'wpshadow' ),
                __( 'Keep only one plugin per functionality category (cache, SEO, etc)', 'wpshadow' ),
                __( 'Check plugin compatibility before activating', 'wpshadow' ),
                __( 'Review error logs for specific fatal errors', 'wpshadow' ),
            ),
            'impact'        => array(
                'stability'   => __( 'Plugin conflicts can cause white screens and crashes', 'wpshadow' ),
                'performance' => __( 'Duplicate functionality wastes resources', 'wpshadow' ),
                'features'    => __( 'Conflicting plugins may break site features', 'wpshadow' ),
            ),
            'evidence'      => array(
                'issues'          => $issues,
                'error_log'       => $error_log ?? null,
                'active_plugins'  => count( $active_plugins ),
            ),
        );
    }
}
