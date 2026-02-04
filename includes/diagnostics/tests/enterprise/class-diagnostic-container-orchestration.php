<?php
/**
 * Container Orchestration Diagnostic
 *
 * Checks if enterprise applications are using container orchestration for scalability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Container Orchestration Diagnostic Class
 *
 * Verifies that enterprise WordPress applications are deployed with proper
 * container orchestration for scalability, resilience, and automated deployments.
 *
 * @since 1.6035.1200
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
	protected static $description = 'Checks if enterprise applications are using container orchestration for scalability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the container orchestration diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if orchestration gaps detected, null otherwise.
	 */
	public static function check() {
		$orchestration_detected = false;
		$orchestration_type     = '';
		$configuration_issues   = array();
		$recommendations        = array();

		// Check for Kubernetes.
		$kubernetes_indicators = array(
			'KUBERNETES_SERVICE_HOST',
			'KUBERNETES_PORT',
			'K8S_NODE_NAME',
			'KUBE_DNS_SERVICE_HOST',
		);

		foreach ( $kubernetes_indicators as $indicator ) {
			if ( getenv( $indicator ) !== false ) {
				$orchestration_detected = true;
				$orchestration_type     = 'Kubernetes';
				break;
			}
		}

		// Check for Docker Swarm.
		if ( ! $orchestration_detected ) {
			$swarm_indicators = array(
				'DOCKER_SWARM_MODE',
				'SWARM_NODE_ID',
			);

			foreach ( $swarm_indicators as $indicator ) {
				if ( getenv( $indicator ) !== false ) {
					$orchestration_detected = true;
					$orchestration_type     = 'Docker Swarm';
					break;
				}
			}
		}

		// Check for AWS ECS.
		if ( ! $orchestration_detected ) {
			if ( getenv( 'ECS_CONTAINER_METADATA_URI' ) !== false || 
				 getenv( 'ECS_CONTAINER_METADATA_URI_V4' ) !== false ) {
				$orchestration_detected = true;
				$orchestration_type     = 'AWS ECS';
			}
		}

		// Check for Google Cloud Run.
		if ( ! $orchestration_detected ) {
			if ( getenv( 'K_SERVICE' ) !== false || 
				 getenv( 'K_REVISION' ) !== false ||
				 getenv( 'K_CONFIGURATION' ) !== false ) {
				$orchestration_detected = true;
				$orchestration_type     = 'Google Cloud Run';
			}
		}

		// Check for Azure Container Instances.
		if ( ! $orchestration_detected ) {
			if ( getenv( 'ACI_RESOURCE_GROUP' ) !== false || 
				 getenv( 'CONTAINER_GROUP_NAME' ) !== false ) {
				$orchestration_detected = true;
				$orchestration_type     = 'Azure Container Instances';
			}
		}

		// Check for Nomad.
		if ( ! $orchestration_detected ) {
			if ( getenv( 'NOMAD_ALLOC_ID' ) !== false || 
				 getenv( 'NOMAD_TASK_NAME' ) !== false ) {
				$orchestration_detected = true;
				$orchestration_type     = 'HashiCorp Nomad';
			}
		}

		// Check for Mesos/Marathon.
		if ( ! $orchestration_detected ) {
			if ( getenv( 'MARATHON_APP_ID' ) !== false || 
				 getenv( 'MESOS_TASK_ID' ) !== false ) {
				$orchestration_detected = true;
				$orchestration_type     = 'Apache Mesos/Marathon';
			}
		}

		// If no orchestration detected, check if we're in a container at all.
		$in_container = self::is_running_in_container();

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and not using orchestration, that's a problem.
		if ( $is_enterprise && ! $orchestration_detected ) {
			if ( $in_container ) {
				$description = __( 'Application is running in a container but without orchestration. For enterprise scalability, consider Kubernetes, Docker Swarm, or managed container services (ECS, Cloud Run, AKS).', 'wpshadow' );
				$severity    = 'high';
				$threat      = 75;
			} else {
				$description = __( 'Enterprise application is not using containerization or orchestration. This limits scalability, deployment automation, and resilience capabilities.', 'wpshadow' );
				$severity    = 'medium';
				$threat      = 65;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/container-orchestration',
				'context'      => array(
					'in_container'  => $in_container,
					'is_enterprise' => $is_enterprise,
				),
			);
		}

		// If orchestration is detected, check for best practices.
		if ( $orchestration_detected ) {
			// Check for health check endpoints.
			if ( ! file_exists( ABSPATH . 'health-check.php' ) && 
				 ! file_exists( ABSPATH . 'healthcheck.php' ) &&
				 ! file_exists( ABSPATH . 'wp-content/health.php' ) ) {
				$configuration_issues[] = __( 'No health check endpoint found for liveness/readiness probes', 'wpshadow' );
			}

			// Check for resource limits awareness (Kubernetes specific).
			if ( 'Kubernetes' === $orchestration_type ) {
				$memory_limit = ini_get( 'memory_limit' );
				if ( '-1' === $memory_limit ) {
					$recommendations[] = __( 'PHP memory_limit is unlimited - should align with container memory limits', 'wpshadow' );
				}

				// Check if we can detect resource limits.
				$cgroup_memory = self::get_container_memory_limit();
				if ( $cgroup_memory && $memory_limit !== '-1' ) {
					$php_memory    = self::parse_memory_limit( $memory_limit );
					$memory_ratio  = $php_memory / $cgroup_memory;
					
					// PHP memory should be 70-80% of container memory.
					if ( $memory_ratio > 0.9 ) {
						$configuration_issues[] = __( 'PHP memory_limit is too close to container limit - risk of OOM kills', 'wpshadow' );
					}
				}
			}

			// Check for proper session handling in orchestrated environments.
			$session_handler = ini_get( 'session.save_handler' );
			if ( 'files' === $session_handler ) {
				$recommendations[] = __( 'Using file-based sessions in orchestrated environment - consider Redis or Memcached for session storage', 'wpshadow' );
			}

			// Check if object cache is configured (important for multi-pod deployments).
			if ( ! wp_using_ext_object_cache() ) {
				$configuration_issues[] = __( 'No persistent object cache detected - critical for multi-instance deployments', 'wpshadow' );
			}

			// If we have configuration issues, report them.
			if ( ! empty( $configuration_issues ) ) {
				$description = sprintf(
					/* translators: 1: orchestration type, 2: list of issues */
					__( 'Running on %1$s but configuration needs improvement: %2$s', 'wpshadow' ),
					$orchestration_type,
					implode( '; ', $configuration_issues )
				);

				if ( ! empty( $recommendations ) ) {
					$description .= ' ' . __( 'Recommendations:', 'wpshadow' ) . ' ' . implode( '; ', $recommendations );
				}

				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => $description,
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/container-orchestration',
					'context'      => array(
						'orchestration_type'     => $orchestration_type,
						'configuration_issues'   => $configuration_issues,
						'recommendations'        => $recommendations,
					),
				);
			}

			// If we have recommendations but no critical issues.
			if ( ! empty( $recommendations ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: orchestration type, 2: list of recommendations */
						__( 'Running on %1$s with good configuration. Recommendations for optimization: %2$s', 'wpshadow' ),
						$orchestration_type,
						implode( '; ', $recommendations )
					),
					'severity'     => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/container-orchestration',
					'context'      => array(
						'orchestration_type' => $orchestration_type,
						'recommendations'    => $recommendations,
					),
				);
			}
		}

		return null; // Orchestration is properly configured or not needed.
	}

	/**
	 * Check if running in a container.
	 *
	 * @since  1.6035.1200
	 * @return bool True if in container, false otherwise.
	 */
	private static function is_running_in_container() {
		// Check for Docker.
		if ( file_exists( '/.dockerenv' ) ) {
			return true;
		}

		// Check cgroup for container indicators.
		if ( file_exists( '/proc/1/cgroup' ) ) {
			$cgroup = file_get_contents( '/proc/1/cgroup' );
			if ( strpos( $cgroup, 'docker' ) !== false || 
				 strpos( $cgroup, 'kubepods' ) !== false ||
				 strpos( $cgroup, 'lxc' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since  1.6035.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		// Check for enterprise indicators.
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			defined( 'AWS_ACCESS_KEY_ID' ),
			defined( 'GOOGLE_CLOUD_PROJECT' ),
			is_multisite() && get_blog_count() > 50,
		);

		return in_array( true, $enterprise_indicators, true );
	}

	/**
	 * Get container memory limit from cgroup.
	 *
	 * @since  1.6035.1200
	 * @return int|null Memory limit in bytes, or null if not available.
	 */
	private static function get_container_memory_limit() {
		// Check cgroup v2 first.
		if ( file_exists( '/sys/fs/cgroup/memory.max' ) ) {
			$max = trim( file_get_contents( '/sys/fs/cgroup/memory.max' ) );
			if ( is_numeric( $max ) ) {
				return (int) $max;
			}
		}

		// Check cgroup v1.
		if ( file_exists( '/sys/fs/cgroup/memory/memory.limit_in_bytes' ) ) {
			$max = trim( file_get_contents( '/sys/fs/cgroup/memory/memory.limit_in_bytes' ) );
			if ( is_numeric( $max ) ) {
				return (int) $max;
			}
		}

		return null;
	}

	/**
	 * Parse memory limit string to bytes.
	 *
	 * @since  1.6035.1200
	 * @param  string $limit Memory limit string (e.g., '256M', '1G').
	 * @return int Memory limit in bytes.
	 */
	private static function parse_memory_limit( $limit ) {
		$limit = trim( $limit );
		$last  = strtolower( $limit[ strlen( $limit ) - 1 ] );
		$value = (int) $limit;

		switch ( $last ) {
			case 'g':
				$value *= 1024;
				// Fall through.
			case 'm':
				$value *= 1024;
				// Fall through.
			case 'k':
				$value *= 1024;
		}

		return $value;
	}
}
