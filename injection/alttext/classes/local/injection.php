<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiinjection_alttext\local;

use local_ai_injection\local\base_injection;

/**
 * AI Alt Text injection class.
 *
 * @package    aiinjection_alttext
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injection extends base_injection {
    /**
     * Get the subplugin name.
     *
     * @return string
     */
    protected function get_subplugin_name(): string {
        return 'aiinjection_alttext';
    }

    /**
     * Get the AMD module name for this subplugin.
     *
     * @return string
     */
    protected function get_amd_module(): string {
        return 'aiinjection_alttext/alttext_injection';
    }

    /**
     * Get the configuration parameters for the JavaScript module.
     *
     * @return array
     */
    protected function get_js_config(): array {
        return [
            'debug' => debugging(),
        ];
    }

    /**
     * Check if this injection should be active on the current page.
     *
     * @return bool
     */
    protected function should_inject(): bool {
        global $PAGE;
        // Require capability to use the Alt Text feature on the current page context.
        if (!has_capability('local/ai_injection:alttext_use', $PAGE->context)) {
            return false;
        }

        // Debug: Allow loading in developer mode to ease testing (capability still required).
        if (debugging()) {
            return true;
        }

        // Check if the tenant allows this functionality.
        $tenant = \core\di::get(\local_ai_manager\local\tenant::class);
        if (!$tenant->is_tenant_allowed()) {
            return false;
        }

        return true;
    }
}
