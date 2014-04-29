<?php
/**
 * DefaultResourceGroups
 *
 * DESCRIPTION
 *
 * This plugin that handles default resource groups
 *
 * @package defaultresourcegroups
 */

$corePath = $modx->getOption('defaultresourcegroups.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/defaultresourcegroups/');
/** @var DefaultResourceGroups $defaultresourcegroups */
$defaultResourceGroups = $modx->getService(
    'defaultresourcegroups',
    'DefaultResourceGroups',
    $corePath . 'model/defaultresourcegroups/',
    array(
        'core_path' => $corePath
    )
);

$eventName = $modx->event->name;
if (method_exists($defaultResourceGroups, $eventName)) {
    $eventName = lcfirst($eventName);
    $defaultResourceGroups->$eventName($scriptProperties);
}