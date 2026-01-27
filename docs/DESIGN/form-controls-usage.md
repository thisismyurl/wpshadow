# Modern Form Controls - Usage Guide

## Overview
This guide demonstrates how to use WPShadow's modern form controls throughout the plugin. All controls are fully accessible (WCAG AA), keyboard navigable, and screen reader compatible.

## Quick Start

### Loading the Components
The form controls are automatically loaded on all WPShadow admin pages via `includes/core/class-hooks-initializer.php`. No manual enqueuing needed.

### PHP Helper Class
```php
use WPShadow\Helpers\Form_Controls;
```

---

## 1. Toggle Switch (Replaces Checkbox)

### When to Use
- Binary on/off settings
- Feature enable/disable options
- Permission toggles

### When NOT to Use
- Multi-select lists (use traditional checkboxes)
- Agreeing to terms/conditions (checkbox feels more deliberate)

### Basic Usage
```php
echo Form_Controls::toggle_switch(
    array(
        'id'      => 'enable-notifications',
        'name'    => 'notifications_enabled',
        'label'   => __( 'Enable Notifications', 'wpshadow' ),
        'checked' => true,
    )
);
```

### With Helper Text
```php
echo Form_Controls::toggle_switch(
    array(
        'id'          => 'guardian-enabled',
        'name'        => 'guardian_enabled',
        'label'       => __( 'Enable WPShadow Guardian', 'wpshadow' ),
        'helper_text' => __( 'Automated health monitoring and intelligent fix suggestions', 'wpshadow' ),
        'checked'     => Guardian_Manager::is_enabled(),
    )
);
```

### Disabled State
```php
echo Form_Controls::toggle_switch(
    array(
        'id'       => 'pro-feature',
        'name'     => 'pro_feature',
        'label'    => __( 'Advanced Feature (Pro)', 'wpshadow' ),
        'checked'  => false,
        'disabled' => true,
    )
);
```

### JavaScript API
```javascript
// Get toggle state
var isEnabled = WPShadowFormControls.getToggleState('guardian-enabled');

// Set toggle state
WPShadowFormControls.setToggleState('guardian-enabled', true);

// Listen for changes
$('.wps-toggle').on('wps:toggle:change', function(e, newState) {
    console.log('Toggle changed:', newState);
});
```

---

## 2. Slider (Replaces Number Input)

### When to Use
- Values with a logical min/max range
- Visual representation helps users understand scale
- Incremental adjustments are common

### When NOT to Use
- Precise values needed (port numbers, exact pixels)
- No logical min/max
- User wants to type exact values (provide both slider + input)

### Basic Usage
```php
echo Form_Controls::slider(
    array(
        'id'    => 'cache-duration',
        'name'  => 'cache_duration',
        'label' => __( 'Cache Duration', 'wpshadow' ),
        'min'   => 0,
        'max'   => 24,
        'step'  => 1,
        'value' => 6,
        'unit'  => __( 'hours', 'wpshadow' ),
    )
);
```

### With Tick Marks
```php
echo Form_Controls::slider(
    array(
        'id'          => 'memory-threshold',
        'name'        => 'memory_threshold',
        'label'       => __( 'Memory Usage Threshold', 'wpshadow' ),
        'helper_text' => __( 'Pause auto-fixes if memory usage exceeds this percentage', 'wpshadow' ),
        'min'         => 50,
        'max'         => 95,
        'step'        => 5,
        'value'       => 85,
        'unit'        => '%',
        'ticks'       => array( 50, 65, 80, 95 ),
    )
);
```

### Large Range Example
```php
echo Form_Controls::slider(
    array(
        'id'    => 'max-file-size',
        'name'  => 'max_file_size',
        'label' => __( 'Maximum File Size', 'wpshadow' ),
        'min'   => 1,
        'max'   => 100,
        'step'  => 1,
        'value' => 10,
        'unit'  => 'MB',
        'ticks' => array( 1, 25, 50, 75, 100 ),
    )
);
```

