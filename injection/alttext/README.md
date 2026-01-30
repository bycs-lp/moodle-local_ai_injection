# AI Alt Text Injection Subplugin

This subplugin for `local_ai_injection` adds AI-powered alt text generation to TinyMCE image dialogs in Moodle.

## Description

When users insert or edit images in the TinyMCE editor, this plugin adds a "Generate AI Alt Text" button to the image dialog. Clicking it generates an appropriate alt text description using AI services provided by `local_ai_manager`.

## Dependencies

- `local_ai_injection` - The main AI injection framework
- `local_ai_manager` - Provides AI service integration

## Installation

1. Install the main plugin `local_ai_injection` including this subplugin.
2. Ensure `local_ai_manager` is installed and configured with an AI provider.
3. Enable the subplugin via Site Administration → Plugins → AI Injection → Manage Subplugins.

## Configuration

The plugin can be enabled or disabled via the subplugin management page. AI service configuration is handled by `local_ai_manager`.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

## Copyright

© 2025 ISB Bayern
Dr. Peter Mayer