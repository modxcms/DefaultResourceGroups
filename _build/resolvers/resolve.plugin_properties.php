<?php

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            /** @var modX $modx */
            $modx =& $object->xpdo;
            /** @var modPlugin $plugin */
            $plugin = $modx->getObject('modPlugin', array('name' => 'DefaultResourceGroups'));
            if ($plugin) {
                $properties = array();

                $properties[] = array(
                    'name' => 'preserveGroups',
                    'value' => '0',
                    'area' => 'settings',
                    'description' => 'defaultresourcegroups.prop.preserveGroups',
                    'lexicon' => 'defaultresourcegroups:default',
                );

                $contexts = $modx->getIterator('modContext', array('key:NOT IN' => array('mgr')));

                /** @var modContext $context */
                foreach ($contexts as $context) {
                    $properties[] = array(
                        'name' => $context->key,
                        'value' => '',
                        'description' => 'defaultresourcegroups.prop.csl',
                        'lexicon' => 'defaultresourcegroups:default',
                        'area' => 'contexts',
                    );
                }

                $plugin->setProperties($properties);
                $plugin->save();
            }

            break;
        case xPDOTransport::ACTION_UPGRADE:
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
return true;