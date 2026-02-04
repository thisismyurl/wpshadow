<?php
/**
 * Persona Registry
 *
 * Manages diagnostic collections tailored to specific WordPress user personas,
 * enabling targeted health assessments focused on what each group cares about most.
 *
 * @since   1.6030.2148
 * @package WPShadow\Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Persona_Registry Class
 *
 * Provides persona definitions, diagnostic collections, and priority mappings
 * for all supported WordPress user types including corporate enterprises.
 *
 * @since 1.6030.2148
 */
class Persona_Registry {

    /**
     * Defined personas with metadata
     *
     * @var array
     */
    protected static $personas = array(
        'diy-owner'      => array(
            'label'       => 'DIY Website Owner',
            'description' => 'Solo site owner managing their own WordPress installation',
            'percentage'  => 35,
            'pain_points' => array(
                'Peace of mind',
                'Disaster prevention',
                'Cost minimization',
                'Technical confusion',
            ),
            'goals'       => array(
                'Prevent data loss',
                'Keep site secure',
                'Avoid emergency outages',
                'Understand health status',
            ),
        ),
        'agency'         => array(
            'label'       => 'Agency Owner',
            'description' => 'Managing multiple client WordPress sites',
            'percentage'  => 15,
            'pain_points' => array(
                'Support ticket overload',
                'Proactive monitoring',
                'Client confidence',
                'Scaling challenges',
            ),
            'goals'       => array(
                'Reduce support tickets',
                'Prevent emergencies',
                'Scale operations',
                'Build reputation',
            ),
        ),
        'ecommerce'      => array(
            'label'       => 'E-commerce Store Owner',
            'description' => 'Running WordPress e-commerce with revenue implications',
            'percentage'  => 20,
            'pain_points' => array(
                'Revenue protection',
                'Conversion optimization',
                'Checkout reliability',
                'Payment processing',
            ),
            'goals'       => array(
                'Maximize uptime',
                'Optimize conversion rate',
                'Ensure payment reliability',
                'Protect customer data',
            ),
        ),
        'publisher'      => array(
            'label'       => 'Content Publisher',
            'description' => 'Publishing content-focused WordPress site',
            'percentage'  => 20,
            'pain_points' => array(
                'Audience reach',
                'Content preservation',
                'Engagement',
                'Performance impact on traffic',
            ),
            'goals'       => array(
                'Maximize audience reach',
                'Improve SEO rankings',
                'Preserve content',
                'Fast page load times',
            ),
        ),
        'developer'      => array(
            'label'       => 'Web Developer/Agency Technical',
            'description' => 'Building and maintaining WordPress sites professionally',
            'percentage'  => 10,
            'pain_points' => array(
                'Code quality',
                'Client technical debt',
                'Post-launch emergencies',
                'Performance baselines',
            ),
            'goals'       => array(
                'Quality deliverables',
                'Fewer post-launch issues',
                'Client satisfaction',
                'Professional standards',
            ),
        ),
        'corporate'      => array(
            'label'       => 'Corporate/Enterprise Compliance-Focused',
            'description' => 'Enterprise organization with regulatory requirements',
            'percentage'  => 5,
            'pain_points' => array(
                'Regulatory compliance',
                'Audit requirements',
                'Data governance',
                'Breach liability',
            ),
            'goals'       => array(
                'Maintain compliance',
                'Pass audits',
                'Protect sensitive data',
                'Ensure accountability',
            ),
        ),
        'enterprise-corp' => array(
            'label'       => 'Large Enterprise Corporation',
            'description' => 'Large-scale enterprise with complex infrastructure needs',
            'percentage'  => 5,
            'pain_points' => array(
                'High availability',
                'Infrastructure complexity',
                'Integration challenges',
                'Global scale',
            ),
            'goals'       => array(
                'Enterprise stability',
                'Proper governance',
                'System integration',
                'Zero data loss',
            ),
        ),
    );

    /**
     * Get all defined personas
     *
     * @since  1.6030.2148
     * @return array Array of persona definitions keyed by persona slug.
     */
    public static function get_personas() {
        return self::$personas;
    }

