<?php

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

namespace GlpiPlugin\Gantt;

use DBmysql;
use Glpi\RichText\RichText;

/**
 * Class used to prepare data for Gantt
 */
class DataFactory
{
    /**
     * Recursive function used to get all subitems of a project, when $id > 0.
     * Returns all projects with their subitems if $id == -1 (for global gantt view).
     *
     * @param array $itemArray Array holding the result
     * @param integer $id ID of the parent project
     */
    public function getItemsForProject(&$itemArray, $id)
    {
        /** @var DBmysql $DB */
        global $DB;
        $project = new \Project();
        if ($id == -1) {
            $iterator = $DB->request([
                'FROM'  => 'glpi_projects',
                'WHERE' => [
                    'projects_id'          => 0,
                    'show_on_global_gantt' => 1,
                    'is_template'          => 0,
                    'is_deleted'           => 0,
                ] + getEntitiesRestrictCriteria('glpi_projects', '', '', true),
                'ORDERBY' => 'glpi_projects.plan_start_date',
            ]);
            foreach ($iterator as $data) {
                $this->getItemsForProject($itemArray, $data['id']);
            }
        } elseif ($project->getFromDB($id)) {
            if ($project->canViewItem()) {
                array_push($itemArray, $this->populateGanttItem($project->fields, 'root-project'));
                $this->getProjectTasks($itemArray, $id);
                $this->getSubprojects($itemArray, $id); // subproject tasks included
            }
        }
    }

    /**
     * Function used to get project task links
     *
     * @param array $itemArray Input array holding project and task items
     *
     * @return array $links Array of Link objects
     */
    public function getProjectTaskLinks($itemArray)
    {
        $links = [];
        $ids = [];
        foreach ($itemArray as $item) {
            if ($item->type != 'project') {
                $ids[] = $item->linktask_id;
            }
        }

        if (count($ids) > 0) {
            $linkDao = new LinkDAO();
            $links   = $linkDao->getLinksForItemIDs($ids);
        }

        return $links;
    }

    /**
     * Recursive function used to get all subprojects and tasks of a project
     *
     * @param array $itemArray Array holding the items
     * @param integer $projectId ID of the parent project
     *
     */
    public function getSubprojects(&$itemArray, $projectId)
    {
        /** @var DBmysql $DB */
        global $DB;
        $iterator = $DB->request('glpi_projects', ['projects_id' => $projectId, 'is_template' => 0, 'is_deleted' => 0]);

        foreach ($iterator as $record) {
            $proj = new \Project();
            $proj->getFromDB($record['id']);

            if ($proj->canViewItem()) {
                array_push($itemArray, $this->populateGanttItem($record, 'project'));
                $this->getSubprojects($itemArray, $record['id']);
                $this->getProjectTasks($itemArray, $record['id']);
            }
        }
    }

    /**
     * Function used to get all tasks of a project
     *
     * @param array $itemArray Array holding the task items
     * @param integer $projectId ID of the project
     */
    public function getProjectTasks(&$itemArray, $projectId)
    {
        $taskRecords[] = \ProjectTask::getAllForProject($projectId);
        foreach ($taskRecords[0] as $record) {
            $task = new \ProjectTask();
            $task->getFromDB($record['id']);
            if (!$task->canViewItem() || $record['is_template'] == 1) {
                continue;
            }
            array_push($itemArray, $this->populateGanttItem($record, 'task'));
        }
    }

    /**
     * Function used to get all subtasks of a task
     *
     * @param array $itemArray Array holding the task items
     * @param integer $taskId ID of the parent task
     */
    public function getSubtasks(&$itemArray, $taskId)
    {
        $taskRecords[] = \ProjectTask::getAllForProjectTask($taskId);
        foreach ($taskRecords[0] as $record) {
            $this->getSubtasks($itemArray, $record['id']);

            $task = new \ProjectTask();
            $task->getFromDB($record['id']);
            if (!$task->canViewItem() || $record['is_template'] == 1) {
                continue;
            }
            array_push($itemArray, $this->populateGanttItem($record, 'task'));
        }
    }

    /**
     * Function used to populate gantt Item objects with projects/tasks/milestones data
     *
     * @param $record Project or task record from database
     * @param string $type Specifies the type of the record (project, task or milestone)
     *
     * @return Item instance
     */
    public function populateGanttItem($record, $type)
    {
        if (isset($record['is_milestone']) && $record['is_milestone'] > 0) {
            $type = 'milestone';
        }

        $parentTaskUid = '';
        if (($type == 'task' || $type == 'milestone') && $record['projecttasks_id'] > 0) {
            $parentTask = new \ProjectTask();
            $parentTask->getFromDB($record['projecttasks_id']);
            $parentTaskUid = $parentTask->fields['uuid'];
        }

        $item              = new Item();
        $item->id          = ($type == 'project' || $type == 'root-project') ? $record['id'] : $record['uuid'];
        $item->type        = ($type == 'root-project') ? 'project' : $type;
        $item->parent      = ($type == 'root-project') ? 0 : (($type == 'project') ? $record['projects_id'] : ($record['projecttasks_id'] > 0 ? $parentTaskUid : $record['projects_id']));
        $item->linktask_id = ($item->type != 'project') ? $record['id'] : 0;
        $item->start_date  = $record['real_start_date'] ?? $record['plan_start_date'] ?? $_SESSION['glpi_currenttime'];
        $item->end_date    = $record['real_end_date']   ?? $record['plan_end_date'] ?? date('Y-m-d H:i:s', strtotime($item->start_date . ' + 1 day'));
        $item->text        = $record['name'];
        $item->content     = isset($record['content']) ? RichText::getSafeHtml($record['content']) : '';
        $item->comment     = $record['comment'] ?? '';
        $item->progress    = $record['percent_done'] / 100;

        return $item;
    }
}
