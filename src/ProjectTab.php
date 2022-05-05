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
 * @copyright Copyright (C) 2013-2022 by gantt plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/gantt
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Gantt;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


use Glpi\Application\View\TemplateRenderer;

class ProjectTab extends \CommonGLPI
{

    static function getTypeName($nb = 0)
    {
        return __('Gantt', 'gantt');
    }


    function getTabNameForItem(\CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof \Project) {
            return self::createTabEntry(self::getTypeName());
        }
    }

    static function displayTabContentForItem(\CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof \Project) {
            self::showForProject($item->getId());
        }
    }

    static function showForProject(int $project_id = -1)
    {
        TemplateRenderer::getInstance()->display('@gantt/view.html.twig', [
            'id' => $project_id,
        ]);
    }

    static function addGlobalGanttToMenu(array $menu): array {
        if (isset($menu['tools']['content']['project']['links'])) {
            $label = '
                <i class="fas fa-stream" title="' . __('Global GANTT', 'gantt') . '"></i>
                <span class="d-none d-xxl-block">
                ' . __('Global GANTT', 'gantt') . '
                </span>
            ';
            $menu['tools']['content']['project']['links'][$label] = \Plugin::getPhpDir('gantt', false) . '/front/global.php';
        }

        return $menu;
    }
}
