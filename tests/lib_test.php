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

namespace local_ai_injection;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/ai_injection/lib.php');

/**
 * Unit tests for AI Injection library functions.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::local_ai_injection_get_subplugins
 * @covers     ::local_ai_injection_get_enabled_subplugins
 * @group      local_ai_injection
 */
final class lib_test extends advanced_testcase {
    /**
     * Test subplugin discovery and enabled state.
     */
    public function test_subplugin_discovery_and_enabled_state(): void {
        $this->resetAfterTest(true);

        // Test subplugin discovery.
        $subplugins = local_ai_injection_get_subplugins();
        $this->assertIsArray($subplugins);
        $this->assertArrayHasKey('alttext', $subplugins);
        $this->assertDirectoryExists($subplugins['alttext']);

        // Disable all - should return empty.
        set_config('enabled', 0, 'aiinjection_alttext');
        $enabled = local_ai_injection_get_enabled_subplugins();
        $this->assertEmpty($enabled);

        // Enable alttext - should appear in enabled list.
        set_config('enabled', 1, 'aiinjection_alttext');
        $enabled = local_ai_injection_get_enabled_subplugins();
        $this->assertArrayHasKey('alttext', $enabled);
    }
}
