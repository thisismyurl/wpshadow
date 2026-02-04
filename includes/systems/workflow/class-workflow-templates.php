<?php
/**
 * Workflow Templates - Curated template library with categories
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages workflow template library (extends examples with categorization)
 * Philosophy: Helpful Neighbor (#1) - Provide ready-made solutions
 */
class Workflow_Templates {

	/**
	 * Get all templates organized by category (#569)
	 *
	 * @return array Templates grouped by category
	 */
	public static function get_all_templates(): array {
		return array(
			'security'    => array(
				'label'     => __( 'Security & Monitoring', 'wpshadow' ),
				'templates' => array(
					'daily_security_scan'     => array(
						'name'        => __( 'Daily Security Scan', 'wpshadow' ),
						'description' => __( 'Comprehensive security check every day at 3 AM', 'wpshadow' ),
						'icon'        => 'dashicons-shield-alt',
						'difficulty'  => 'beginner',
						'blocks'      => array(
							array(
								'type'   => 'trigger',
								'id'     => 'time_trigger',
								'config' => array(
									'frequency' => 'daily',
									'time'      => '03:00',
									'days'      => Block_Registry::get_default_days(),
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'run_diagnostic',
								'config' => array(
									'diagnostic_type'     => 'specific',
									'specific_diagnostic' => 'ssl',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'run_diagnostic',
								'config' => array(
									'diagnostic_type'     => 'specific',
									'specific_diagnostic' => 'debug_mode',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'send_email',
								'config' => array(
									'recipient' => 'admin',
									'subject'   => __( 'Daily Security Report', 'wpshadow' ),
									'message'   => __( 'Your daily security scan is complete.', 'wpshadow' ),
								),
							),
						),
					),
					'plugin_activation_alert' => array(
						'name'        => __( 'Plugin Activation Alert', 'wpshadow' ),
						'description' => __( 'Get notified when plugins are activated', 'wpshadow' ),
						'icon'        => 'dashicons-bell',
						'difficulty'  => 'beginner',
						'blocks'      => array(
							array(
								'type'   => 'trigger',
								'id'     => 'event_trigger',
								'config' => array(
									'event_type'    => 'plugin_state_changed',
									'plugin_action' => 'activated',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'send_email',
								'config' => array(
									'recipient' => 'admin',
									'subject'   => __( 'Plugin Activated', 'wpshadow' ),
									'message'   => __( 'A plugin was just activated on your site.', 'wpshadow' ),
								),
							),
						),
					),
				),
			),
			'maintenance' => array(
				'label'     => __( 'Maintenance & Cleanup', 'wpshadow' ),
				'templates' => array(
					'weekly_health_check' => array(
						'name'        => __( 'Weekly Health Check', 'wpshadow' ),
						'description' => __( 'Complete site health audit every Monday morning', 'wpshadow' ),
						'icon'        => 'dashicons-heart',
						'difficulty'  => 'beginner',
						'blocks'      => array(
							array(
								'type'   => 'trigger',
								'id'     => 'time_trigger',
								'config' => array(
									'frequency' => 'weekly',
									'time'      => '02:00',
									'days'      => array( 'monday' ),
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'run_diagnostic',
								'config' => array(
									'diagnostic_type' => 'full',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'send_email',
								'config' => array(
									'recipient'      => 'admin',
									'subject'        => __( 'Weekly Health Report', 'wpshadow' ),
									'message'        => __( 'Your weekly site health scan is complete.', 'wpshadow' ),
									'include_report' => true,
								),
							),
						),
					),
					'hourly_uptime_check' => array(
						'name'        => __( 'Hourly Uptime Monitor', 'wpshadow' ),
						'description' => __( 'Check site availability every hour', 'wpshadow' ),
						'icon'        => 'dashicons-clock',
						'difficulty'  => 'intermediate',
						'blocks'      => array(
							array(
								'type'   => 'trigger',
								'id'     => 'time_trigger',
								'config' => array(
									'frequency' => 'hourly',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'run_diagnostic',
								'config' => array(
									'diagnostic_type'     => 'specific',
									'specific_diagnostic' => 'backup',
								),
							),
						),
					),
				),
			),
			'performance' => array(
				'label'     => __( 'Performance & Optimization', 'wpshadow' ),
				'templates' => array(
					'auto_optimize' => array(
						'name'        => __( 'Auto-Optimize Daily', 'wpshadow' ),
						'description' => __( 'Run optimization treatments every night', 'wpshadow' ),
						'icon'        => 'dashicons-performance',
						'difficulty'  => 'advanced',
						'blocks'      => array(
							array(
								'type'   => 'trigger',
								'id'     => 'time_trigger',
								'config' => array(
									'frequency' => 'daily',
									'time'      => '04:00',
									'days'      => Block_Registry::get_default_days(),
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'apply_treatment',
								'config' => array(
									'specific_treatment' => 'image_lazy_load',
									'halt_on_error'      => false,
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'apply_treatment',
								'config' => array(
									'specific_treatment' => 'external_fonts',
									'halt_on_error'      => false,
								),
							),
						),
					),
				),
			),
			'content'     => array(
				'label'     => __( 'Content & Publishing', 'wpshadow' ),
				'templates' => array(
					'pre_publish_checklist' => array(
						'name'        => __( 'Pre-Publish Quality Check', 'wpshadow' ),
						'description' => __( 'Run checks before content goes live', 'wpshadow' ),
						'icon'        => 'dashicons-yes-alt',
						'difficulty'  => 'intermediate',
						'blocks'      => array(
							array(
								'type'   => 'trigger',
								'id'     => 'event_trigger',
								'config' => array(
									'event_type' => 'pre_publish_review',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'run_diagnostic',
								'config' => array(
									'diagnostic_type' => 'full',
								),
							),
							array(
								'type'   => 'action',
								'id'     => 'kanban_note',
								'config' => array(
									'title'       => __( 'Pre-Publish Check Complete', 'wpshadow' ),
									'description' => __( 'Content passed quality checks', 'wpshadow' ),
									'severity'    => 'low',
									'status'      => 'detected',
									'category'    => 'content',
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Get flattened template list (for dropdowns/selectors)
	 *
	 * @return array All templates with category metadata
	 */
	public static function get_flat_list(): array {
		$all_templates = self::get_all_templates();
		$flat          = array();

		foreach ( $all_templates as $category_slug => $category ) {
			foreach ( $category['templates'] as $template_slug => $template ) {
				$flat[ $template_slug ] = array_merge(
					$template,
					array(
						'category'       => $category_slug,
						'category_label' => $category['label'],
					)
				);
			}
		}

		return $flat;
	}

	/**
	 * Get templates by category
	 *
	 * @param string $category Category slug
	 * @return array Templates in category
	 */
	public static function get_by_category( string $category ): array {
		$all_templates = self::get_all_templates();
		return isset( $all_templates[ $category ] ) ? $all_templates[ $category ]['templates'] : array();
	}

	/**
	 * Get single template by slug
	 *
	 * @param string $template_slug Template identifier
	 * @return array|null Template data or null
	 */
	public static function get_template( string $template_slug ): ?array {
		$flat = self::get_flat_list();
		return isset( $flat[ $template_slug ] ) ? $flat[ $template_slug ] : null;
	}

	/**
	 * Create workflow from template
	 *
	 * @param string $template_slug Template identifier
	 * @param string $custom_name   Optional custom workflow name
	 * @return array Created workflow or error
	 */
	public static function create_from_template( string $template_slug, string $custom_name = '' ): array {
		$template = self::get_template( $template_slug );

		if ( ! $template ) {
			return array( 'error' => __( 'Template not found', 'wpshadow' ) );
		}

		$name        = ! empty( $custom_name ) ? $custom_name : $template['name'];
		$workflow_id = 'wf_' . wp_generate_uuid4();

		$workflow = Workflow_Manager::save_workflow( $name, $template['blocks'], $workflow_id );

		// Mark template as used (for analytics/rotation)
		self::mark_template_used( $template_slug );

		// Log activity (Philosophy #9: Show Value)
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'workflow_created_from_template',
				sprintf(
					/* translators: 1: workflow name, 2: template name */
					__( 'Workflow "%1$s" created from template "%2$s"', 'wpshadow' ),
					$name,
					$template['name']
				),
				'workflows',
				array(
					'workflow_id'   => $workflow_id,
					'template_slug' => $template_slug,
					'category'      => $template['category'],
				)
			);
		}

		return $workflow;
	}

	/**
	 * Mark template as used (for analytics)
	 *
	 * @param string $template_slug Template identifier
	 */
	private static function mark_template_used( string $template_slug ): void {
		$used = get_option( 'wpshadow_used_templates', array() );
		if ( ! isset( $used[ $template_slug ] ) ) {
			$used[ $template_slug ] = 0;
		}
		++$used[ $template_slug ];
		update_option( 'wpshadow_used_templates', $used );
	}

	/**
	 * Get template usage stats
	 *
	 * @return array Usage counts by template slug
	 */
	public static function get_usage_stats(): array {
		return get_option( 'wpshadow_used_templates', array() );
	}
}
