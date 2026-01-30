# AI Injection

Framework plugin for AI-based JavaScript injection subplugins.

## Description

This plugin provides a framework for developing AI-based subplugins that can automatically inject JavaScript into Moodle pages. It uses Moodle's Hook System to load JavaScript from enabled subplugins.

Subplugins inherit from `base_injection` and implement abstract methods to define when and what JavaScript to inject.

## Dependencies

- `local_ai_manager` - Required for AI functionality

## Installation

1. Copy the plugin to `local/ai_injection`
2. Run Moodle upgrade
3. Configure subplugins at Site Administration > Plugins > AI injection subplugins

## License

This plugin is licensed under the GNU GPL v3 or later.

## Copyright

© 2025 ISB Bayern
Dr. Peter Mayer
