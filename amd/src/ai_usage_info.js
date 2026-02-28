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
 * AI usage info modal.
 *
 * Provides a function to show an info-only modal displaying AI usage hints.
 *
 * @module     local_ai_injection/ai_usage_info
 * @copyright  2026 ISB Bayern
 * @author     Thomas Sch\u00f6nlein
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {renderInfoBox} from 'local_ai_manager/infobox';
import {renderWarningBox} from 'local_ai_manager/warningbox';
import ModalCancel from 'core/modal_cancel';
import Templates from 'core/templates';
import {getString} from 'core/str';
import {userId} from 'core/config';

/**
 * Show an info-only modal displaying AI usage hints.
 *
 * @param {string} component the component from which the request is being done
 * @param {array} purposes the purpose to use for the request
 */
export const showAiInfo = async(component, purposes = []) => {

    const [bodyHtml, title, closeText] = await Promise.all([
        Templates.render('local_ai_injection/ai_usage_info_modal', {}),
        getString('info_aiusage', 'local_ai_injection'),
        getString('close', 'local_ai_injection'),
    ]);

    const modal = await ModalCancel.create({
        title,
        body: bodyHtml,
        large: true,
        show: true,
        removeOnClose: true,
        buttons: {
            cancel: closeText,
        },
    });

    const container = modal.getBody()[0].querySelector('.local_ai_injection-info-content');
    await Promise.all([
        renderInfoBox(component, userId, container, purposes),
        renderWarningBox(container),
    ]);
};
