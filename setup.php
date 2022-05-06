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

define('PLUGIN_GANTT_VERSION', '1.0.0');

// Minimal GLPI version, inclusive
define("PLUGIN_GANTT_MIN_GLPI_VERSION", "10.0.1");
// Maximum GLPI version, exclusive
define("PLUGIN_GANTT_MAX_GLPI_VERSION", "10.0.99");

use Glpi\Plugin\Hooks;

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_gantt()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['gantt'] = true;

    $plugin = new Plugin();
    if (
        !$plugin->isInstalled('gantt')
        || !$plugin->isActivated('gantt')
    ) {
        return false;
    }

    Plugin::registerClass('GlpiPlugin\Gantt\ProjectTab', [
        'addtabon' => 'Project'
    ]);

    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['gantt'][] = 'public/build/libs.js';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['gantt'][] = 'js/gantt-helper.js';

    $PLUGIN_HOOKS[Hooks::ADD_CSS]['gantt'][] = 'css/gantt.scss';

    $PLUGIN_HOOKS[Hooks::REDEFINE_MENUS]['gantt'] = [
        'GlpiPlugin\Gantt\ProjectTab',
        'addGlobalGanttToMenu'
    ];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_gantt()
{
    return [
        'name'           => 'gantt',
        'version'        => PLUGIN_GANTT_VERSION,
        'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
        'license'        => 'GPL-2.0-or-later',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_GANTT_MIN_GLPI_VERSION,
                'max' => PLUGIN_GANTT_MAX_GLPI_VERSION,
            ]
        ]
    ];
}
