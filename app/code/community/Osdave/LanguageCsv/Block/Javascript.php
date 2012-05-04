<?php

/**
 * Description of Javascript
 *
 * @author david
 */
class Osdave_LanguageCsv_Block_Javascript extends Mage_Adminhtml_Block_Abstract
{
    const TEMPLATE_DEPTH = 2;// the template folders are in level 2 (0 based): base/default/template

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
	$maxDepth = Mage::getStoreConfig('dev/languagecsv/tree_depth') + self::TEMPLATE_DEPTH;
	$rootFolderPath = Mage::getBaseDir('design') . DS . $section . DS;
	$rootFolder = opendir($rootFolderPath);
	$folders = array();

//	$fileSPLObjects =  new RecursiveIteratorIterator(
//                new RecursiveDirectoryIterator($rootFolderPath),
//                RecursiveIteratorIterator::SELF_FIRST
//            );
//	foreach( $fileSPLObjects as $fullFileName => $fileSPLObject ) {
//	    print $fullFileName . " " . $fileSPLObject->getFilename() . "<br>";
//	}
//	die;
//
//	$directoriesTree = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootFolderPath), true);
//	foreach ($directoriesTree as $directory) {
//	    if (is_dir($directory) && !$this->_linuxDir($directory->getFileName()) && ($directoriesTree->getDepth() <= $maxDepth)) {
//		if ($directoriesTree->getDepth() == 0) {
//		    $optgroupBase = $directory->getFileName();
//		} elseif ($directoriesTree->getDepth() == 1) {
//		    $optgroup = $directory->getFileName();
//		} elseif ($directoriesTree->getDepth() > self::TEMPLATE_DEPTH) {
//		    $label = str_replace($rootFolderPath, '', $directory->getFileName());
//		    if (($directoriesTree->getDepth() - self::TEMPLATE_DEPTH) > 1) {
//			$label = '|-' . $label;
//		    }
//		    $folders[$optgroupBase . DS . $optgroup][] = array(
//			'value' => $directory,
//			'label' => $label,
//			'class' => ($directoriesTree->getDepth() - self::TEMPLATE_DEPTH)
//		    );
//		}
//	    }
//	}
//	asort($folders);
//
//	return $folders;

	while (false !== ($package = readdir($rootFolder))) {//app/design
	    if (is_dir($rootFolderPath . $package) && !$this->_linuxDir($package)) {
		$packageFolder = opendir($rootFolderPath . $package . DS);
		while (false !== ($theme = readdir($packageFolder))) {//app/design/adminhtml_or_frontend
		    if (is_dir($rootFolderPath . $package . DS . $theme . DS) && !$this->_linuxDir($theme)) {
			if (is_dir($rootFolderPath . $package . DS . $theme . DS . 'template' . DS)) {
			    $themeFolder = opendir($rootFolderPath . $package . DS . $theme . DS . 'template' . DS);
			    while (false !== ($folder = readdir($themeFolder))) {//app/design/adminhtml_or_frontend/package/theme/template
				if (is_dir($rootFolderPath . $package . DS . $theme . DS . 'template' . DS . $folder) && !$this->_linuxDir($folder)) {
				    $folders[$package . DS . $theme][] = array(
					'value' => $rootFolderPath . $package . DS . $theme . DS . 'template' . DS . $folder,
					'label' => $folder
				    );
				}
			    }
			    sort($folders[$package . DS . $theme]);
			}
		    }
		}
	    }
	}
	ksort($folders);

	return $folders;
    }

    private function _linuxDir($package)
    {
	if (($package == '.') || ($package == '..')) {
	    return true;
	}

	return false;
    }

}