    /**
     * Get specific persona definition
     *
     * @since  1.6030.2148
     * @param  string $persona_slug Persona identifier.
     * @return array|null Persona definition or null if not found.
     */
    public static function get_persona( $persona_slug ) {
        return self::$personas[ $persona_slug ] ?? null;
    }

    /**
     * Get all diagnostics for a specific persona, ranked by priority
     *
     * @since  1.6030.2148
     * @param  string $persona_slug Persona identifier.
     * @return array Array of diagnostic slugs ranked by priority for this persona.
     */
    public static function get_diagnostics_for_persona( $persona_slug ) {
        $diagnostics = self::get_persona_diagnostics_map();
        return $diagnostics[ $persona_slug ] ?? array();
    }

    /**
     * Get diagnostic priority for specific persona
     *
     * Developer humor: I have a joke about user personas, but only 3% of you will get it.
     *
     * @since  1.6030.2148
     * @param  string $diagnostic_slug Diagnostic identifier.
     * @param  string $persona_slug    Persona identifier.
     * @return int Priority score 1-100 (100 = highest priority).
     */
    public static function get_diagnostic_priority( $diagnostic_slug, $persona_slug ) {
        $map = self::get_priority_map();
        return $map[ $diagnostic_slug ][ $persona_slug ] ?? 0;
    }

    /**
     * Get all personas this diagnostic applies to
     *
     * @since  1.6030.2148
     * @param  string $diagnostic_slug Diagnostic identifier.
     * @return array Array of persona slugs this diagnostic applies to.
     */
    public static function get_personas_for_diagnostic( $diagnostic_slug ) {
        $map = self::get_diagnostic_to_personas_map();
        return $map[ $diagnostic_slug ] ?? array();
    }

    /**
     * Generate persona-specific action plan
     *
     * @since  1.6030.2148
     * @param  string $persona_slug Persona identifier.
     * @param  array  $findings     Array of findings from recent scan.
     * @return array {
     *     Structured action plan for this persona.
     *
     *     @type string   $title       Action plan title.
     *     @type array    $priorities  High-priority issues to fix first.
     *     @type array    $medium      Medium-priority improvements.
     *     @type array    $low         Low-priority enhancements.
     *     @type int      $time_estimate_hours Total hours to resolve.
     * }
     */
    public static function generate_action_plan( $persona_slug, $findings ) {
        $persona = self::get_persona( $persona_slug );
        if ( ! $persona ) {
            return array();
        }

        $diagnostics = self::get_diagnostics_for_persona( $persona_slug );
        $priorities = array();
        $medium = array();
        $low = array();
        $total_hours = 0;

        foreach ( $findings as $finding ) {
            $diagnostic_slug = $finding['id'] ?? '';
            $priority = self::get_diagnostic_priority( $diagnostic_slug, $persona_slug );

            if ( $priority >= 80 ) {
                $priorities[] = $finding;
                $total_hours += $finding['estimated_hours'] ?? 1;
            } elseif ( $priority >= 50 ) {
                $medium[] = $finding;
                $total_hours += $finding['estimated_hours'] ?? 0.5;
            } else {
                $low[] = $finding;
            }
        }

        return array(
            'title'                => sprintf(
                /* translators: %s: persona label */
                __( 'Action Plan for %s', 'wpshadow' ),
                $persona['label']
            ),
            'description'          => sprintf(
                /* translators: %s: persona description */
                __( '%s action plan focusing on: %s', 'wpshadow' ),
                $persona['label'],
                implode( ', ', $persona['goals'] )
            ),
            'critical_issues'      => $priorities,
            'recommended_fixes'    => $medium,
            'nice_to_have'         => $low,
            'total_hours_estimate' => $total_hours,
            'severity_distribution' => array(
                'critical' => count( $priorities ),
                'medium'   => count( $medium ),
                'low'      => count( $low ),
            ),
        );
    }

