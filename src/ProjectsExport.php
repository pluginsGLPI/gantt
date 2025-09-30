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
 * @copyright 2015-2024 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/gantt
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Gantt;

use function Safe\json_encode;

final class ProjectsExport
{
    private int $project_id;

    public function __construct(int $project_id)
    {
        $this->project_id = $project_id;
    }

    private function loadProjectsData(): array
    {
        $items   = [];
        $factory = new DataFactory();
        $factory->getItemsForProject($items, $this->project_id);

        $id_refs   = [];
        $to_export = [];
        foreach ($items as $item) {
            switch ($item->type) {
                case 'project':
                    $project = [
                        'id'         => $item->id,
                        'name'       => $item->text,
                        'start_date' => $item->start_date,
                        'end_date'   => $item->end_date,
                    ];

                    $id_refs[$item->id] = array_merge(
                        $id_refs[$item->id] ?? [],
                        $project,
                    );
                    if ($item->parent == 0) {
                        $to_export[] = &$id_refs[$item->id];
                    } else {
                        $id_refs[$item->parent]['projects'][] = &$id_refs[$item->id];
                    }
                    break;
                case 'task':
                    $task = [
                        'id'         => $item->linktask_id,
                        'name'       => $item->text,
                        'start_date' => $item->start_date,
                        'end_date'   => $item->end_date,
                        'progress'   => $item->progress,
                    ];

                    $id_refs[$item->id] = array_merge(
                        $id_refs[$item->id] ?? [],
                        $task,
                    );
                    $id_refs[$item->parent]['tasks'][] = &$id_refs[$item->id];
                    break;
                case 'milestone':
                    $milestone = [
                        'id'   => $item->linktask_id,
                        'name' => $item->text,
                        'date' => $item->start_date,
                    ];

                    $id_refs[$item->id] = array_merge(
                        $id_refs[$item->id] ?? [],
                        $milestone,
                    );
                    $id_refs[$item->parent]['milestones'][] = &$id_refs[$item->id];
                    break;
            }
        }

        return $to_export;
    }

    public function json(): string
    {
        return json_encode($this->loadProjectsData());
    }
}
