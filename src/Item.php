<?php

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
