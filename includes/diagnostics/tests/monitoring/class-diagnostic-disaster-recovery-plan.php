<?php
/**
 * Disaster Recovery Plan Diagnostic
 *
 * Analyzes disaster recovery documentation and procedures.
 *
 * @since   1.6033.2150
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disaster Recovery Plan Diagnostic
 *
 * Evaluates disaster recovery preparedness and documentation.
 *
 * @since 1.6033.2150
 */
class Diagnostic_Disaster_Recovery_Plan extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disaster-recovery-plan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery Plan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes disaster recovery documentation and procedures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2150
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins (indicates some disaster planning)
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'duplicator/duplicator.php'   => 'Duplicator',
			'jetpack/jetpack.php'         => 'Jetpack Backup',
		);

		$active_backup = null;
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup = $name;
				break;
			}
		}

		// Check for disaster recovery plan documentation
		$has_recovery_plan = get_option( 'wpshadow_has_disaster_recovery_plan' );
		$recovery_plan_updated = get_option( 'wpshadow_recovery_plan_updated' );
		$days_since_update = $recovery_plan_updated ? floor( ( time() - $recovery_plan_updated ) / DAY_IN_SECONDS ) : 999;

		// Check for emergency contact configuration
		$has_emergency_contact = get_option( 'wpshadow_emergency_contact' );

		// Estimate site criticality
		$post_count = wp_count_posts()->publish ?? 0;
		$is_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$is_high_value = $post_count > 100 || $is_woocommerce;

		// Generate findings if no disaster plan
		if ( ! $has_recovery_plan && $is_high_value ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No disaster recovery plan documented. High-value sites need documented recovery procedures.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-plan',
				'meta'         => array(
					'has_recovery_plan'   => $has_recovery_plan,
					'is_high_value'       => $is_high_value,
					'is_woocommerce'      => $is_woocommerce,
					'recommendation'      => 'Document disaster recovery procedures',
					'plan_components'     => array(
						'Emergency contact information',
						'Backup restoration procedures',
						'DNS/hosting credentials',
						'Recovery time objectives (RTO)',
						'Recovery point objectives (RPO)',
						'Communication plan',
					),
					'recovery_scenarios'  => array(
						'Site hacked/defaced',
						'Database corruption',
						'Server failure',
						'Accidental deletion',
						'Plugin/theme conflict',
						'DNS/hosting issues',
					),
					'documentation_location' => 'Store securely outside site (password manager, wiki)',
				),
			);
		}

		// Alert if plan outdated
		if ( $has_recovery_plan && $days_since_update > 180 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days since plan updated */
					__( 'Disaster recovery plan last updated %d days ago. Review and update quarterly.', 'wpshadow' ),
					$days_since_update
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-plan',
				'meta'         => array(
					'days_since_update' => $days_since_update,
					'recommendation'    => 'Review and update recovery procedures',
					'update_triggers'   => array(
						'Hosting provider changes',
						'New critical plugins',
						'Team member changes',
						'Backup strategy changes',
					),
				),
			);
		}

		// Warning if no emergency contacts
		if ( ! $has_emergency_contact ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No emergency contacts configured. Document key contacts for disaster scenarios.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-plan',
				'meta'         => array(
					'recommendation'     => 'Document emergency contacts',
					'key_contacts'       => array(
						'Hosting provider support',
						'DNS provider support',
						'Development team lead',
						'Database administrator',
						'Security incident response',
					),
				),
			);
		}

		return null;
	}
}
