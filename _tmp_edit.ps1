$file = "c:\Users\Owner\Local Sites\dev\app\public\wp-content\plugins\plugin-wp-support-thisismyurl\wp-support-thisismyurl.php"
$lines = [System.Collections.Generic.List[string]](Get-Content -Path $file -Encoding UTF8)

function FindIndex($list, $predicate){
    for($i=0; $i -lt $list.Count; $i++){
        if (& $predicate $list[$i]) { return $i }
    }
    return -1
}

function ToLines([string]$text){
    return [string[]]($text -split "`n")
}

# Insert screen option before footer postbox init
$idxComment = FindIndex $lines { param($l) $l -like "*Initialize postboxes on this screen (drag/toggle) in footer*" }
if ($idxComment -lt 0) { throw "Comment for postbox init not found" }
$insert1 = ToLines @'
	// Settings use a single-column layout.
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 1,
			'default' => 1,
		)
	);

'@
$insert1 += ""
$lines.InsertRange($idxComment, $insert1)

# Update settings layout to single column
$idxSettingsH1 = FindIndex $lines { param($l) $l -like "*<h1><?php echo esc_html( $settings_title ); ?></h1>*" }
if ($idxSettingsH1 -lt 0) { throw "Settings heading not found" }
$lines.RemoveRange($idxSettingsH1 + 1, 8)
$settingsBlock = ToLines @'
		<div id="dashboard-widgets" class="metabox-holder columns-1">
			<div id="postbox-container-1" class="postbox-container" style="width:100%;">
				<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
				<?php do_meta_boxes( $screen->id, 'side', null ); ?>
			</div>
		</div>
'@
$lines.InsertRange($idxSettingsH1 + 1, $settingsBlock)

# Route help tab to help renderer
$idxRegister = FindIndex $lines { param($l) $l -like "*case 'register':*" }
if ($idxRegister -lt 0) { throw "Register case not found" }
$idxHelp = -1
for($i=$idxRegister+1; $i -lt $lines.Count; $i++){
    if ($lines[$i] -like "*case 'help':*") { $idxHelp = $i; break }
}
if ($idxHelp -lt 0) { throw "Core help case not found" }
$lines.RemoveRange($idxHelp, 4)
$helpBlock = ToLines @'
		case 'help':
			wp_support_render_help_page();
			break;
'@
$lines.InsertRange($idxHelp, $helpBlock)

# Update dashboard layout
$idxDashH1 = FindIndex $lines { param($l) $l -like "*<h1><?php echo esc_html( $dashboard_title ); ?></h1>*" }
if ($idxDashH1 -lt 0) { throw "Dashboard heading not found" }
$lines.RemoveRange($idxDashH1 + 1, 8)
$dashBlock = ToLines @'
		<div class="license-status-container" style="width:100%;">
			<?php do_meta_boxes( $screen->id, 'license', null ); ?>
		</div>
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container" style="width:66%;">
				<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container" style="width:33%;">
				<?php do_meta_boxes( $screen->id, 'side', null ); ?>
			</div>
		</div>
'@
$lines.InsertRange($idxDashH1 + 1, $dashBlock)

# Insert help page functions before modules view
$idxModulesComment = FindIndex $lines { param($l) $l -like "*Render modules view.*" }
if ($idxModulesComment -lt 0) { throw "Modules comment not found" }
$helpFunctions = ToLines @'
/**
 * Render help page (license-focused).
 *
 * @return void
 */
function wp_support_render_help_page(): void {
	if ( ! wps_can_access_dashboard() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Force single-column layout for help.
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 1,
			'default' => 1,
		)
	);

	// Ensure license widget is available; add fallback if missing.
	global $wp_meta_boxes;
	$license_boxes      = $wp_meta_boxes[ $screen->id ]['license'] ?? array();
	$has_license_widget = false;

	foreach ( $license_boxes as $priority_boxes ) {
		if ( empty( $priority_boxes ) || ! is_array( $priority_boxes ) ) {
			continue;
		}
		foreach ( $priority_boxes as $box ) {
			if ( isset( $box['id'] ) && 'wps_license_widget' === $box['id'] ) {
				$has_license_widget = true;
				break 2;
			}
		}
	}

	if ( ! $has_license_widget ) {
		add_meta_box(
			'wps_help_license_status',
			__( 'License Status', 'plugin-wp-support-thisismyurl' ),
			__NAMESPACE__ . '\render_help_content',
			$screen->id,
			'license',
			'core'
		);
	}

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Help', 'plugin-wp-support-thisismyurl' ); ?></h1>
		<div class="license-status-container" style="width:100%;">
			<?php do_meta_boxes( $screen->id, 'license', null ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render fallback help content.
 *
 * @return void
 */
function render_help_content(): void {
	echo '<p>' . esc_html__( 'Review your license status to access updates and support.', 'plugin-wp-support-thisismyurl' ) . '</p>';
}

'@
$helpFunctions += ""
$lines.InsertRange($idxModulesComment, $helpFunctions)

$lines | Set-Content -Path $file -Encoding UTF8
