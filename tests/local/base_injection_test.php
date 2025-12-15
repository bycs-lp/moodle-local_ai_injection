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
use local_ai_injection\local\base_injection;

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
     */
    private function get_test_injection() {
        return new class extends base_injection {
            /**
             * Get the subplugin name.
             * @return string
             */
            protected function get_subplugin_name(): string {
                return 'aiinjection_test';
            }

            /**
             * Get the AMD module name.
             * @return string
             */
            protected function get_amd_module(): string {
                return 'aiinjection_test/test_module';
            }

            /**
             * Get the configuration parameters.
             * @return array
             */
            protected function get_js_config(): array {
                return [
                    'debug' => true,
                    'setting' => 'test_value',
                ];
            }

            /**
             * Check if should inject.
             * @return bool
             */
            protected function should_inject(): bool {
                return true;
            }

            /**
             * Test method to check if enabled.
             * @return bool
             */
            public function test_is_enabled(): bool {
                return $this->is_enabled();
            }

            /**
             * Test method to get config.
             * @param string $name Configuration name
             * @param mixed $default Default value
             * @return mixed
             */
            public function test_get_config(string $name, $default = null): mixed {
                return $this->get_config($name, $default);
            }
        };
    }

    /**
     * Test is_enabled method when subplugin is enabled.
     */
    public function test_is_enabled_when_enabled(): void {
        $this->resetAfterTest(true);

        // Enable the test subplugin.
        set_config('enabled', 1, 'aiinjection_test');

        $injection = $this->get_test_injection();
        $this->assertTrue($injection->test_is_enabled());
    }

    /**
     * Test is_enabled method when subplugin is disabled.
     */
    public function test_is_enabled_when_disabled(): void {
        $this->resetAfterTest(true);

        // Disable the test subplugin.
        set_config('enabled', 0, 'aiinjection_test');

        $injection = $this->get_test_injection();
        $this->assertFalse($injection->test_is_enabled());
    }

    /**
     * Test is_enabled method when no configuration exists.
     */
    public function test_is_enabled_when_not_configured(): void {
        $this->resetAfterTest(true);

        $injection = $this->get_test_injection();
        $this->assertFalse($injection->test_is_enabled());
    }

    /**
     * Test get_config method with existing configuration.
     */
    public function test_get_config_existing(): void {
        $this->resetAfterTest(true);

        set_config('test_setting', 'test_value', 'aiinjection_test');

        $injection = $this->get_test_injection();
        $this->assertEquals('test_value', $injection->test_get_config('test_setting'));
    }

    /**
     * Test get_config method with default value.
     */
    public function test_get_config_default(): void {
        $this->resetAfterTest(true);

        $injection = $this->get_test_injection();
        $this->assertEquals('default', $injection->test_get_config('nonexistent_setting', 'default'));
    }

    /**
     * Test get_config method with null default.
     */
    public function test_get_config_null_default(): void {
        $this->resetAfterTest(true);

        $injection = $this->get_test_injection();
        $this->assertNull($injection->test_get_config('nonexistent_setting'));
    }

    /**
     * Test inject_javascript method when enabled and should inject.
     */
    public function test_inject_javascript_when_enabled(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Enable the subplugin.
        set_config('enabled', 1, 'aiinjection_test');

        // Create injection with mock for testing JavaScript injection.
        $injection = new class extends base_injection {
            /** @var bool Track if JavaScript was called */
            public $jscalled = false;
            /** @var string Track the JavaScript module name */
            public $jsmodule = '';
            /** @var string Track the JavaScript method name */
            public $jsmethod = '';
            /** @var array Track the JavaScript configuration */
            public $jsconfig = [];

            /**
             * Get the subplugin name.
             * @return string
             */
            protected function get_subplugin_name(): string {
                return 'aiinjection_test';
            }

            /**
             * Get the AMD module name.
             * @return string
             */
            protected function get_amd_module(): string {
                return 'aiinjection_test/test_module';
            }

            /**
             * Get the configuration parameters.
             * @return array
             */
            protected function get_js_config(): array {
                return [
                    'debug' => true,
                    'setting' => 'test_value',
                ];
            }

            /**
             * Check if should inject.
             * @return bool
             */
            protected function should_inject(): bool {
                return true;
            }

            /**
             * Override inject_javascript for testing.
             * @return void
             */
            public function inject_javascript(): void {
                if (!$this->is_enabled()) {
                    return;
                }

                if (!$this->should_inject()) {
                    return;
                }

                // Simulate JavaScript injection.
                $this->jscalled = true;
                $this->jsmodule = $this->get_amd_module();
                $this->jsmethod = 'init';
                $this->jsconfig = $this->get_js_config();
            }
        };

        $injection->inject_javascript();

        $this->assertTrue($injection->jscalled);
        $this->assertEquals('aiinjection_test/test_module', $injection->jsmodule);
        $this->assertEquals('init', $injection->jsmethod);
        $this->assertEquals([
            'debug' => true,
            'setting' => 'test_value',
        ], $injection->jsconfig);
    }

    /**
     * Test inject_javascript method when disabled.
     */
    public function test_inject_javascript_when_disabled(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Disable the subplugin.
        set_config('enabled', 0, 'aiinjection_test');

        // Create injection with mock for testing JavaScript injection.
        $injection = new class extends base_injection {
            /** @var bool Track if JavaScript was called */
            public $jscalled = false;

            /**
             * Get the subplugin name.
             * @return string
             */
            protected function get_subplugin_name(): string {
                return 'aiinjection_test';
            }

            /**
             * Get the AMD module name.
             * @return string
             */
            protected function get_amd_module(): string {
                return 'aiinjection_test/test_module';
            }

            /**
             * Get the configuration parameters.
             * @return array
             */
            protected function get_js_config(): array {
                return [];
            }

            /**
             * Check if should inject.
             * @return bool
             */
            protected function should_inject(): bool {
                return true;
            }

            /**
             * Override inject_javascript to capture calls.
             * @return void
             */
            public function inject_javascript(): void {
                if (!$this->is_enabled()) {
                    return;
                }

                if (!$this->should_inject()) {
                    return;
                }

                $this->jscalled = true;
            }
        };

        $injection->inject_javascript();

        $this->assertFalse($injection->jscalled);
    }

    /**
     * Test inject_javascript method when should not inject.
     */
    public function test_inject_javascript_should_not_inject(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Enable the subplugin.
        set_config('enabled', 1, 'aiinjection_test');

        // Create injection that should not inject.
        $injection = new class extends base_injection {
            /** @var bool Track if JavaScript was called */
            public $jscalled = false;

            /**
             * Get the subplugin name.
             * @return string
             */
            protected function get_subplugin_name(): string {
                return 'aiinjection_test';
            }

            /**
             * Get the AMD module name.
             * @return string
             */
            protected function get_amd_module(): string {
                return 'aiinjection_test/test_module';
            }

            /**
             * Get the configuration parameters.
             * @return array
             */
            protected function get_js_config(): array {
                return [];
            }

            /**
             * Check if should inject.
             * @return bool
             */
            protected function should_inject(): bool {
                return false; // Should not inject.
            }

            /**
             * Override inject_javascript to capture calls.
             * @return void
             */
            public function inject_javascript(): void {
                if (!$this->is_enabled()) {
                    return;
                }

                if (!$this->should_inject()) {
                    return;
                }

                $this->jscalled = true;
            }
        };

        $injection->inject_javascript();

        $this->assertFalse($injection->jscalled);
    }
}
