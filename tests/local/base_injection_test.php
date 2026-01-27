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

use advanced_testcase;

/**
 * Unit tests for base injection class.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_ai_injection\local\base_injection
 * @group      local_ai_injection
 */
final class base_injection_test extends advanced_testcase {
    /**
     * Test concrete implementation for testing abstract methods.
     *
     * @return base_injection
     */
    private function get_test_injection(): base_injection {
        return new class extends base_injection {
            /** @var bool Track if JavaScript was called */
            public bool $jscalled = false;

            /**
             * Get the subplugin name.
             * @return string
             */
            public function get_subplugin_name(): string {
                return 'aiinjection_test';
            }

            /**
             * Get the AMD module name.
             * @return string
             */
            public function get_amd_module(): string {
                return 'aiinjection_test/test_module';
            }

            /**
             * Get the configuration parameters.
             * @return array
             */
            public function get_js_config(): array {
                return ['setting' => 'test_value'];
            }

            /**
             * Check if should inject.
             * @return bool
             */
            public function should_inject(): bool {
                return true;
            }

            /**
             * Override inject_javascript for testing.
             * @return void
             */
            public function inject_javascript(): void {
                if ($this->is_enabled() && $this->should_inject()) {
                    $this->jscalled = true;
                }
            }
        };
    }

    /**
     * Test base injection configuration and enabled state.
     */
    public function test_configuration_and_enabled_state(): void {
        $this->resetAfterTest(true);

        $injection = $this->get_test_injection();

        // Not configured = disabled.
        $this->assertFalse($injection->is_enabled());

        // Explicitly disabled.
        set_config('enabled', 0, 'aiinjection_test');
        $this->assertFalse($injection->is_enabled());

        // Enabled.
        set_config('enabled', 1, 'aiinjection_test');
        $this->assertTrue($injection->is_enabled());

        // Config retrieval with default.
        $this->assertNull($injection->get_config('nonexistent'));
        $this->assertEquals('default', $injection->get_config('nonexistent', 'default'));

        // Config retrieval with set value.
        set_config('test_setting', 'test_value', 'aiinjection_test');
        $this->assertEquals('test_value', $injection->get_config('test_setting'));
    }

    /**
     * Test inject_javascript respects enabled state.
     */
    public function test_inject_javascript_respects_enabled_state(): void {
        $this->resetAfterTest(true);

        // Test disabled - should not inject.
        $injection = $this->get_test_injection();
        set_config('enabled', 0, 'aiinjection_test');
        $injection->inject_javascript();
        $this->assertFalse($injection->jscalled);

        // Test enabled - should inject.
        $injection = $this->get_test_injection();
        set_config('enabled', 1, 'aiinjection_test');
        $injection->inject_javascript();
        $this->assertTrue($injection->jscalled);
    }
}
