<?php

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
