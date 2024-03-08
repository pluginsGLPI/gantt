<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2024 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Gantt;

final class ProjectsExport
{
    private int $project_id;

    public function __construct(int $project_id) {
        $this->project_id = $project_id;
    }

    private function loadProjectsData(): array
    {
        $items = [];
        $factory = new DataFactory();
        $factory->getItemsForProject($items, $this->project_id);

        $id_refs = [];
        $to_export = [];
        foreach ($items as $item) {
            switch ($item->type) {
                case 'project':
                    $project = [
                        'id' => $item->id,
                        'name' => $item->text,
                        'start_date' => $item->start_date,
                        'end_date' => $item->end_date,
                    ];

                    $id_refs[$item->id] = array_merge(
                        $id_refs[$item->id] ?? [],
                        $project
                    );
                    if ($item->parent == 0) {
                        $to_export[] = &$id_refs[$item->id];
                    } else {
                        $id_refs[$item->parent]['projects'][] = &$id_refs[$item->id];
                    }
                    break;
                case 'task':
                    $task = [
                        'id' => $item->linktask_id,
                        'name' => $item->text,
                        'start_date' => $item->start_date,
                        'end_date' => $item->end_date,
                        'progress' => $item->progress,
                    ];

                    $id_refs[$item->id] = array_merge(
                        $id_refs[$item->id] ?? [],
                        $task
                    );
                    $id_refs[$item->parent]['tasks'][] = &$id_refs[$item->id];
                    break;
                case 'milestone':
                    $milestone = [
                        'id' => $item->linktask_id,
                        'name' => $item->text,
                        'date' => $item->start_date,
                    ];

                    $id_refs[$item->id] = array_merge(
                        $id_refs[$item->id] ?? [],
                        $milestone
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
