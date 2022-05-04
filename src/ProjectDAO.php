<?php

namespace GlpiPlugin\Gantt;

/**
 * DAO class for handling project records
 */
class ProjectDAO
{
    public function addProject($project)
    {

        if (!\Project::canCreate()) {
            throw new \Exception(__('Not enough rights'));
        }

        $input = [
            'name' => $project->text,
            'comment' => $project->comment,
            'projects_id' => $project->parent,
            'date' => $_SESSION['glpi_currenttime'],
            'plan_start_date' => $project->start_date,
            'plan_end_date' => $project->end_date,
            'priority' => 3,  //medium
            'projectstates_id' => 1,
            'users_id' => \Session::getLoginUserID(),
            'show_on_global_view' => 1
        ];
        $proj = new \Project();
        $proj->add($input);
        return $proj;
    }

    public function updateProject($project)
    {
        $p = new \Project();
        $p->getFromDB($project->id);

        if (!$p::canUpdate() || !$p->canUpdateItem()) {
            throw new \Exception(__('Not enough rights'));
        }

        $p->update([
            'id' => $project->id,
            'percent_done' => ($project->progress * 100),
            'name' => $project->text
        ]);
        return true;
    }

    public function updateParent($project)
    {
        $p = new \Project();
        $p->getFromDB($project->id);

        if (!$p::canUpdate() || !$p->canUpdateItem()) {
            throw new \Exception(__('Not enough rights'));
        }

        $input = [
            'id' => $project->id,
            'projects_id' => $project->parent
        ];
        $p->update($input);
    }
}