    /**
     * Get mapping of persona → array of diagnostics ranked by priority
     *
     * @since  1.6030.2148
     * @return array Personas mapped to their ranked diagnostic collections.
     */
    protected static function get_persona_diagnostics_map() {
        /**
         * Filter persona diagnostic collections
         *
         * @since 1.6030.2148
         *
         * @param array $diagnostics Persona → diagnostics mapping.
         */
        return apply_filters( 'wpshadow_persona_diagnostics_map', array(
            'diy-owner'      => array(
                'backup_last_run',
                'backup_size_normal',
                'site_uptime_status',
                'ssl_cert_expiration',
                'email_delivery_test',
                'php_memory_limit',
                'wordpress_updates_available',
                'plugin_compatibility',
                'disk_space_available',
                'database_size_growth',
                'admin_users_count',
                'failed_login_attempts',
                'wp_config_writable',
                'file_upload_success',
                'theme_active_valid',
                'child_theme_in_use',
                'search_engine_visibility',
                'spam_comments_ratio',
                'dead_links_detection',
                'https_redirect_working',
            ),
            'agency'         => array(
                'downtime_alert_status',
                'client_site_uptime_percent',
                'database_query_performance',
                'plugin_update_status',
                'theme_update_status',
                'backup_restoration_test',
                'ssl_certificate_health',
                'email_smtp_working',
                'redis_cache_active',
                'php_version_current',
                'database_corruption_scan',
                'malware_signature_scan',
                'admin_user_audit',
                'plugin_security_check',
                'file_permission_issues',
                'api_rate_limits',
                'wp_cron_working',
                'memory_exhaustion_risk',
                'log_file_age',
                'support_ticket_patterns',
            ),
            'ecommerce'      => array(
                'checkout_speed',
                'payment_gateway_status',
                'ssl_certificate_valid',
                'cart_abandonment_tracking',
                'downtime_detection',
                'database_query_performance',
                'product_image_optimization',
                'inventory_accuracy',
                'order_processing_speed',
                'email_delivery',
                'payment_failure_rate',
                'fraud_detection_active',
                'ssl_cert_expiration',
                'cdn_performance',
                'mobile_checkout_optimization',
                'gdpr_privacy_policy',
                'pci_dss_compliance',
                'product_page_load_time',
                'search_performance',
                'coupon_system_health',
            ),
            'publisher'      => array(
                'page_load_speed',
                'core_web_vitals_score',
                'seo_meta_tags',
                'seo_indexation',
                'xml_sitemap_updated',
                'backups_recent',
                'orphaned_posts',
                'author_activity',
                'comment_moderation_queue',
                'comment_spam_filter',
                'image_optimization',
                'article_structure',
                'internal_linking_health',
                'newsletter_delivery',
                'social_media_sharing',
                'mobile_responsiveness',
                'reading_time_accuracy',
                'content_duplicate_detection',
                'content_freshness',
                'analytics_tracking',
            ),
            'developer'      => array(
                'theme_framework_quality',
                'custom_code_standards',
                'database_query_efficiency',
                'asset_minification',
                'plugin_load_order',
                'custom_post_types_valid',
                'custom_taxonomy_valid',
                'hook_system_health',
                'rest_api_endpoints',
                'theme_options_valid',
                'child_theme_structure',
                'translation_ready',
                'accessibility_compliance',
                'schema_markup',
                'performance_baseline',
                'security_headers',
                'error_logging',
                'database_optimization',
                'staging_environment',
                'version_control',
            ),
            'corporate'      => array(
                'backup_encryption_status',
                'data_retention_policy',
                'access_control_list',
                'encryption_at_rest',
                'encryption_in_transit',
                'gdpr_cookie_consent',
                'data_export_capability',
                'gdpr_data_deletion',
                'audit_log_activity',
                'audit_log_retention',
                'user_role_segregation',
                'change_log_available',
                'security_check_status',
                'penetration_test_results',
                'dlp_rules_configured',
                'incident_response_plan',
                'business_continuity_plan',
                'disaster_recovery_plan',
                'security_monitoring',
                'compliance_checklist',
            ),
            'enterprise-corp' => array(
                'high_availability_setup',
                'multi_region_deployment',
                'database_replication',
                'failover_tested',
                'api_rate_limiting',
                'oauth2_sso_integration',
                'ldap_active_directory',
                'vpn_secure_access',
                'waf_rules',
                'ddos_mitigation',
                'container_orchestration',
                'infrastructure_as_code',
                'monitoring_stack',
                'log_aggregation',
                'alerting_system',
                'change_management_process',
                'disaster_recovery_rto',
                'disaster_recovery_rpo',
                'api_documentation',
                'sla_monitoring_dashboard',
            ),
        ) );
    }

