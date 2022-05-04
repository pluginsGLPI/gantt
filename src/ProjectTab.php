<?php

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
