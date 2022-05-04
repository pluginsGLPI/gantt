<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

$projecttab = new \GlpiPlugin\Gantt\ProjectTab();

Html::header(Project::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "tools", "project");
$projecttab->showForProject();
Html::footer();