    /**
     * Get priority scoring map (diagnostic → persona → priority score)
     *
     * @since  1.6030.2148
     * @return array Priority mapping for all diagnostics across personas.
     */
    protected static function get_priority_map() {
        /**
         * Filter diagnostic priority by persona
         *
         * @since 1.6030.2148
         *
         * @param array $priorities Diagnostic → persona priority mapping.
         */
        return apply_filters( 'wpshadow_diagnostic_priority_map', array(
            'backup_last_run'             => array(
                'diy-owner'      => 100,
                'agency'         => 95,
                'ecommerce'      => 100,
                'publisher'      => 95,
                'developer'      => 60,
                'corporate'      => 100,
                'enterprise-corp' => 95,
            ),
            'ssl_cert_expiration'         => array(
                'diy-owner'      => 95,
                'agency'         => 95,
                'ecommerce'      => 100,
                'publisher'      => 90,
                'developer'      => 85,
                'corporate'      => 95,
                'enterprise-corp' => 90,
            ),
            'downtime_alert_status'       => array(
                'diy-owner'      => 90,
                'agency'         => 100,
                'ecommerce'      => 100,
                'publisher'      => 80,
                'developer'      => 60,
                'corporate'      => 100,
                'enterprise-corp' => 100,
            ),
            'payment_gateway_status'      => array(
                'diy-owner'      => 20,
                'agency'         => 30,
                'ecommerce'      => 100,
                'publisher'      => 5,
                'developer'      => 50,
                'corporate'      => 20,
                'enterprise-corp' => 30,
            ),
            'page_load_speed'             => array(
                'diy-owner'      => 70,
                'agency'         => 80,
                'ecommerce'      => 95,
                'publisher'      => 100,
                'developer'      => 90,
                'corporate'      => 60,
                'enterprise-corp' => 75,
            ),
            'database_query_performance'  => array(
                'diy-owner'      => 60,
                'agency'         => 85,
                'ecommerce'      => 90,
                'publisher'      => 85,
                'developer'      => 100,
                'corporate'      => 70,
                'enterprise-corp' => 85,
            ),
            'security_monitoring'         => array(
                'diy-owner'      => 60,
                'agency'         => 85,
                'ecommerce'      => 80,
                'publisher'      => 50,
                'developer'      => 75,
                'corporate'      => 100,
                'enterprise-corp' => 100,
            ),
            'seo_meta_tags'               => array(
                'diy-owner'      => 40,
                'agency'         => 50,
                'ecommerce'      => 60,
                'publisher'      => 95,
                'developer'      => 70,
                'corporate'      => 10,
                'enterprise-corp' => 5,
            ),
        ) );
    }

    /**
     * Get mapping of diagnostic → personas it applies to
     *
     * @since  1.6030.2148
     * @return array Diagnostic → personas mapping.
     */
    protected static function get_diagnostic_to_personas_map() {
        $persona_map = self::get_persona_diagnostics_map();
        $diagnostic_to_personas = array();

        foreach ( $persona_map as $persona => $diagnostics ) {
            foreach ( $diagnostics as $diagnostic ) {
                if ( ! isset( $diagnostic_to_personas[ $diagnostic ] ) ) {
                    $diagnostic_to_personas[ $diagnostic ] = array();
                }
                $diagnostic_to_personas[ $diagnostic ][] = $persona;
            }
        }

        return $diagnostic_to_personas;
    }
}