### JavaScript API
```javascript
// Get slider value
var value = WPShadowFormControls.getSliderValue('memory-threshold');

// Set slider value
WPShadowFormControls.setSliderValue('memory-threshold', 85);

// Listen for changes
$('.wps-slider').on('input', function() {
    var value = $(this).val();
    console.log('Slider value:', value);
});
```

### Keyboard Navigation
- **Left/Right Arrow**: Adjust by step
- **Page Up/Down**: Adjust by 10× step
- **Home/End**: Jump to min/max

---

## 3. Styled Dropdown (Replaces Select)

### When to Use
- Desktop/tablet users
- Less than 100 options
- Want consistent styling across browsers

### When NOT to Use
- Mobile devices (use native `<select>`)
- Very long lists (100+ items)
- Multi-select needed (use `<select multiple>`)

### Basic Usage
```php
echo Form_Controls::dropdown(
    array(
        'id'       => 'execution-frequency',
        'name'     => 'execution_frequency',
        'label'    => __( 'Execution Frequency', 'wpshadow' ),
        'options'  => array(
            'manual'      => __( 'Manual Only', 'wpshadow' ),
            'cron_hourly' => __( 'Hourly', 'wpshadow' ),
            'cron_daily'  => __( 'Daily', 'wpshadow' ),
        ),
        'selected' => 'cron_daily',
    )
);
```

### With Placeholder
```php
echo Form_Controls::dropdown(
    array(
        'id'          => 'country',
        'name'        => 'country',
        'label'       => __( 'Country', 'wpshadow' ),
        'placeholder' => __( 'Select your country...', 'wpshadow' ),
        'options'     => array(
            'us' => __( 'United States', 'wpshadow' ),
            'ca' => __( 'Canada', 'wpshadow' ),
            'uk' => __( 'United Kingdom', 'wpshadow' ),
            'au' => __( 'Australia', 'wpshadow' ),
        ),
        'selected'    => '',
    )
);
```

### Disabled State
```php
echo Form_Controls::dropdown(
    array(
        'id'       => 'timezone',
        'name'     => 'timezone',
        'label'    => __( 'Timezone', 'wpshadow' ),
        'options'  => $timezone_options,
        'selected' => 'America/New_York',
        'disabled' => true,
    )
);
```

### JavaScript API
```javascript
// Get dropdown value
var value = WPShadowFormControls.getDropdownValue('execution-frequency');

// Set dropdown value
WPShadowFormControls.setDropdownValue('execution-frequency', 'cron_hourly');

// Listen for changes
$('.wps-dropdown').on('wps:dropdown:change', function(e, value, text) {
    console.log('Selected:', value, text);
});
```

### Keyboard Navigation
- **Arrow Up/Down**: Navigate options
- **Enter/Space**: Open dropdown or select option
- **Escape**: Close dropdown
- **Type ahead**: Type to find option

---

## 4. Button Group (Replaces Radio Buttons)

### When to Use
- 2-5 mutually exclusive options
- Options are simple (1-2 words)
- Visual grouping makes sense

### When NOT to Use
- More than 5 options (use dropdown or radio buttons)
- Long option labels (use radio buttons)
- Options need detailed descriptions

### Basic Usage
```php
echo Form_Controls::button_group(
    array(
        'name'     => 'scan_mode',
        'label'    => __( 'Scan Mode', 'wpshadow' ),
        'options'  => array(
            array(
                'value' => 'auto',
                'label' => __( 'Auto', 'wpshadow' ),
            ),
            array(
                'value' => 'manual',
                'label' => __( 'Manual', 'wpshadow' ),
            ),
        ),
        'selected' => 'auto',
    )
);
```

