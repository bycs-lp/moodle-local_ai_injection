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
 * Language strings for aiinjection_alttext.
 *
 * @package    aiinjection_alttext
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['aiprompt'] = 'Generate a precise alt text for the image in the following language: {$a}. Describe briefly and factually what is seen in the image. The alt text should be understandable for visually impaired people. Do not use any special characters, especially no quotes.';
$string['alttext:use'] = 'Use AI Alt Text generation';
$string['apply'] = 'Apply';
$string['generatealttext'] = 'Generate Image Description';
$string['generatedalttext'] = 'Generated Image Description';
$string['generateerror'] = 'Error generating image description. Please try again.';
$string['generateerrorwithmessage'] = 'Error generating image description: {$a}';
$string['generating'] = 'Generating';
$string['generatingdescription'] = 'Generating description...';
$string['noalttextgenerated'] = 'No image description generated';
$string['plugin_desc'] = 'Enable automatic generation of image descriptions in the file picker using AI';
$string['pluginname'] = 'AI Image Description';
$string['privacy:metadata'] = 'The AI Image Description plugin does not store any personal data.';
$string['regenerate'] = 'Regenerate';
$string['retry'] = 'Retry';
$string['selector'] = 'Image Selector';
$string['selector_desc'] = 'CSS selector to identify images that need image descriptions (default: img:not([alt]), img[alt=""])';
