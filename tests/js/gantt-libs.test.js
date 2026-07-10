/**
 * -------------------------------------------------------------------------
 * gantt plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of gantt.
 *
 * gantt is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 *
 * gantt is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with gantt. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2013-2023 by gantt plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/gantt
 * -------------------------------------------------------------------------
 */

// Loads the actual webpack bundle (built via the "build"/"postinstall" npm script)
// the same way the browser does, to catch dhtmlx-gantt upgrades that break the
// library's own module packaging or drop APIs this plugin relies on.
describe('dhtmlx-gantt bundle (public/lib/libs.js)', () => {
    beforeAll(() => {
        require('../../public/lib/libs.js');
    });

    test('exposes a usable gantt instance on window', () => {
        expect(window.gantt).toBeDefined();
        expect(typeof window.gantt).toBe('object');
    });

    test('enables the tooltip/fullscreen/undo/marker plugins used by gantt-helper.js', () => {
        expect(() => {
            window.gantt.plugins({
                tooltip: true,
                fullscreen: true,
                undo: true,
                marker: true,
            });
        }).not.toThrow();

        expect(typeof window.gantt.addMarker).toBe('function');
        expect(typeof window.gantt.undo).toBe('function');
        expect(typeof window.gantt.ext.fullscreen).toBe('object');
        expect(typeof window.gantt.ext.zoom).toBe('object');
    });

    test.each([
        'addTask', 'alert', 'attachEvent', 'calculateTaskLevel', 'changeLinkId',
        'deleteLink', 'deleteTask', 'eachParent', 'eachTask', 'getChildren',
        'getLightboxSection', 'getLink', 'getTask', 'hideLightbox', 'init',
        'message', 'modalbox', 'parse', 'render', 'resetLightbox', 'sort',
        'updateTask',
    ])('exposes gantt.%s(), used by gantt-helper.js', (method) => {
        expect(typeof window.gantt[method]).toBe('function');
    });

    test('exposes the gantt.date and gantt.i18n helpers used by gantt-helper.js', () => {
        expect(typeof window.gantt.date.add).toBe('function');
        expect(typeof window.gantt.date.date_to_str).toBe('function');
        expect(typeof window.gantt.i18n.setLocale).toBe('function');
    });
});
