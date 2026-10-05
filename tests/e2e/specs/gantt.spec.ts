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

import { expect, test } from '../fixtures/gantt_fixture';
import { GanttPage } from '../pages/GanttPage';
import { Profiles } from '../../../../../tests/e2e/utils/Profiles';
import { getWorkerEntityId } from '../../../../../tests/e2e/utils/WorkerEntities';

// Marks a changeParent() request among gantt.php's other POST actions.
const CHANGE_ITEM_PARENT_FLAG = 'changeItemParent=1';
// Marks an onTaskDrag() request among gantt.php's other POST actions.
const UPDATE_TASK_FLAG = 'updateTask=1';

function toGlpiDate(date: Date): string {
    return date.toISOString().slice(0, 19).replace('T', ' ');
}

test.describe('Gantt view', () => {
    // Wide enough (past and future) that "today" always falls inside the range.
    const today = new Date();
    const plan_start = new Date(today);
    plan_start.setDate(plan_start.getDate() - 5);
    const plan_end = new Date(today);
    plan_end.setDate(plan_end.getDate() + 10);

    test('renders, highlights today, and reparents a task via drag and drop', async ({ page, profile, api }) => {
        await profile.set(Profiles.SuperAdmin);

        const project_id = await api.createItem('Project', {
            name: `E2E Gantt project ${test.info().workerIndex}-${Date.now()}`,
            entities_id: getWorkerEntityId(),
            plan_start_date: toGlpiDate(plan_start),
            plan_end_date: toGlpiDate(plan_end),
        });

        await api.createItem('ProjectTask', {
            name: 'E2E Task A',
            projects_id: project_id,
            entities_id: getWorkerEntityId(),
            plan_start_date: toGlpiDate(plan_start),
            plan_end_date: toGlpiDate(today),
        });
        await api.createItem('ProjectTask', {
            name: 'E2E Task B',
            projects_id: project_id,
            entities_id: getWorkerEntityId(),
            plan_start_date: toGlpiDate(today),
            plan_end_date: toGlpiDate(plan_end),
        });
        await api.createItem('ProjectTask', {
            name: 'E2E Task C',
            projects_id: project_id,
            entities_id: getWorkerEntityId(),
            plan_start_date: toGlpiDate(today),
            plan_end_date: toGlpiDate(plan_end),
        });

        const gantt = new GanttPage(page);
        await gantt.goto(project_id);

        // Renders: the tasks created via the API are all visible in the timeline.
        await expect(gantt.getRow('E2E Task A')).toBeVisible();
        await expect(gantt.getRow('E2E Task B')).toBeVisible();
        await expect(gantt.getRow('E2E Task C')).toBeVisible();

        // Today highlight: replaces the marker plugin dropped in v10 community edition.
        await gantt.zoomToDays();
        await expect(gantt.today_cells.first()).toBeVisible();

        // Drag and drop: reparenting Task B under Task A persists across a reload.
        const task_a_id_client = await gantt.getTaskId('E2E Task A');
        await gantt.dragRowOnto('E2E Task B', 'E2E Task A');
        const task_b_id = await gantt.getTaskId('E2E Task B');
        expect(await gantt.getParentId(task_b_id!)).toBe(task_a_id_client);

        await gantt.goto(project_id);
        expect(await gantt.getParentId(task_b_id!)).toBe(task_a_id_client);

        // Rollback only happens on an explicit { ok: false } response, not on a transport error.
        await page.route('**/ajax/gantt.php', async (route) => {
            const post_data = route.request().postData() ?? '';
            if (post_data.includes(CHANGE_ITEM_PARENT_FLAG)) {
                await route.fulfill({ json: { ok: false, error: 'Simulated rejection' } });
                return;
            }
            await route.continue();
        });
        await gantt.dragRowOnto('E2E Task C', 'E2E Task A');
        const task_c_id = await gantt.getTaskId('E2E Task C');
        expect(String(await gantt.getParentId(task_c_id!))).toBe(String(project_id));
    });

    test('rolls back a task drag on a server-rejected date change', async ({ page, profile, api }) => {
        await profile.set(Profiles.SuperAdmin);

        const project_id = await api.createItem('Project', {
            name: `E2E Gantt drag project ${test.info().workerIndex}-${Date.now()}`,
            entities_id: getWorkerEntityId(),
            plan_start_date: toGlpiDate(plan_start),
            plan_end_date: toGlpiDate(plan_end),
        });

        await api.createItem('ProjectTask', {
            name: 'E2E Task D',
            projects_id: project_id,
            entities_id: getWorkerEntityId(),
            plan_start_date: toGlpiDate(today),
            plan_end_date: toGlpiDate(plan_end),
        });

        const gantt = new GanttPage(page);
        await gantt.goto(project_id);
        await gantt.zoomToDays();

        const task_id = await gantt.getTaskId('E2E Task D');
        const original_dates = await page.evaluate((id) => {
            const task = (window as any).gantt.getTask(id);
            return { start_date: task.start_date.getTime(), end_date: task.end_date.getTime() };
        }, task_id);

        await page.route('**/ajax/gantt.php', async (route) => {
            const post_data = route.request().postData() ?? '';
            if (post_data.includes(UPDATE_TASK_FLAG)) {
                await route.fulfill({ json: { ok: false, error: 'Simulated rejection' } });
                return;
            }
            await route.continue();
        });

        await gantt.dragTaskBarByDays(task_id!, 2);
        await expect(page.getByText('Simulated rejection')).toBeVisible();

        const rolled_back_dates = await page.evaluate((id) => {
            const task = (window as any).gantt.getTask(id);
            return { start_date: task.start_date.getTime(), end_date: task.end_date.getTime() };
        }, task_id);
        expect(rolled_back_dates).toEqual(original_dates);
    });
});
