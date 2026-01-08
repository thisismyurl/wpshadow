<?php
/**
 * Modules Dashboard View
 *
<?php
/**
 * Modules Dashboard View
 *
 * @package TIMU_Core_Support
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Normalize modules input.
$modules = isset( $modules ) && is_array( $modules ) ? $modules : array();
// Grouping arrays for rendering.
$hub_modules   = array_filter( $modules, static fn( $m ) => ( $m['type'] ?? '' ) === 'hub' );
$spoke_modules = array_filter( $modules, static fn( $m ) => ( $m['type'] ?? '' ) === 'spoke' );
?>
<div class="wrap timu-dashboard-wrap">
	<div class="timu-dashboard-header">
		<h1><?php esc_html_e( 'Support Dashboard', 'core-support-thisismyurl' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Manage your @thisismyurl Support Suite modules and settings.', 'core-support-thisismyurl' ); ?>
		</p>
	</div>


	<?php
	$modules_url = is_network_admin()
		? network_admin_url( 'admin.php?page=timu-core-modules' )
		: admin_url( 'admin.php?page=timu-core-modules' );
	?>
	<p>
		<a href="<?php echo esc_url( $modules_url ); ?>" class="button button-primary"><?php esc_html_e( 'Go to Modules', 'core-support-thisismyurl' ); ?></a>
	</p>
</div>