### With Icons
```php
echo Form_Controls::button_group(
    array(
        'name'     => 'dark_mode_pref',
        'label'    => __( 'Mode Preference', 'wpshadow' ),
        'options'  => array(
            array(
                'value' => 'auto',
                'label' => __( 'Auto', 'wpshadow' ),
                'icon'  => 'dashicons-update',
            ),
            array(
                'value' => 'light',
                'label' => __( 'Light', 'wpshadow' ),
                'icon'  => 'dashicons-admin-appearance',
            ),
            array(
                'value' => 'dark',
                'label' => __( 'Dark', 'wpshadow' ),
                'icon'  => 'dashicons-admin-customizer',
            ),
        ),
        'selected' => 'auto',
    )
);
```

### Disabled State
```php
echo Form_Controls::button_group(
    array(
        'name'     => 'report_format',
        'label'    => __( 'Report Format', 'wpshadow' ),
        'options'  => array(
            array( 'value' => 'pdf', 'label' => 'PDF' ),
            array( 'value' => 'html', 'label' => 'HTML' ),
            array( 'value' => 'json', 'label' => 'JSON' ),
        ),
        'selected' => 'pdf',
        'disabled' => true,
    )
);
```

### JavaScript API
```javascript
// Listen for changes
$('.wps-button-group').on('wps:buttongroup:change', function(e, value, label) {
    console.log('Selected:', value, label);
});
```

### Keyboard Navigation
- **Arrow Left/Right**: Navigate options
- **Arrow Up/Down**: Navigate options
- **Space/Enter**: Select option

---

## 5. Modern Textarea

### Basic Usage
```php
echo Form_Controls::textarea(
    array(
        'id'    => 'description',
        'name'  => 'description',
        'label' => __( 'Description', 'wpshadow' ),
        'value' => '',
        'rows'  => 4,
    )
);
```

### With Character Counter
```php
echo Form_Controls::textarea(
    array(
        'id'          => 'description',
        'name'        => 'description',
        'label'       => __( 'Description', 'wpshadow' ),
        'helper_text' => __( 'Optional: Add details about this configuration', 'wpshadow' ),
        'value'       => '',
        'rows'        => 4,
        'maxlength'   => 500,
        'placeholder' => __( 'Enter description...', 'wpshadow' ),
    )
);
```

---

## Accessibility Best Practices

### 1. Always Provide Labels
```php
// ✅ Good
echo Form_Controls::toggle_switch(
    array(
        'id'    => 'enable-feature',
        'name'  => 'enable_feature',
        'label' => __( 'Enable Feature', 'wpshadow' ),
    )
);

// ❌ Bad - Missing label
echo Form_Controls::toggle_switch(
    array(
        'id'   => 'enable-feature',
        'name' => 'enable_feature',
    )
);
```

### 2. Use Helper Text for Context
```php
echo Form_Controls::slider(
    array(
        'id'          => 'cache-duration',
        'name'        => 'cache_duration',
        'label'       => __( 'Cache Duration', 'wpshadow' ),
        'helper_text' => __( 'How long to cache diagnostic results', 'wpshadow' ),
        'min'         => 0,
        'max'         => 24,
        'value'       => 6,
        'unit'        => __( 'hours', 'wpshadow' ),
    )
);
```

### 3. Meaningful IDs
```php
// ✅ Good - Descriptive IDs
'id' => 'guardian-enabled'
'id' => 'memory-threshold'
'id' => 'execution-frequency'

// ❌ Bad - Generic IDs
'id' => 'toggle1'
'id' => 'slider2'
'id' => 'dropdown3'
```

### 4. Test with Keyboard Only
- Can you navigate to all controls?
- Can you operate all controls without a mouse?
- Are focus indicators visible?
- Can you escape from dropdowns?

### 5. Test with Screen Reader
- Do labels announce correctly?
- Do helper texts provide context?
- Do state changes announce (on/off, selected value)?
- Are error states announced?

---

## Migration Checklist

When converting an existing form:

