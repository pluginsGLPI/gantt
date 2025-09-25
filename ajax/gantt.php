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
use GlpiPlugin\Gantt\DataFactory;
use GlpiPlugin\Gantt\Item;
use GlpiPlugin\Gantt\ProjectDAO;
use GlpiPlugin\Gantt\TaskDAO;

use function Safe\json_encode;

/** @var array $CFG_GLPI */
global $CFG_GLPI;

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkLoginUser();

$id = 0;

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}

if (isset($_REQUEST['getData'])) {
    $itemArray = [];
    $factory   = new DataFactory();
    $factory->getItemsForProject($itemArray, $id);
    $links = $factory->getProjectTaskLinks($itemArray);

    usort($itemArray, function ($a, $b) {
        $lengthA = $a->id !== null ? strlen($a->id) : 0;
        $lengthB = $b->id !== null ? strlen($b->id) : 0;
        return $lengthA <=> $lengthB;
    });

    $result = [
        'data'  => $itemArray,
        'links' => $links,
    ];
    echo json_encode($result);
} elseif (isset($_POST['addTask'])) {
    try {
        $item = new Item();
        $task = $_POST['task'];
        $item->populateFrom($task);
        $taskDAO   = new TaskDAO();
        $newTask   = $taskDAO->addTask($item);
        $factory   = new DataFactory();
        $ganttItem = $factory->populateGanttItem($newTask->fields, 'task');

        $result = [
            'ok'   => true,
            'item' => $ganttItem,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['updateTask'])) {
    try {
        $updated = false;
        $item    = new Item();
        $task    = $_POST['task'];
        $item->populateFrom($task);
        $taskDAO = new TaskDAO();
        $updated = $taskDAO->updateTask($item);
        $result  = [
            'ok' => $updated,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['changeItemParent'])) {
    try {
        $p_item   = $_POST['item'];
        $p_target = $_POST['target'];

        if ($p_item['type'] == 'project' && $p_target['type'] != 'project') {
            throw new Exception(__s('Target item must be of project type', 'gantt'));
        }

        $item = new Item();
        $item->populateFrom($p_item);
        $target = new Item();
        $target->populateFrom($p_target);

        $item->parent = $target->id;
        $dao = $p_item['type'] == 'project' ? new ProjectDAO() : new TaskDAO();
        $dao->updateParent($item);

        $result = [
            'ok' => true,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['makeRootProject'])) {
    try {
        $p_item = $_POST['item'];

        // double check for safety..
        if ($p_item['type'] != 'project') {
            throw new Exception(__s('Item must be of project type', 'gantt'));
        }

        $item = new Item();
        $item->populateFrom($p_item);
        $dao = new ProjectDAO();
        $dao->updateParent($item);

        $result = [
            'ok' => true,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['addProject'])) {
    try {
        $item    = new Item();
        $project = $_POST['project'];
        $item->populateFrom($project);
        $dao       = new ProjectDAO();
        $newProj   = $dao->addProject($item);
        $factory   = new DataFactory();
        $ganttItem = $factory->populateGanttItem($newProj->fields, 'project');

        $result = [
            'ok'   => true,
            'item' => $ganttItem,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['updateProject'])) {
    try {
        $updated = false;
        $item    = new Item();
        $project = $_POST['project'];
        $item->populateFrom($project);
        $projectDAO = new ProjectDAO();
        $updated    = $projectDAO->updateProject($item);
        $result     = [
            'ok' => $updated,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['addTaskLink'])) {
    try {
        $taskLink = new ProjectTaskLink();

        if ($taskLink->checkIfExist($_POST['taskLink'])) {
            throw new Exception(__s('Link already exist!', 'gantt'));
        }

        $id     = $taskLink->add($_POST['taskLink']);
        $result = [
            'ok' => true,
            'id' => $id,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['updateTaskLink'])) {
    try {
        $taskLink = new ProjectTaskLink();
        $taskLink->update($_POST['taskLink']);
        $result = [
            'ok' => true,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_POST['deleteTaskLink'])) {
    try {
        $taskLink = new ProjectTaskLink();
        $taskLink->delete($_POST);
        $result = [
            'ok' => true,
        ];
    } catch (Exception $ex) {
        $result = [
            'ok'    => false,
            'error' => $ex->getMessage(),
        ];
    }
    echo json_encode($result);
} elseif (isset($_REQUEST['openEditForm'])) {
    $result       = [];
    $result['ok'] = true;
    if ($_POST['item']['type'] == 'project') {
        $result['url'] = $CFG_GLPI['root_doc'] . '/front/project.form.php?id=' . $_POST['item']['id'] . '&forcetab=Project';
    } else {
        $result['url'] = $CFG_GLPI['root_doc'] . '/front/projecttask.form.php?id=' . $_POST['item']['linktask_id'] . '&forcetab=ProjectTask';
    }
    echo json_encode($result);
}
