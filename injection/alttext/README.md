# AI Alt Text Injection Subplugin

AI-based alt text generation for TinyMCE images in Moodle.

## Overview

The `aiinjection_alttext` subplugin automatically adds "Generate AI Alt Text" buttons to TinyMCE image dialogs and generates alt text using AI services via the `local_ai_manager` plugin.

## Features

- **🤖 AI Integration**: Uses local_ai_manager for alt text generation
- **🎯 TinyMCE Integration**: Automatic button injection into image dialogs
- **🎨 Template-based**: Clean UI components via Mustache templates
- **⚡ Core-compliant**: Uses Moodle Core LoadingIcon for spinners
- **🔧 Optimized**: Only 239 lines of JavaScript (65% code reduction)
- **📱 Responsive**: Works on all devices

## Technical Details

### JavaScript Architecture
```javascript
// Ultra-optimized 239-line implementation
// - Template-based state management
// - MutationObserver for modal detection
// - Core LoadingIcon integration
// - Event-hook approach without core changes
```

### Template System
```mustache
{{#isloading}}
  <!-- Loading state with Core spinner -->
{{/isloading}}

{{^isloading}}
  <!-- Normal state with AI icon -->
{{/isloading}}
```

### Hook Integration
The plugin is automatically loaded via the main plugin `local_ai_injection`:
- No separate hook system
- Integration via `base_injection` class
- Automatic JavaScript injection

## Installation

1. **Install main plugin**: `local_ai_injection` must be installed
2. **AI Manager**: `local_ai_manager` required for AI services
3. **Enable subplugin**:
   ```
   Site Administration > Plugins > AI injection subplugins > AI Alt Text
   ```

## Configuration

### Plugin Settings
- **Enable/Disable**: Toggle switch for the plugin
- **Debug Mode**: Automatically based on Moodle debug settings

### AI Service
- Configuration via `local_ai_manager` plugin
- Supports various AI providers
- Automatic error handling

## Usage

1. **Open TinyMCE Editor**: In any text field
2. **Insert Image**: Via image button or drag & drop
3. **Use AI Button**: Click "Generate AI Alt Text" button
4. **Accept Result**: Alt text is automatically inserted

## Development

### Code Quality
- ✅ **ESLint Clean**: No warnings or errors
- ✅ **Moodle Standards**: PHP and JavaScript compliant
- ✅ **Template-based**: No HTML in JavaScript
- ✅ **Core Integration**: Uses Moodle Core methods

### Build Process
```bash
# Compile JavaScript
grunt amd --modules=local_ai_injection/injection/alttext/alttext_injection

# Clear caches
./bindev/purge_caches.sh
```

### Debugging
```php
// Enable debug mode
$CFG->debug = DEBUG_DEVELOPER;
$CFG->debugdisplay = 1;
```

JavaScript logs appear in browser console:
```javascript
Log.debug('AI button injected via template', templateContext);
Log.debug('Alt text inserted:', altText);
```

## Architecture

### File Structure
```
aiinjection_alttext/
├── classes/local/injection.php    # Main class (78 lines)
├── amd/src/alttext_injection.js   # JavaScript (239 lines)
├── templates/ai_button_container.mustache # UI template
├── lang/en/aiinjection_alttext.php # Language strings
├── settings.php                   # Admin settings
├── styles.css                     # Minimal CSS
└── version.php                    # Plugin info
```

### Code Optimizations
- **JavaScript**: 688 → 239 lines (65% reduction)
- **PHP**: Clean OOP structure with base_injection
- **Templates**: UI logic extracted from JavaScript
- **Performance**: Minimal overhead through event hooks

## Requirements

- **Moodle**: 4.5+
- **PHP**: 8.1+
- **Dependencies**:
  - `local_ai_injection` (Framework)
  - `local_ai_manager` (AI Services)
- **Browser**: ES6+ Support

## Support

- **Developer**: Dr. Peter Mayer, ISB Bayern
- **Ticket System**: Internal issues
- **Documentation**: This README + inline comments

## License

GPL v3 or later

## Copyright

© 2025 ISB Bayern
Dr. Peter Mayer

---

*This plugin is part of the AI Injection Framework and demonstrates best practices for Moodle subplugin development.*