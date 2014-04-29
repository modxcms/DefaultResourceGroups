<?php
$plugins = array();

/* create the plugin object */
$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->set('id',1);
$plugins[0]->set('name','DefaultResourceGroups');
$plugins[0]->set('description','This plugin handles correct setting of show_in_tree parameter. It also inject JS to handle close button in Resource panel.');
$plugins[0]->set('plugincode', getSnippetContent($sources['plugins'] . 'defaultresourcegroups.plugin.php'));
$plugins[0]->set('category', 0);

$events = array();

$e = array(
    'OnDocFormSave',
    'OnResourceSort',
);

foreach ($e as $ev) {
    $events[$ev] = $modx->newObject('modPluginEvent');
    $events[$ev]->fromArray(array(
        'event' => $ev,
        'priority' => 0,
        'propertyset' => 0
    ),'',true,true);
}

if (is_array($events) && !empty($events)) {
    $plugins[0]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in '.count($events).' Plugin Events for Collections.'); flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events for Collections!');
}
unset($events);

return $plugins;