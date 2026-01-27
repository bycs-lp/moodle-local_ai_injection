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

use advanced_testcase;

/**
 * Unit tests for alttext injection class.
 *
 * @package    aiinjection_alttext
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \aiinjection_alttext\local\injection
 * @group      aiinjection_alttext
 */
final class injection_test extends advanced_testcase {
    /**
     * Test injection class properties and inheritance.
     */
    public function test_injection_class_properties(): void {
        $this->resetAfterTest(true);

        $injection = new injection();

        // Test inheritance.
        $this->assertInstanceOf(\local_ai_injection\local\base_injection::class, $injection);

        // Test public methods return expected values.
        $this->assertEquals('aiinjection_alttext', $injection->get_subplugin_name());
        $this->assertEquals('aiinjection_alttext/alttext_injection', $injection->get_js_module_name());
        $this->assertIsArray($injection->get_js_config());

        // Test enabled state.
        set_config('enabled', 1, 'aiinjection_alttext');
        $this->assertTrue($injection->is_enabled());

        set_config('enabled', 0, 'aiinjection_alttext');
        $this->assertFalse($injection->is_enabled());
    }

    /**
     * Test should_inject respects capability.
     *
     * Note: The full should_inject logic depends on local_ai_manager configuration.
     * This test focuses on the capability check only.
     */
    public function test_should_inject_respects_capability(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        $injection = new injection();

        // Regular user without capability - should not inject.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse($injection->should_inject());

        // Admin has capability but without configured ai_manager,
        // should_inject returns false due to AVAILABILITY_HIDDEN.
        // The capability check happens before AI config check.
        $this->setAdminUser();
        // The full test would require a configured local_ai_manager instance.
        // We just verify no exceptions are thrown.
        $result = $injection->should_inject();
        $this->assertIsBool($result);
    }
}
