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

use ReturnTypeWillChange;

/**
 * Generic class for holding Gantt item details.
 * Used to exchange Json data between client-server functions with Ajax calls.
 */
class Item implements \JsonSerializable
{
    public $id;
    public $linktask_id;
    public $start_date; // format 2019-09-07 04:06:15
    public $end_date;
    public $text;
    public $content;
    public $comment;
    public $type; // project / task / milestone
    public $progress;
    public $parent;
    public $open; // 1 / 0

    public function __construct()
    {
        $this->id = 0;
        $this->start_date = date("Y-m-d H:i:s");
        $this->progress = 0.0;
        $this->parent = "";
        $this->open = 1;
    }

    /**
     * Populates Item instances with Json data
     *
     * @param $json Json data
     */
    public function populateFrom($json)
    {
        if (isset($json["id"])) {
            $this->id = $json["id"];
        }
        if (isset($json["parent"])) {
            $this->parent = $json["parent"];
        }
        if (isset($json["start_date"])) {
            $this->start_date = $json["start_date"];
        }
        if (isset($json["end_date"])) {
            $this->end_date = $json["end_date"];
        }
        if (isset($json["progress"])) {
            $this->progress = $json["progress"];
        }
        if (isset($json["name"])) {
            $this->text = $json["name"];
        }
        if (isset($json["type"])) {
            $this->type = $json["type"];
        }
    }

    /**
     * Enables Json serialization of Item objects
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return (array)$this;
    }
}
