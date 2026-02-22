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
 * AI usage confirmation modal.
 *
 * Provides functions to show a confirmation modal before AI features are used
 * and an info-only modal to review AI usage hints.
 *
 * @module     local_ai_injection/confirm_ai_usage
 * @copyright  2026 ISB Bayern
 * @author     Thomas Schönlein
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {renderInfoBox} from 'local_ai_manager/infobox';
import {renderWarningBox} from 'local_ai_manager/warningbox';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalCancel from 'core/modal_cancel';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {getString} from 'core/str';
import LocalStorage from 'core/localstorage';
import {userId} from 'core/config';

const STORAGE_KEY_PREFIX = 'local_ai_injection_ai_confirmed_';

/**
 * Hash a string using SHA-256.
 *
 * @param {string} stringToHash the string to hash
 * @returns {Promise<string>} hex representation of the SHA-256 hash
 */
const hash = async(stringToHash) => {
    const data = new TextEncoder().encode(stringToHash);
    const hashAsArrayBuffer = await window.crypto.subtle.digest('SHA-256', data);
    const uint8ViewOfHash = new Uint8Array(hashAsArrayBuffer);
    return Array.from(uint8ViewOfHash)
        .map((b) => b.toString(16).padStart(2, '0'))
        .join('');
};

/**
 * Get the hashed storage key for the current user.
 *
 * @returns {Promise<string>} hashed storage key
 */
const getStorageKey = async() => {
    return hash(STORAGE_KEY_PREFIX + userId);
};

/**
 * Check if the user has already confirmed AI usage.
 *
 * @returns {Promise<boolean>} true if confirmed
 */
export const isAiUsageConfirmed = async() => {
    const key = await getStorageKey();
    return LocalStorage.get(key) === '1';
};

/**
 * Render the modal body template.
 *
 * @returns {Promise<string>} rendered HTML
 */
const renderModalBody = () => {
    return Templates.render('local_ai_injection/confirm_ai_usage_modal', {});
};

/**
 * Show a confirmation modal for AI usage on first use.
 *
 * @param {string} component the component from which the request is being done
 * @param {array} purposes the purpose to use for the request
 * @returns {Promise<boolean>} True if user confirmed, false if canceled
 */
export const confirmAiUsage = async(component, purposes = []) => {

    const storageKey = await getStorageKey();

    if (LocalStorage.get(storageKey) === '1') {
        return true;
    }

    const [bodyHtml, title, saveButtonText] = await Promise.all([
        renderModalBody(),
        getString('confirmaiusage_title', 'local_ai_injection'),
        getString('confirmaiusage_confirm', 'local_ai_injection'),
    ]);

    const modal = await ModalSaveCancel.create({
        title,
        body: bodyHtml,
        large: true,
        show: true,
        removeOnClose: true,
        buttons: {
            save: saveButtonText,
        },
    });

    const container = modal.getBody()[0].querySelector('.local_ai_injection-confirm-content');
    await Promise.all([
        renderInfoBox(component, userId, container, purposes),
        renderWarningBox(container),
    ]);

    return new Promise(resolve => {
        modal.getRoot().on(ModalEvents.save, () => {
            LocalStorage.set(storageKey, '1');
            resolve(true);
        });
        modal.getRoot().on(ModalEvents.hidden, () => {
            resolve(false);
        });
    });

};

/**
 * Show an info-only modal displaying AI usage hints without requiring confirmation.
 *
 * @param {string} component the component from which the request is being done
 * @param {array} purposes the purpose to use for the request
 */
export const showAiInfo = async(component, purposes = []) => {

    const [bodyHtml, title, closeText] = await Promise.all([
        renderModalBody(),
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

    const container = modal.getBody()[0].querySelector('.local_ai_injection-confirm-content');
    await Promise.all([
        renderInfoBox(component, userId, container, purposes),
        renderWarningBox(container),
    ]);
};
