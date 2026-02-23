# AI Injection

Framework plugin for AI-based JavaScript injection subplugins.

## Description

This plugin provides a framework for developing AI-based subplugins that can automatically inject JavaScript into Moodle pages. It uses Moodle's Hook System to load JavaScript from enabled subplugins.

Subplugins inherit from `base_injection` and implement abstract methods to define when and what JavaScript to inject.

## Using the AI Usage Confirmation Modal

`local_ai_injection` provides a general-purpose confirmation and info modal for
AI features. Subplugins can use it directly without reimplementing the UX.

**Import:**
```javascript
import {confirmAiUsage, isAiUsageConfirmed, showAiInfo} from 'local_ai_injection/confirm_ai_usage';
```

**`confirmAiUsage(component, purposes)`**
Shows a confirmation modal on first use. Returns `true` if the user confirmed,
`false` if cancelled. Subsequent calls return `true` directly (localStorage-based).

**`isAiUsageConfirmed()`**
Async check whether the user has already confirmed. Use on page load to
determine initial UI state (e.g. whether to show the info icon).

**`showAiInfo(component, purposes)`**
Shows an info-only modal (no confirmation required).

See `injection/alttext` for a complete reference implementation.

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
