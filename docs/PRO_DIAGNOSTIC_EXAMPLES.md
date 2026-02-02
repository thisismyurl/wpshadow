# Pro/Cloud Diagnostic Examples

**Purpose:** Practical examples showing how to add upgrade paths to diagnostics

---

## Example 1: Backup Encryption (Vault)

### Before (Free-Only):
```php
<?php
class Diagnostic_Backup_Encryption_Not_Enabled extends Diagnostic_Base {
    protected static $slug = 'backup-encryption-not-enabled';
    protected static $title = 'Backup Encryption Not Enabled';
    protected static $description = 'Checks backup encryption';
    protected static $family = 'security';

    public static function check() {
        if ( ! get_option( 'backup_encryption_enabled' ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'Backup encryption not enabled. Encrypt all backups at rest using AES-256 or similar.', 'wpshadow' ),
                'severity'     => 'high',
                'threat_level' => 80,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/backup-encryption-not-enabled',
            );
        }
        return null;
    }
}
```

### After (With Upgrade Path):
```php
<?php
use WPShadow\Core\Upgrade_Path_Helper;

class Diagnostic_Backup_Encryption_Not_Enabled extends Diagnostic_Base {
    protected static $slug = 'backup-encryption-not-enabled';
    protected static $title = 'Backup Encryption Not Enabled';
    protected static $description = 'Checks backup encryption';
    protected static $family = 'security';

    public static function check() {
        if ( ! get_option( 'backup_encryption_enabled' ) ) {
            $finding = array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'Your backups are stored unencrypted. If your hosting account is compromised, sensitive customer data (emails, addresses, passwords) could be exposed to attackers.', 'wpshadow' ),
                'severity'     => 'high',
                'threat_level' => 80,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/backup-encryption',
            );

            // Only show upgrade path if they don't have Vault
            if ( ! Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
                $finding = Upgrade_Path_Helper::add_upgrade_path(
                    $finding,
                    'vault',
                    'automatic-encryption',
                    'https://wpshadow.com/kb/manual-backup-encryption' // DIY guide
                );
            }

            return $finding;
        }
        return null;
    }
}
```

**Result:** User sees educational message + DIY guide + optional Vault suggestion

---

## Example 2: Media Optimization (Media Image)

### Implementation:
```php
<?php
use WPShadow\Core\Upgrade_Path_Helper;

class Diagnostic_Media_Image_Optimization_Integration extends Diagnostic_Base {
    protected static $slug = 'media-image-optimization-integration';
    protected static $title = 'Image Optimization Not Configured';
    protected static $description = 'Checks image optimization';
    protected static $family = 'media';

    public static function check() {
        // Check if images are optimized for social media
        $unoptimized = self::count_unoptimized_images();
        
        if ( $unoptimized > 0 ) {
            $finding = array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    __( 'Found %d images not optimized for social media sharing. This results in slow load times on Facebook/Twitter (images are 3x larger than needed), cropping issues (wrong aspect ratios), and no branding consistency.', 'wpshadow' ),
                    $unoptimized
                ),
                'severity'     => 'medium',
                'threat_level' => 55,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/social-media-image-optimization',
            );

            // Show upgrade path if they don't have Media Image
            if ( ! Upgrade_Path_Helper::has_pro_product( 'media-image' ) ) {
                $finding = Upgrade_Path_Helper::add_upgrade_path(
                    $finding,
                    'media-image',
                    'social-optimization',
                    'https://wpshadow.com/kb/manual-social-optimization'
                );
            }

            return $finding;
        }
        return null;
    }

    private static function count_unoptimized_images() {
        // Logic to count unoptimized images
        return 42; // Example
    }
}
```

---

## Example 3: Storage Space (Vault)

### Implementation:
```php
<?php
use WPShadow\Core\Upgrade_Path_Helper;

class Diagnostic_Storage_Space_Availability extends Diagnostic_Base {
    protected static $slug = 'storage-space-availability';
    protected static $title = 'Storage Space Running Low';
    protected static $description = 'Checks available storage';
    protected static $family = 'functionality';

    public static function check() {
        $disk_free = disk_free_space( ABSPATH );
        $disk_total = disk_total_space( ABSPATH );
        $percent_free = ( $disk_free / $disk_total ) * 100;

        if ( $percent_free < 10 ) {
            $finding = array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    __( 'Your server has only %s of free space left (%d%%). When storage runs out, your site will stop working and you may lose data.', 'wpshadow' ),
                    size_format( $disk_free ),
                    round( $percent_free )
                ),
                'severity'     => 'high',
                'threat_level' => 60,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/storage-space-management',
            );

            // Suggest Vault for cloud offload
            if ( ! Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
                $finding = Upgrade_Path_Helper::add_upgrade_path(
                    $finding,
                    'vault',
                    'cloud-offload',
                    'https://wpshadow.com/kb/manual-storage-cleanup'
                );
            }

            return $finding;
        }
        return null;
    }
}
```

---

