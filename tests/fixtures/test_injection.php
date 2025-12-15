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

namespace aiinjection_testplugin\local;

use local_ai_injection\local\base_injection;

/**
 * Test subplugin for unit testing.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injection extends base_injection {
    /** @var bool Track if JavaScript was injected */
    public $jsinjected = false;

    /**
     * Get the subplugin name.
     *
     * @return string
     */
    protected function get_subplugin_name(): string {
        return 'aiinjection_testplugin';
    }

    /**
     * Get the AMD module name for this subplugin.
     *
     * @return string
     */
    protected function get_amd_module(): string {
        return 'aiinjection_testplugin/test_module';
    }

    /**
     * Get the configuration parameters for the JavaScript module.
     *
     * @return array
     */
    protected function get_js_config(): array {
        return [];
    }

    /**
     * Check if this injection should be active on the current page.
     *
     * @return bool
     */
    protected function should_inject(): bool {
        return true;
    }

    /**
     * Override to track injection for testing.
     *
     * @return void
     */
    public function inject_javascript(): void {
        $this->jsinjected = true;
    }
}
