<?php

/**
 * Description of Javascript
 *
 * @author david
 */
class Osdave_LanguageCsv_Block_Javascript extends Mage_Adminhtml_Block_Abstract
{
    const TEMPLATE_DEPTH = 2;// the template folders are in level 2 (0 based): base/default/template

    private $_modulesFolders = array();

    public function getCreateFileUrl()
    {
	return $this->getUrl('*/*/create');
    }

    public function getModulesList()
    {
	$modules = array_keys((array) Mage::getConfig()->getNode('modules')->children());

	sort($modules);

	$modulesList = array();
	foreach ($modules as $moduleName) {
	    if ($moduleName === 'Mage_Adminhtml') {
		continue;
	    }
	    $modulesList[] = $moduleName;
	}

	return $modulesList;
    }

    public function getTemplateFolders($section)
    {
	$folders = array();
	$rootFolderPath = Mage::getBaseDir('design') . DS . $section . DS;
	$rootFolder = opendir($rootFolderPath);

	while (false !== ($package = readdir($rootFolder))) {//app/design/adminhtml_or_frontend/package/
	    if (is_dir($rootFolderPath . $package) && !$this->_linuxDir($package)) {
		$packageFolder = opendir($rootFolderPath . $package . DS);

		while (false !== ($theme = readdir($packageFolder))) {//app/design/adminhtml_or_frontend/package/theme/
		    if (is_dir($rootFolderPath . $package . DS . $theme . DS) && !$this->_linuxDir($theme)) {
			$containerFolderPath = $rootFolderPath . $package . DS . $theme . DS . 'template' . DS;
			if (is_dir($containerFolderPath)) {//app/design/adminhtml_or_frontend/package/theme/template/
			    $this->_modulesFolders = array();//reinitiate
			    $folders[] = $this->_getModulesFolders($containerFolderPath, $package, $theme);
			    sort($folders[sizeof($folders)-1][$package . DS . $theme]);
			}
		    }
		}

	    }
	}

//	Zend_Debug::dump($folders, 'debug');

	return $folders;
    }

    private function _getModulesFolders($containerFolderPath, $package, $theme)
    {
	$maxDepth = Mage::getStoreConfig('dev/languagecsv/tree_depth');

	$themeFolder = opendir($containerFolderPath);
	while (false !== ($folder = readdir($themeFolder))) {//app/design/adminhtml_or_frontend/package/theme/template/module
	    if (is_dir($containerFolderPath . $folder) && !$this->_linuxDir($folder)) {
		$actualPath = $containerFolderPath . $folder;
		$lengthToThemeFolder = strpos($actualPath, $package . DS . $theme . DS) + strlen($package . DS . $theme . DS);
		$pathFromThemeFolder = substr($actualPath, $lengthToThemeFolder);
		$currentDepth = substr_count($pathFromThemeFolder, DS);

		$this->_modulesFolders[$package . DS . $theme][] = array(
		    'value' => $actualPath,
		    'label' => $folder,
		    'depth' => $currentDepth
		);

		if ($currentDepth < $maxDepth) {
		    $this->_getModulesFolders($actualPath . DS, $package, $theme);
		}
	    }
	}

	return $this->_modulesFolders;
    }

    private function _linuxDir($package)
    {
	if (($package == '.') || ($package == '..')) {
	    return true;
	}

	return false;
    }

}