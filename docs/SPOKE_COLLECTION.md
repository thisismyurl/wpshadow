# Spoke Collection Milestones & Unlock System

## Overview

The Spoke Collection system provides a gamified interface for discovering, installing, and activating format-specific image processing plugins (called "spokes"). Users collect spokes like achievements, unlocking milestones and rewards as they expand their media capabilities.

## Features

### Visual Collection Gallery

Located in **Support Hub → Spoke Collection** tab, the gallery displays all 8 available spoke formats:

- **AVIF** - Next-generation format (50% smaller than JPEG)
- **WebP** - Modern format with broad browser support
- **SVG** - Scalable vector graphics
- **TIFF** - High-quality professional photography
- **BMP** - Uncompressed bitmap for legacy compatibility
- **GIF** - Animated image format
- **HEIC** - Apple's modern image format
- **RAW** - Professional camera raw format

### Four Status States

Each spoke card visually represents its current state:

1. **Locked** (Gray, 40% opacity)
   - Not installed
   - Shows "Install This Spoke" button
   - Lock icon badge

2. **Unlocked** (Indigo, 70% opacity)
   - Installed but inactive
   - Shows "Activate" button
   - Unlock icon badge

3. **Active** (Green, 100% opacity)
   - Installed and enabled
   - Processing images
   - Checkmark badge
   - Shows metrics (files processed, space saved)

4. **Mastered** (Gold with glow, 100% opacity)
   - 1,000+ images converted
   - Golden appearance with glow effect
   - Star badge
   - Maximum metrics display

### Milestone System

#### System-Wide Milestones

- **First Format Unlocked** - Install your first spoke (threshold: 1)
- **Multi-Format Master** - Install 3+ spokes (reward: Speed Boost theme)
- **Full Collection Achieved** - Install all 8 spokes (reward: Master Optimizer admin color scheme)
- **Format Expert** - Convert 1,000+ images total (reward: Exclusive dashboard skin)

#### Per-Format Milestones

For each spoke format:
- **First Conversion** - Convert your first image (1 image)
- **Format Apprentice** - Convert 100 images
- **Format Master** - Convert 1,000 images
- **Format Legend** - Convert 5,000+ images

### Progress Tracking

The collection dashboard displays:
- **Collection Progress** - Circular progress indicator (0-100%)
- **Spokes Installed** - Count of installed spokes (X/8)
- **Active Spokes** - Count of currently active spokes
- **Files Converted** - Total images processed across all formats
- **Space Saved** - Total storage saved through optimization

### Interactive Animations

#### Installation Animation
1. Lock icon fades away
2. Spoke icon animates from gray to colored
3. Confetti particles burst from center
4. Toast notification appears
5. Status updates to "Activate Now"

#### Activation Animation
1. Icon glows and pulses for 2 seconds
2. Badge changes to green checkmark
3. Metrics counters start appearing
4. Celebration toast notification

#### Milestone Achievement
1. Modal popup slides in with bounce animation
2. Golden star icon spins
3. Achievement name and description displayed
4. Reward information shown
5. "Awesome!" button to close

## Technical Implementation

### Backend Classes

#### WPS_Spoke_Collection
Main collection management class located in `includes/class-wps-spoke-collection.php`:

```php
// Get status of a specific spoke
$status = WPS_Spoke_Collection::get_status('avif');
// Returns: ['installed', 'active', 'files_processed', 'space_saved', 'status']

// Get all spokes with status
$spokes = WPS_Spoke_Collection::get_all_spokes();

// Get collection-wide statistics
$stats = WPS_Spoke_Collection::get_collection_stats();

// Check for milestone unlocks
$milestones = WPS_Spoke_Collection::check_milestone_unlocks();

// Update metrics for a spoke
WPS_Spoke_Collection::update_metrics('avif', [
    'files_processed' => 150,
    'space_saved' => 52428800, // bytes
    'quality_retention' => 0.95
]);
```

### Frontend Components

