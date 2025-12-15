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

/**
 * Unit tests for AI Injection library functions.
 *
 * @package    local_ai_injection
 * @copyright  2025 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_injection;

use advanced_testcase;
use Exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/ai_injection/lib.php');

/**
 * Test class for AI injection library functions.
 *
 * @package    local_ai_injection
 * @copyright  2025 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::local_ai_injection_get_subplugins
 * @covers     ::local_ai_injection_get_enabled_subplugins
 */
final class lib_test extends advanced_testcase {
    /**
     * Setup before each test.
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test getting subplugins via library function.
     *
     * @return void
     */
    public function test_get_subplugins(): void {
        $subplugins = local_ai_injection_get_subplugins();

        // Should return array.
        if (!is_array($subplugins)) {
            throw new Exception('get_subplugins should return array');
        }

        // Should contain alttext subplugin.
        if (!isset($subplugins['alttext'])) {
            throw new Exception('alttext subplugin should be available');
        }

        // Path should exist.
        if (!is_dir($subplugins['alttext'])) {
            throw new Exception('alttext subplugin path should exist');
        }
    }

    /**
     * Test getting enabled subplugins.
     *
     * @return void
     */
    public function test_get_enabled_subplugins(): void {
        // Store initial state.
        $initialalttextenabled = get_config('aiinjection_alttext', 'enabled');

        // Disable all plugins first.
        set_config('enabled', 0, 'aiinjection_alttext');

        // Should be empty when disabled.
        $enabled = local_ai_injection_get_enabled_subplugins();
        $this->assertIsArray($enabled, 'get_enabled_subplugins should return an array');
        $this->assertEmpty($enabled, 'Should have no enabled subplugins when all disabled');

        // Enable alttext subplugin.
        set_config('enabled', 1, 'aiinjection_alttext');

        // Should now appear in enabled list.
        $enabled = local_ai_injection_get_enabled_subplugins();
        $this->assertIsArray($enabled, 'get_enabled_subplugins should return an array');
        $this->assertArrayHasKey('alttext', $enabled, 'alttext should be in enabled subplugins');

        // Restore initial state.
        set_config('enabled', $initialalttextenabled ? 1 : 0, 'aiinjection_alttext');
    }

    /**
     * Test subplugin configuration handling.
     *
     * @return void
     */
    public function test_subplugin_configuration(): void {
        // Test setting and getting configuration.
        $testvalue = 'test-configuration-value';
        set_config('testconfig', $testvalue, 'aiinjection_alttext');

        $retrievedvalue = get_config('aiinjection_alttext', 'testconfig');
        $this->assertEquals($testvalue, $retrievedvalue, 'Configuration value should match what was set');

        // Test boolean configuration.
        set_config('enabled', 1, 'aiinjection_alttext');
        $enabled = get_config('aiinjection_alttext', 'enabled');
        $this->assertEquals('1', $enabled, 'Boolean configuration should work (as string)');

        // Clean up.
        unset_config('testconfig', 'aiinjection_alttext');
    }

    /**
     * Test subplugin path validation.
     *
     * @return void
     */
    public function test_subplugin_paths(): void {
        global $CFG;

        $subplugins = local_ai_injection_get_subplugins();

        foreach ($subplugins as $name => $path) {
            // Path should be within Moodle directory.
            $this->assertStringStartsWith($CFG->dirroot, $path, "Subplugin path should be within Moodle directory: $path");

            // Path should exist.
            $this->assertDirectoryExists($path, "Subplugin directory should exist: $path");

            // Should have version.php.
            $versionfile = $path . '/version.php';
            $this->assertFileExists($versionfile, "Subplugin should have version.php: $versionfile");
        }
    }
}