## Example 4: Design Tool Integration

### Implementation:
```php
<?php
use WPShadow\Core\Upgrade_Path_Helper;

class Diagnostic_Design_Tool_Integration_Not_Configured extends Diagnostic_Base {
    protected static $slug = 'design-tool-integration-not-configured';
    protected static $title = 'Design Tool Integration Not Configured';
    protected static $description = 'Checks design tool connectivity';
    protected static $family = 'integrations';

    public static function check() {
        // Check if user is manually uploading images (slow workflow)
        $manual_uploads = self::detect_manual_upload_pattern();

        if ( $manual_uploads ) {
            $finding = array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'You\'re manually uploading images from design tools like Canva or Figma. This creates version control issues (which file is latest?), no optimization pipeline (large file sizes), and a broken workflow (download → upload → optimize). The average time wasted: 15 minutes per image.', 'wpshadow' ),
                'severity'     => 'low',
                'threat_level' => 45,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/design-tool-webhooks',
            );

            // Show Integration upgrade path
            if ( ! Upgrade_Path_Helper::has_pro_product( 'integration' ) ) {
                $finding = Upgrade_Path_Helper::add_upgrade_path(
                    $finding,
                    'integration',
                    'design-tool-sync',
                    'https://wpshadow.com/kb/manual-webhook-setup'
                );
            }

            return $finding;
        }
        return null;
    }

    private static function detect_manual_upload_pattern() {
        // Logic to detect manual upload patterns
        // (e.g., check media library for consecutive uploads in short time)
        return true; // Example
    }
}
```

---

## UI Rendering Example

### Admin Template (includes/views/diagnostic-finding-card.php):

```php
<div class="wpshadow-finding-card severity-<?php echo esc_attr( $finding['severity'] ); ?>">
    <div class="finding-header">
        <h3><?php echo esc_html( $finding['title'] ); ?></h3>
        <span class="threat-level"><?php echo esc_html( $finding['threat_level'] ); ?></span>
    </div>

    <div class="finding-body">
        <p><?php echo esc_html( $finding['description'] ); ?></p>

        <!-- Manual solution (always show) -->
        <div class="finding-actions">
            <a href="<?php echo esc_url( $finding['kb_link'] ); ?>" 
               class="button button-secondary" 
               target="_blank">
                <?php esc_html_e( '📚 Learn How to Fix This Manually', 'wpshadow' ); ?>
            </a>
        </div>

        <!-- Upgrade path (only if available) -->
        <?php if ( isset( $finding['upgrade_path'] ) ) : ?>
            <div class="finding-upgrade-path">
                <hr>
                <h4><?php esc_html_e( '💡 Or Automate It:', 'wpshadow' ); ?></h4>
                <p>
                    <strong><?php echo esc_html( $finding['upgrade_path']['product_name'] ); ?></strong>
                </p>

                <ul class="upgrade-benefits">
                    <?php foreach ( $finding['upgrade_path']['benefits'] as $benefit ) : ?>
                        <li>✓ <?php echo esc_html( $benefit ); ?></li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?php echo esc_url( $finding['upgrade_path']['learn_more'] ); ?>" 
                   class="button button-primary" 
                   target="_blank"
                   data-track="upgrade-path-click"
                   data-product="<?php echo esc_attr( $finding['upgrade_path']['product'] ); ?>"
                   data-finding="<?php echo esc_attr( $finding['id'] ); ?>">
                    <?php esc_html_e( 'Learn More About ', 'wpshadow' ); ?>
                    <?php echo esc_html( $finding['upgrade_path']['product_name'] ); ?>
                </a>

                <!-- Manual guide link (if available) -->
                <?php if ( ! empty( $finding['upgrade_path']['manual_guide'] ) ) : ?>
                    <p class="upgrade-manual-alternative">
                        <small>
                            <?php esc_html_e( 'Or follow our ', 'wpshadow' ); ?>
                            <a href="<?php echo esc_url( $finding['upgrade_path']['manual_guide'] ); ?>" target="_blank">
                                <?php esc_html_e( 'step-by-step manual guide', 'wpshadow' ); ?>
                            </a>
                        </small>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
```

### CSS Styling:

```css
.wpshadow-finding-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    background: #fff;
}

.wpshadow-finding-card.severity-high {
    border-left: 4px solid #dc3232;
}

.wpshadow-finding-card.severity-medium {
    border-left: 4px solid #ffb900;
}

.finding-upgrade-path {
    margin-top: 20px;
    padding: 15px;
    background: #f0f6fc;
    border-radius: 4px;
}

.finding-upgrade-path h4 {
    margin-top: 0;
    color: #0073aa;
}

.upgrade-benefits {
    list-style: none;
    padding-left: 0;
    margin: 15px 0;
}

.upgrade-benefits li {
    padding: 5px 0;
    color: #046a38;
}

.upgrade-manual-alternative {
    margin-top: 10px;
    text-align: center;
    color: #666;
}
```

