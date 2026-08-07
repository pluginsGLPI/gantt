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

import { Locator, Page } from '@playwright/test';
import { GlpiPage } from '../../../../../tests/e2e/pages/GlpiPage';

/** Page helpers for the dhtmlx-gantt view rendered on a project's Gantt tab. */
export class GanttPage extends GlpiPage {
    public readonly container: Locator;
    public readonly today_cells: Locator;

    public constructor(page: Page) {
        super(page);
        this.container = this.page.getByTestId('gantt-container');
        // eslint-disable-next-line playwright/no-raw-locators -- dhtmlx-gantt cell, no semantic hook available
        this.today_cells = this.page.locator('.gantt_task_cell.today');
    }

    /** Navigates to a project's Gantt tab and waits for the chart to be ready. */
    public async goto(project_id: number): Promise<void> {
        await this.page.goto(`/front/project.form.php?id=${project_id}&forcetab=GlpiPlugin%5CGantt%5CProjectTab$1`);
        await this.container.waitFor({ state: 'visible' });
        // The loader overlay is appended after the fetch starts, so wait for a row instead.
        // eslint-disable-next-line playwright/no-raw-locators -- dhtmlx-gantt row, no semantic hook available
        await this.page.locator('.gantt_row').first().waitFor({ state: 'visible' });
    }

    /** The rendered row for a task/project whose label contains the given text. */
    public getRow(label: string): Locator {
        // eslint-disable-next-line playwright/no-raw-locators -- dhtmlx-gantt row, no semantic hook available
        return this.page.locator('.gantt_row').filter({ hasText: label }).first();
    }

    /** Switches to the "Days" zoom level, the only one where each column is a single day. */
    public async zoomToDays(): Promise<void> {
        await this.page.getByTestId('gantt-zoom-days').click();
        // Zooming keeps the previous scroll offset, so scroll to today explicitly.
        await this.page.evaluate(() => (window as any).gantt.showDate(new Date()));
    }

    /** Reads the internal dhtmlx task id of the first task/project matching the given label. */
    public async getTaskId(label: string): Promise<number | string | null> {
        return this.page.evaluate((text) => {
            let id: number | string | null = null;
            (window as any).gantt.eachTask((task: any) => {
                if (id === null && task.text === text) {
                    id = task.id;
                }
            });
            return id;
        }, label);
    }

    /** Reads the current parent id (dhtmlx data model) of the given task id. */
    public async getParentId(task_id: number | string): Promise<number | string> {
        return this.page.evaluate((id) => (window as any).gantt.getTask(id).parent, task_id);
    }

    /** Drags a row onto another one to trigger dhtmlx's row reparenting. */
    public async dragRowOnto(source_label: string, target_label: string): Promise<void> {
        const source_box = await this.getRow(source_label).boundingBox();
        const target_box = await this.getRow(target_label).boundingBox();
        if (source_box === null || target_box === null) {
            throw new Error('Could not locate the source or target row to drag');
        }

        // eslint-disable-next-line playwright/no-raw-locators -- dhtmlx-gantt internal drop marker, no semantic hook available
        const drop_marker = this.page.locator('.gantt_grid_dnd_marker');

        await this.page.mouse.move(source_box.x + 20, source_box.y + source_box.height / 2);
        await this.page.mouse.down();
        await this.page.mouse.move(source_box.x + 20, source_box.y + source_box.height / 2 - 10, { steps: 5 });
        await drop_marker.waitFor({ state: 'visible' });
        await this.page.mouse.move(target_box.x + 20, target_box.y + target_box.height / 2, { steps: 10 });
        await drop_marker.waitFor({ state: 'visible' });
        await this.page.mouse.up();
        await drop_marker.waitFor({ state: 'hidden' });
    }
}
