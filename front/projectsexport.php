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

use GlpiPlugin\Gantt\ProjectsExport;

include('../../../inc/includes.php');

$project_id = $_GET['project_id'] ?? -1;

// Check right
Session::checkRightsOr(Project::$rightname, [Project::READALL, Project::READMY]);

// Get the project name
$project = new Project();
$project->getFromDB($project_id);
$project_name = $project->fields['name'] ?? 'projects';
$project_name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $project_name); // Remove special characters

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="gantt-data-' . $project_name . '.json"');

echo (new ProjectsExport($project_id))->json();
