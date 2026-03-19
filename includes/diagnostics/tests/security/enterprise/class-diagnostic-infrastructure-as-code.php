<?php
/**
 * Infrastructure as Code Diagnostic
 *
 * Checks if infrastructure is managed through code for consistency and automation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Infrastructure as Code Diagnostic Class
 *
 * Verifies that infrastructure is defined and managed through code (IaC) tools
 * for reproducibility, version control, and automated deployments.
 *
 * @since 1.6093.1200
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
	protected static $title = 'Infrastructure as Code (IaC)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if infrastructure is managed through code for consistency and automation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the infrastructure as code diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if IaC gaps detected, null otherwise.
	 */
	public static function check() {
		$iac_tools      = array();
		$missing_tools  = array();
		$warnings       = array();
		$root_path      = ABSPATH;
		$parent_path    = dirname( $root_path );
		$search_paths   = array( $root_path, $parent_path );

		// Check for Terraform.
		$terraform_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/.terraform' ) || 
				 file_exists( $path . '/terraform.tfstate' ) ||
				 file_exists( $path . '/main.tf' ) ||
				 self::scan_for_files( $path, '*.tf' ) ) {
				$iac_tools['terraform'] = __( 'Terraform configuration detected', 'wpshadow' );
				$terraform_found        = true;
				break;
			}
		}

		// Check for Ansible.
		$ansible_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/ansible.cfg' ) || 
				 file_exists( $path . '/playbook.yml' ) ||
				 file_exists( $path . '/playbooks' ) ||
				 self::scan_for_files( $path, 'playbook*.yml' ) ) {
				$iac_tools['ansible'] = __( 'Ansible playbooks detected', 'wpshadow' );
				$ansible_found        = true;
				break;
			}
		}

		// Check for CloudFormation.
		$cloudformation_found = false;
		foreach ( $search_paths as $path ) {
			if ( self::scan_for_files( $path, '*cloudformation*.yml' ) ||
				 self::scan_for_files( $path, '*cloudformation*.yaml' ) ||
				 self::scan_for_files( $path, '*cloudformation*.json' ) ) {
				$iac_tools['cloudformation'] = __( 'AWS CloudFormation templates detected', 'wpshadow' );
				$cloudformation_found        = true;
				break;
			}
		}

		// Check for Pulumi.
		$pulumi_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/Pulumi.yaml' ) || 
				 file_exists( $path . '/Pulumi.yml' ) ||
				 file_exists( $path . '/.pulumi' ) ) {
				$iac_tools['pulumi'] = __( 'Pulumi configuration detected', 'wpshadow' );
				$pulumi_found        = true;
				break;
			}
		}

		// Check for Docker Compose (infrastructure definition).
		$docker_compose_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/docker-compose.yml' ) || 
				 file_exists( $path . '/docker-compose.yaml' ) ||
				 file_exists( $path . '/compose.yml' ) ||
				 file_exists( $path . '/compose.yaml' ) ) {
				$iac_tools['docker_compose'] = __( 'Docker Compose configuration detected', 'wpshadow' );
				$docker_compose_found        = true;
				break;
			}
		}

		// Check for Kubernetes manifests.
		$kubernetes_found = false;
		foreach ( $search_paths as $path ) {
			if ( is_dir( $path . '/k8s' ) || 
				 is_dir( $path . '/kubernetes' ) ||
				 self::scan_for_files( $path, 'deployment*.yaml' ) ||
				 self::scan_for_files( $path, 'service*.yaml' ) ) {
				$iac_tools['kubernetes'] = __( 'Kubernetes manifests detected', 'wpshadow' );
				$kubernetes_found        = true;
				break;
			}
		}

		// Check for Helm charts.
		$helm_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/Chart.yaml' ) || 
				 is_dir( $path . '/charts' ) ||
				 is_dir( $path . '/helm' ) ) {
				$iac_tools['helm'] = __( 'Helm charts detected', 'wpshadow' );
				$helm_found        = true;
				break;
			}
		}

		// Check for Chef.
		$chef_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/Berksfile' ) || 
				 file_exists( $path . '/metadata.rb' ) ||
				 is_dir( $path . '/cookbooks' ) ) {
				$iac_tools['chef'] = __( 'Chef cookbooks detected', 'wpshadow' );
				$chef_found        = true;
				break;
			}
		}

		// Check for Puppet.
		$puppet_found = false;
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/Puppetfile' ) || 
				 is_dir( $path . '/manifests' ) ||
				 self::scan_for_files( $path, '*.pp' ) ) {
				$iac_tools['puppet'] = __( 'Puppet manifests detected', 'wpshadow' );
				$puppet_found        = true;
				break;
			}
		}

		// Count how many IaC tools are in use.
		$iac_count = count( $iac_tools );

		// Check if infrastructure appears to be cloud-hosted (more critical for IaC).
		$is_cloud_hosted = false;
		if ( defined( 'AWS_ACCESS_KEY_ID' ) || 
			 defined( 'GOOGLE_CLOUD_PROJECT' ) || 
			 defined( 'AZURE_TENANT_ID' ) ||
			 getenv( 'AWS_REGION' ) ||
			 getenv( 'GCP_PROJECT' ) ) {
			$is_cloud_hosted = true;
		}

		// Critical if cloud-hosted with no IaC.
		if ( $is_cloud_hosted && 0 === $iac_count ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cloud infrastructure detected but no Infrastructure as Code (IaC) tools found. Manual infrastructure changes are error-prone and not version-controlled.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/infrastructure-as-code',
				'context'      => array(
					'iac_tools'       => $iac_tools,
					'is_cloud_hosted' => $is_cloud_hosted,
				),
			);
		}

		// High priority if no IaC at all.
		if ( 0 === $iac_count ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No Infrastructure as Code (IaC) tools detected. Consider using Terraform, Ansible, or similar tools to manage infrastructure through version-controlled code.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/infrastructure-as-code',
				'context'      => array(
					'iac_tools' => $iac_tools,
				),
			);
		}

		// Check for best practices.
		// Check if state files are in version control (bad practice for Terraform).
		foreach ( $search_paths as $path ) {
			if ( file_exists( $path . '/.git' ) && file_exists( $path . '/terraform.tfstate' ) ) {
				$gitignore_path = $path . '/.gitignore';
				if ( file_exists( $gitignore_path ) ) {
					$gitignore_content = file_get_contents( $gitignore_path );
					if ( false === strpos( $gitignore_content, 'terraform.tfstate' ) ) {
						$warnings[] = __( 'Terraform state file should be excluded from version control', 'wpshadow' );
					}
				}
			}
		}

		// Low severity if IaC is in place but has warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of IaC tools, 2: list of warnings */
					__( 'Infrastructure as Code is configured (%1$d tool(s) detected) but has recommendations: %2$s', 'wpshadow' ),
					$iac_count,
					implode( ', ', $warnings )
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/infrastructure-as-code',
				'context'      => array(
					'iac_tools' => $iac_tools,
					'warnings'  => $warnings,
				),
			);
		}

		return null; // IaC is properly configured.
	}

	/**
	 * Scan for files matching a pattern in a directory.
	 *
	 * @since 1.6093.1200
	 * @param  string $path    Directory path to scan.
	 * @param  string $pattern Glob pattern to match.
	 * @return bool True if matching files found, false otherwise.
	 */
	private static function scan_for_files( $path, $pattern ) {
		if ( ! is_dir( $path ) || ! is_readable( $path ) ) {
			return false;
		}

		$files = glob( $path . '/' . $pattern );
		return ! empty( $files );
	}
}
