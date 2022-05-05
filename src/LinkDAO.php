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

use \ProjectTaskLink;

/**
 * DAO class for handling project task links
 */
class LinkDAO
{
    public function getLinksForItemIDs($ids)
    {
        $links = [];
        $tasklink = new ProjectTaskLink();

        $ids = implode(',', $ids);
        $iterator = $tasklink->getFromDBForItemIDs($ids);
        foreach ($iterator as $data) {
            array_push($links, $this->populateFromDB($data));
        }

        return $links;
    }

    /**
     * Populates a Link object with data
     *
     * @param $data Database record
     *
     * @return Link object
     */
    public function populateFromDB($data)
    {
        $link = new Link();
        $link->id = $data["id"];
        $link->source = $data["source_uuid"];
        $link->target = $data["target_uuid"];
        $link->type = $data["type"];
        $link->lag = $data["lag"];
        $link->lead = $data["lead"];
        return $link;
    }
}
