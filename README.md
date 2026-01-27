# AI Injection Plugin for Moodle

A framework plugin for Moodle that allows development of AI-based subplugins which can automatically inject JavaScript into web pages.

## Overview

The `local_ai_injection` plugin serves as a framework for AI-based subplugins. It uses Moodle's Hook System (`before_footer_html_generation`) to automatically load JavaScript code from subplugins.

## Architecture

### Main Plugin: `local_ai_injection`
- **Hook Management**: Registers for `before_footer_html_generation` hook
- **Subplugin Discovery**: Automatically finds and loads all enabled subplugins
- **Base Class**: Provides `base_injection` class for subplugins

### Subplugins: `aiinjection_*`
- **Namespace**: `aiinjection_*` (e.g. `aiinjection_alttext`)
- **Simple API**: Inherit from `base_injection` and implement abstract methods
- **JavaScript Injection**: Use `inject_javascript()` method from parent class

## Installation

1. Install the main plugin:
```bash
# In Moodle root directory
cp -r local/ai_injection $MOODLE_DIR/local/
```

2. Run Moodle upgrade:
```bash
php admin/cli/upgrade.php
```

3. Configure the plugin:
   - Site Administration > Plugins > Local plugins > AI Injection
   - Configure subplugins separately

## First Subplugin: AI Alt Text

The plugin includes the first subplugin `aiinjection_alttext` which provides AI-based alt text generation for TinyMCE images.

### Features:
- Automatic button injection into TinyMCE image dialogs
- AI-based alt text generation via local_ai_manager
- Template-based UI components
- Core LoadingIcon integration
- Debug mode for development

### Configuration:
- **Enable/Disable**: Site Administration > Plugins > AI injection subplugins > AI Alt Text
- **Integration**: Works with local_ai_manager plugin

## Developing New Subplugins

### 1. Create subplugin structure:
```
local/ai_injection/injection/my_plugin/
├── version.php
├── classes/local/injection.php
├── amd/src/my_module.js
├── lang/en/aiinjection_my_plugin.php
├── settings.php
└── templates/ (optional)
```

### 2. Implement injection class:
```php
<?php
namespace aiinjection_my_plugin\local;

use local_ai_injection\local\base_injection;

class injection extends base_injection {

    protected function get_subplugin_name(): string {
        return 'aiinjection_my_plugin';
    }

    protected function get_js_module_name(): string {
        return 'aiinjection_my_plugin/my_module';
    }

    protected function get_js_config(): array {
        return [
            'debug' => debugging(),
            'setting' => get_config('aiinjection_my_plugin', 'setting'),
        ];
    }

    protected function should_inject(): bool {
        global $PAGE;

        // Example: Only load on course pages and activities
        $allowedpagetypes = ['course-view', 'mod-'];
        foreach ($allowedpagetypes as $pagetype) {
            if (strpos($PAGE->pagetype, $pagetype) === 0) {
                return true;
            }
        }
        return false;
    }
}
```

### 3. Create JavaScript AMD module:
```javascript
// amd/src/my_module.js
import Log from 'core/log';

export const init = (config) => {
    if (config.debug) {
        Log.debug('My AI Plugin initialized:', config);
    }

    // Your AI-based functionality here
};
```

### 4. Version and dependencies:
```php
<?php
// version.php
$plugin->component = 'aiinjection_my_plugin';
$plugin->version = 2025091100;
$plugin->requires = 2024042200; // Moodle 4.5+
$plugin->dependencies = [
    'local_ai_injection' => 2025091100,
];
```

## Available methods in base_injection

### Abstract methods (must be implemented):
- `get_subplugin_name()`: Returns the subplugin name
- `get_js_module_name()`: Returns the JS module name
- `get_js_config()`: Returns configuration for JavaScript
- `should_inject()`: Checks if plugin should load on current page

### Available helper methods:
- `inject_javascript()`: Loads the JavaScript module (called automatically)
- `is_enabled()`: Checks if the subplugin is enabled
- `get_config($name, $default)`: Gets configuration values for the subplugin

## Building JavaScript

After making changes to JavaScript files:
```bash
cd $MOODLE_DIR
grunt amd --modules=local_ai_injection/injection/my_plugin/my_module
```

## Hook System

The plugin uses Moodle's modern Hook System:
- **Hook**: `\core\hook\output\before_footer_html_generation`
- **Priority**: 0 (can be adjusted)
- **Callback**: `local_ai_injection\local\hook_callbacks::before_footer_html_generation`

## Debugging

Enable debug mode in subplugin settings or globally:
```php
// In config.php
$CFG->debug = DEBUG_DEVELOPER;
$CFG->debugdisplay = 1;
```

JavaScript debug logs appear in browser console.

## Requirements

- **Moodle**: 4.5+
- **PHP**: 8.1+
- **JavaScript**: ES6+ Support
- **Dependencies**: local_ai_manager (for AI functions)

## Support

For questions or issues:
- **Developer**: Dr. Peter Mayer, ISB Bayern

## License

GPL v3 or later

## Copyright

© 2025 ISB Bayern
Dr. Peter Mayer
