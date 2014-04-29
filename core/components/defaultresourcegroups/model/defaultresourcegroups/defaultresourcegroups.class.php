<?php

/**
 * The main Default Resource Groups service class.
 *
 * @package defaultresourcegroups
 */
class DefaultResourceGroups {
    public $modx = null;
    public $namespace = 'defaultresourcegroups';
    public $cache = null;
    public $options = array();

    public function __construct(modX &$modx, array $options = array()) {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, 'defaultresourcegroups');

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/defaultresourcegroups/');

        /* loads some default paths for easier management */
        $this->options = array_merge(array(
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'pluginsPath' => $corePath . 'elements/plugins/',
        ), $options);

        $this->modx->addPackage('defaultresourcegroups', $this->getOption('modelPath'));
        $this->modx->lexicon->load('defaultresourcegroups:default');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null) {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    public function explodeAndClean($array, $delimiter = ',') {
        $array = explode($delimiter, $array);     // Explode fields to array
        $array = array_map('trim', $array);       // Trim array's values
        $array = array_keys(array_flip($array));  // Remove duplicate fields
        $array = array_filter($array);            // Remove empty values from array

        return $array;
    }

    public function onDocFormSave($scriptProperties) {
        /** @var modResource $resource */
        $resource = $scriptProperties['resource'];
        $resourceGroups = $this->modx->getOption($resource->context_key, $scriptProperties, '');
        $resourceGroups = $this->explodeAndClean($resourceGroups);

        if (empty($resourceGroups)) return;

        if ($scriptProperties['mode'] == 'upd') {
            $preserveGroups = (int) $this->modx->getOption('preserveGroups', $scriptProperties, 0);

            if ($preserveGroups == 1) {
                $defaults = $scriptProperties;
                unset($defaults['resource'], $defaults['preserveGroups'], $defaults['mode'], $defaults['id'], $defaults['reloadOnly']);
                $defaults = $this->explodeAndClean(implode(',', $defaults));

                $this->handleUpdate($resource, $defaults, $resourceGroups);
            }

            return;
        }

        $this->handleCreate($resource, $resourceGroups);
    }

    public function onResourceSort($scriptProperties) {
        $preserveGroups = (int) $this->modx->getOption('preserveGroups', $scriptProperties, 0);

        if ($preserveGroups == 1) {
            $defaults = $scriptProperties;
            unset($defaults['nodes'], $defaults['preserveGroups'], $defaults['contexts'], $defaults['contextsAffected'], $defaults['modifiedNodes']);
            $defaults = $this->explodeAndClean(implode(',', $defaults));

            $modifiedNodes = $scriptProperties['modifiedNodes'];

            /** @var modResource $modifiedNode */
            foreach ($modifiedNodes as $modifiedNode) {
                $resourceGroups = $this->modx->getOption($modifiedNode->context_key, $scriptProperties, '');
                $resourceGroups = $this->explodeAndClean($resourceGroups);

                $this->handleUpdate($modifiedNode, $defaults, $resourceGroups);
            }
        }

        return;
    }

    /**
     * Assign resource that is being created to resource groups
     *
     * @param modResource $resource
     * @param array $resourceGroups
     */
    public function handleCreate($resource, $resourceGroups) {
        foreach ($resourceGroups as $resourceGroup) {
            $resource->joinGroup($resourceGroup);
        }
    }

    /**
     * @param modResource $resource
     * @param array $defaults
     * @param array $resourceGroups
     */
    public function handleUpdate($resource, $defaults, $resourceGroups) {
        $defaults = array_flip($defaults);

        foreach ($resourceGroups as $resourceGroup) {
            $resource->joinGroup($resourceGroup);

            if (isset($defaults[$resourceGroup])) {
                unset($defaults[$resourceGroup]);
            }
        }

        $defaults = array_keys($defaults);

        foreach ($defaults as $resourceGroup) {
            $resource->leaveGroup($resourceGroup);
        }
    }
}