- [ ] Replace `<input type="checkbox">` with `Form_Controls::toggle_switch()`
- [ ] Replace `<input type="number">` with `Form_Controls::slider()` (if appropriate)
- [ ] Replace `<select>` with `Form_Controls::dropdown()` (desktop only)
- [ ] Replace `<input type="radio">` with `Form_Controls::button_group()` (if appropriate)
- [ ] Replace `<textarea>` with `Form_Controls::textarea()` (for consistency)
- [ ] Add `// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` before each echo
- [ ] Test keyboard navigation
- [ ] Test with screen reader
- [ ] Verify form still submits correctly
- [ ] Check mobile responsiveness

---

## Examples from Real Pages

### Guardian Settings - General Tab
```php
echo \WPShadow\Helpers\Form_Controls::toggle_switch(
    array(
        'id'          => 'guardian-enabled',
        'name'        => 'guardian_enabled',
        'label'       => __( 'Enable WPShadow Guardian', 'wpshadow' ),
        'helper_text' => __( 'Enables automated health monitoring', 'wpshadow' ),
        'checked'     => Guardian_Manager::is_enabled(),
    )
);
```

### Guardian Settings - Schedule Tab
```php
echo \WPShadow\Helpers\Form_Controls::dropdown(
    array(
        'id'       => 'execution-frequency',
        'name'     => 'execution_frequency',
        'label'    => __( 'Auto-Fix Execution Frequency', 'wpshadow' ),
        'options'  => array(
            'manual'      => __( 'Manual Only', 'wpshadow' ),
            'cron_hourly' => __( 'Hourly', 'wpshadow' ),
            'cron_daily'  => __( 'Daily', 'wpshadow' ),
        ),
        'selected' => 'cron_daily',
    )
);

echo \WPShadow\Helpers\Form_Controls::slider(
    array(
        'id'          => 'max-treatments',
        'name'        => 'max_treatments',
        'label'       => __( 'Max Treatments Per Run', 'wpshadow' ),
        'helper_text' => __( 'Maximum number of treatments to apply', 'wpshadow' ),
        'min'         => 1,
        'max'         => 20,
        'step'        => 1,
        'value'       => 5,
        'ticks'       => array( 1, 5, 10, 15, 20 ),
    )
);
```

### Dark Mode Tool
```php
echo \WPShadow\Helpers\Form_Controls::button_group(
    array(
        'name'     => 'dark_mode_pref',
        'label'    => __( 'Mode Preference', 'wpshadow' ),
        'options'  => array(
            array(
                'value' => 'auto',
                'label' => __( 'Auto', 'wpshadow' ),
                'icon'  => 'dashicons-update',
            ),
            array(
                'value' => 'light',
                'label' => __( 'Light', 'wpshadow' ),
                'icon'  => 'dashicons-admin-appearance',
            ),
            array(
                'value' => 'dark',
                'label' => __( 'Dark', 'wpshadow' ),
                'icon'  => 'dashicons-admin-customizer',
            ),
        ),
        'selected' => $dark_mode_pref,
    )
);
```

---

## Troubleshooting

### Toggle not updating hidden input
Make sure the `data-setting` attribute matches the `name`:
```php
'id'   => 'guardian-enabled',
'name' => 'guardian_enabled',  // This must match
```

### Dropdown not showing selected value
Ensure the selected value exists in options:
```php
'options'  => array(
    'option1' => 'Label 1',
    'option2' => 'Label 2',
),
'selected' => 'option1',  // Must be a key from options
```

### Slider value not displaying
Check that the display element ID is correct:
```php
'id' => 'memory-threshold',
// JavaScript looks for #memory-threshold-display
```

### Form not submitting correctly
Hidden inputs must have correct `name` attributes that match your form processing:
```php
'name' => 'guardian_enabled',  // Must match $_POST key
```

---

## Support

For bugs or feature requests, open an issue on GitHub:
https://github.com/thisismyurl/wpshadow/issues