### JavaScript Tracking:

```javascript
// Track upgrade path clicks
jQuery(document).ready(function($) {
    $('[data-track="upgrade-path-click"]').on('click', function() {
        const product = $(this).data('product');
        const finding = $(this).data('finding');

        // Track click via AJAX
        $.post(ajaxurl, {
            action: 'wpshadow_track_upgrade_path_click',
            nonce: wpShadowData.nonce,
            product: product,
            finding_id: finding
        });
    });
});
```

---

## A/B Testing Implementation

### Test Variations:

```php
<?php
/**
 * Get upgrade path variation for A/B testing
 *
 * @param array $finding Diagnostic finding.
 * @return string Variation ID (A, B, or C).
 */
function get_upgrade_path_variation( $finding ) {
    // Get user's assigned variation (stored in user meta)
    $user_id = get_current_user_id();
    $variation = get_user_meta( $user_id, 'wpshadow_upgrade_ab_test', true );

    // Assign variation if not set (33/33/33 split)
    if ( empty( $variation ) ) {
        $rand = rand( 1, 100 );
        if ( $rand <= 33 ) {
            $variation = 'A'; // Educational only
        } elseif ( $rand <= 66 ) {
            $variation = 'B'; // Educational + subtle pro
        } else {
            $variation = 'C'; // Direct pro recommendation
        }
        update_user_meta( $user_id, 'wpshadow_upgrade_ab_test', $variation );
    }

    return $variation;
}

/**
 * Render upgrade path based on A/B test variation
 */
function render_upgrade_path_ab_test( $finding ) {
    $variation = get_upgrade_path_variation( $finding );

    switch ( $variation ) {
        case 'A':
            // Educational only (no pro mention)
            ?>
            <div class="finding-actions">
                <a href="<?php echo esc_url( $finding['kb_link'] ); ?>">
                    <?php esc_html_e( 'Learn How to Fix This', 'wpshadow' ); ?>
                </a>
            </div>
            <?php
            break;

        case 'B':
            // Educational + subtle pro mention (RECOMMENDED)
            ?>
            <div class="finding-actions">
                <a href="<?php echo esc_url( $finding['kb_link'] ); ?>">
                    <?php esc_html_e( '📚 Learn How to Fix This Manually', 'wpshadow' ); ?>
                </a>
            </div>
            <?php if ( isset( $finding['upgrade_path'] ) ) : ?>
                <div class="finding-upgrade-path-subtle">
                    <p>
                        💡 <strong><?php esc_html_e( 'Or automate it:', 'wpshadow' ); ?></strong>
                        <?php echo esc_html( $finding['upgrade_path']['product_name'] ); ?>
                        <a href="<?php echo esc_url( $finding['upgrade_path']['learn_more'] ); ?>">
                            <?php esc_html_e( 'Learn more', 'wpshadow' ); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>
            <?php
            break;

        case 'C':
            // Direct pro recommendation (more aggressive)
            ?>
            <?php if ( isset( $finding['upgrade_path'] ) ) : ?>
                <div class="finding-upgrade-path-prominent">
                    <h4><?php echo esc_html( $finding['upgrade_path']['product_name'] ); ?></h4>
                    <ul>
                        <?php foreach ( $finding['upgrade_path']['benefits'] as $benefit ) : ?>
                            <li>✓ <?php echo esc_html( $benefit ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo esc_url( $finding['upgrade_path']['learn_more'] ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Get Started', 'wpshadow' ); ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="finding-actions">
                <a href="<?php echo esc_url( $finding['kb_link'] ); ?>">
                    <?php esc_html_e( 'Or fix it manually', 'wpshadow' ); ?>
                </a>
            </div>
            <?php
            break;
    }

    // Track which variation was shown
    track_ab_test_impression( $finding['id'], $variation );
}
```

---

## Analytics Dashboard

### Conversion Funnel Report:

```php
<?php
/**
 * Display upgrade path analytics
 */
function display_upgrade_path_analytics() {
    $analytics = Upgrade_Path_Helper::get_analytics();
    ?>
    <div class="wpshadow-analytics-dashboard">
        <h2><?php esc_html_e( 'Upgrade Path Performance', 'wpshadow' ); ?></h2>

        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'wpshadow' ); ?></th>
                    <th><?php esc_html_e( 'Shown', 'wpshadow' ); ?></th>
                    <th><?php esc_html_e( 'Clicked', 'wpshadow' ); ?></th>
                    <th><?php esc_html_e( 'Click Rate', 'wpshadow' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $analytics as $product => $data ) : ?>
                    <tr>
                        <td><?php echo esc_html( $data['product_name'] ); ?></td>
                        <td><?php echo number_format_i18n( $data['shown'] ); ?></td>
                        <td><?php echo number_format_i18n( $data['clicked'] ); ?></td>
                        <td><?php echo esc_html( $data['click_rate'] ); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
```

---

**Status:** Examples complete. Ready for implementation in actual diagnostic files.
