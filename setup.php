<?php

define('PLUGIN_GANTT_VERSION', '0.1.0');

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

    $PLUGIN_HOOKS[Hooks::MENU_LINKS]['gantt'] = [
        'GlpiPlugin\Gantt\ProjectTab',
        'menuLinks'
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

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_gantt_check_prerequisites()
{

    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_gantt_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'gantt');
    }
    return false;
}