#### View Template
`includes/views/spoke-collection.php` - Main gallery view

#### CSS Styles
`assets/css/spoke-collection.css` - Responsive layout, animations, status states

#### JavaScript Controller
`assets/js/spoke-collection.js` - AJAX interactions, animations, modal handling

### AJAX Handlers

Located in `includes/admin/ajax-spoke-collection.php`:

- `wps_ajax_install_spoke` - Install a spoke plugin
- `wps_ajax_activate_spoke` - Activate an installed spoke
- `wps_ajax_deactivate_spoke` - Deactivate an active spoke

### Data Storage

- **Milestones**: Stored in `wps_spoke_milestones` option
- **Metrics**: Stored in `wps_spoke_metrics` option
- **Activity Log**: Integrated with WPS_Activity_Logger

## User Experience

### Installing a Spoke

1. Navigate to Support Hub → Spoke Collection tab
2. Browse available spoke formats
3. Click "Install This Spoke" on a locked card
4. Watch installation animation
5. Click "Activate" when ready
6. View real-time metrics as images are processed

### Viewing Progress

The collection dashboard shows:
- Visual progress circle indicating completion percentage
- Stat cards with current counts and totals
- Individual spoke cards with detailed metrics

### Earning Achievements

Milestones automatically trigger when thresholds are met:
- A modal popup celebrates the achievement
- Activity log records the milestone unlock
- Rewards are applied (themes, badges, features)

## Accessibility Features

- High contrast mode support
- Reduced motion preferences respected
- Keyboard navigation for all interactive elements
- Focus states on buttons and cards
- Screen reader compatible status text
- Color-blind friendly status indicators (icons + colors)

## Mobile Responsive Design

- **Desktop** (1024px+): 3-4 column grid
- **Tablet** (768px-1023px): 2-3 column grid
- **Mobile** (< 768px): 1 column grid
- Touch-friendly button sizes
- Optimized animations for mobile devices

## Browser Compatibility

The Spoke Collection interface is compatible with:
- Modern browsers (Chrome, Firefox, Safari, Edge)
- WordPress 6.4+
- PHP 8.1.29+

## Developer Hooks

### Actions

```php
// Fired after a spoke is installed
do_action('wps_spoke_installed', $spoke_id, $timestamp);

// Fired after a spoke is activated
do_action('wps_spoke_activated', $spoke_id, $timestamp);

// Fired when a milestone is unlocked
do_action('wps_milestone_unlocked', $milestone_key, $milestone_data);

// Fired when an image is converted
do_action('wps_image_converted', $spoke_id, $attachment_id, $conversion_data);
```

### Filters

```php
// Modify spoke definitions
$spokes = apply_filters('wps_collection_spokes', $default_spokes);

// Modify milestone definitions
$milestones = apply_filters('wps_collection_milestones', $default_milestones);

// Modify collection statistics
$stats = apply_filters('wps_collection_stats', $stats);
```

## Integration with Other Systems

### Activity Logger
All spoke installations, activations, and milestone unlocks are logged to the WPS Activity Logger for audit trails.

### Module Registry
The collection system integrates with WPS_Module_Registry to detect installed and active spoke plugins.

### Achievement Badges
Future integration planned with WPS_Achievement_Badges for persistent badge display.

## Future Enhancements

- [ ] Spoke-specific configuration panels
- [ ] Advanced metrics (processing time, quality graphs)
- [ ] Export collection progress as PDF
- [ ] Social sharing for achievements
- [ ] Leaderboards (opt-in)
- [ ] Seasonal challenges and special events
- [ ] Integration with WordPress.org plugin repository

## Support

For questions or issues with the Spoke Collection system:
1. Check the Activity Log for installation/activation errors
2. Review browser console for JavaScript errors
3. Verify PHP version is 8.1.29 or higher
4. Contact support via Help tab

## Credits

Developed by Christopher Ross (thisismyurl)
Design inspired by Pokemon, Duolingo, Apple Health, and Steam achievement systems
