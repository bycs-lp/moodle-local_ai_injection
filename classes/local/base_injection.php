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

namespace local_ai_injection\local;

/**
 * Base class for AI injection subplugins.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_injection {
    /**
     * Get the subplugin name.
     *
     * @return string
     */
    abstract protected function get_subplugin_name(): string;

    /**
     * Get the AMD module name for this subplugin.
     *
     * @return string
     */
    abstract protected function get_amd_module(): string;

    /**
     * Get the configuration parameters for the JavaScript module.
     *
     * @return array
     */
    abstract protected function get_js_config(): array;

    /**
     * Check if this injection should be active on the current page.
     *
     * @return bool
     */
    abstract protected function should_inject(): bool;

    /**
     * Inject JavaScript into the page.
     * This is the main method subplugins should call.
     *
     * @return void
     */
    public function inject_javascript(): void {
        global $PAGE;

        // Check if subplugin is enabled.
        if (!$this->is_enabled()) {
            return;
        }

        // Check if we should inject on this page.
        if (!$this->should_inject()) {
            return;
        }

        // Load the AMD module with configuration.
        $PAGE->requires->js_call_amd(
            $this->get_amd_module(),
            'init',
            $this->get_js_config()
        );
    }

    /**
     * Check if this subplugin is enabled.
     *
     * @return bool
     */
    protected function is_enabled(): bool {
        return (bool) get_config($this->get_subplugin_name(), 'enabled');
    }

    /**
     * Get a configuration value for this subplugin.
     *
     * @param string $name Configuration name
     * @param mixed $default Default value
     * @return mixed
     */
    protected function get_config(string $name, $default = null) {
        return get_config($this->get_subplugin_name(), $name) ?: $default;
    }
}
