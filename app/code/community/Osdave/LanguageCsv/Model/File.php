<?php

/**
 * Description of File
 *
 * @author david
 */
class Osdave_LanguageCsv_Model_File extends Varien_Object
{
    const LANGUAGE_FILE_EXTENSION = 'csv';
    const MODULE_DEFINITION_FILE_EXTENSION = 'xml';

    consT PATTERN_QUOTE_SINGLE = '/__\(\'(.+?)(\'\)|\',)/';
    consT PATTERN_QUOTE_DOUBLE = '/__\("(.+?)("\)|",)/';

    /**
     * file pointer
     *
     * @var resource
     */
    protected $_handler = null;

    /**
     * Load language file info
     *
     * @param string fileName
     * @param string filePath
     * @return Osdave_LanguageCsv_Model_File
     */
    public function load($fileName, $filePath)
    {
        list ($time, $type) = explode("_", substr($fileName, 0, strrpos($fileName, ".")));
        $this->addData(array(
            'id' => $filePath . DS . $fileName,
            'name' => $fileName,
            'path' => $filePath
        ));
//        $this->setType($type);
        return $this;
    }

    public function getFileName()
    {
        return $this->getName() . '.' . self::LANGUAGE_FILE_EXTENSION;
    }

    /**
     * Checks language csv file exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->getPath() . DS . $this->getName());
    }

    /**
     * Print output
     *
     */
    public function output()
    {
        if (!$this->exists()) {
            return ;
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => $this->getPath()));

        $ioAdapter->streamOpen($this->getName(), 'r');
        while ($buffer = $ioAdapter->streamRead()) {
            echo $buffer;
        }
        $ioAdapter->streamClose();
    }

    public function createLanguageCsvFile($languagecsv, $frontendTemplateRootFolder, $adminTemplateRootFolder)
    {
        $languagecsv->open(true);

        //1. get all files from module
        //1.1. get all php files
        $phpFiles = $languagecsv->getModuleFiles();
        $languagecsv->extractStrings($phpFiles, 'php');
        //1.2. get all phtml files
        if (!is_null($frontendTemplateRootFolder) && ($frontendTemplateRootFolder != '')) {
            $frontendPhtmlFiles = $languagecsv->getTemplateFiles($frontendTemplateRootFolder);
            $languagecsv->extractStrings($frontendPhtmlFiles, 'phtml');
        }
        if (!is_null($adminTemplateRootFolder) && ($adminTemplateRootFolder != '')) {
            $adminPhtmlFiles = $languagecsv->getTemplateFiles($adminTemplateRootFolder);
            $languagecsv->extractStrings($adminPhtmlFiles, 'phtml');
        }

        $languagecsv->close();
    }

    public function getModuleFiles()
    {
        $codePool = $this->_getCodePool();
        $module = str_replace('_', DS, $this->getName());
        $pathToDirectory = Mage::getBaseDir('code') . DS . $codePool . DS . $module . DS;

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathToDirectory));

        return $objects;
    }

    public function getTemplateFiles($rootFolder)
    {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootFolder));

        return $objects;
    }

    public function extractStrings($objects, $type = null)
    {
        foreach($objects as $file => $object) {
            if (!is_null($type) && (substr(strrchr($file,'.'),1) == $type) ||
                    is_null($type)) {
                //parse file
                $file_handle = fopen($file, "r");
                while (!feof($file_handle)) {
                    $line = fgets($file_handle);
                    preg_match_all(self::PATTERN_QUOTE_SINGLE, $line, $singleQuoteMatches);
                    if (sizeof($singleQuoteMatches[1])) {
                        foreach ($singleQuoteMatches[1] as $string) {
                            $string = str_replace('"', '""', $string);
                            $this->write('"' . $string . '","' . $string . "\"\r\n");
                        }
                    }
                    preg_match_all(self::PATTERN_QUOTE_DOUBLE, $line, $doubleQuoteMatches);
                    if (sizeof($doubleQuoteMatches[1])) {
                        foreach ($doubleQuoteMatches[1] as $string) {
                            $this->write('"' . $string . '","' . $string . "\"\r\n");
                        }
                    }
                }
                fclose($file_handle);
            }
        }

    }

    private function _getCodePool()
    {
        $moduleName = $this->getName();
        $moduleXmlFile = simplexml_load_file(Mage::getBaseDir('etc') .'/modules/' . $moduleName . '.' . self::MODULE_DEFINITION_FILE_EXTENSION);
        $codePool = $moduleXmlFile->modules->$moduleName->codePool;

        return (string)$codePool;
    }

    public function open($write = false)
    {
        if (is_null($this->getPath())) {
            Mage::exception('Osdave_LanguageCsv', Mage::helper('languagecsv')->__('Language CSV file path was not specified.'));
        }

        $ioAdapter = new Varien_Io_File();
        try {
            $path = $ioAdapter->getCleanPath($this->getPath());
            $ioAdapter->checkAndCreateFolder($path);
            $filePath = $path . DS . $this->getFileName();
        } catch (Exception $e) {
            Mage::exception('Osdave_LanguageCsv', $e->getMessage());
        }

        if ($write && $ioAdapter->fileExists($filePath)) {
            $ioAdapter->rm($filePath);
        }
        if (!$write && !$ioAdapter->fileExists($filePath)) {
            Mage::exception('Osdave_LanguageCsv', Mage::helper('languagecsv')->__('Language CSV file "%s" does not exist.', $this->getFileName()));
        }

        $mode = $write ? 'a+' : 'r';

        try {
            $this->_handler = fopen($filePath, $mode);
        } catch (Exception $e) {
            Mage::exception('Osdave_LanguageCsv', Mage::helper('languagecsv')->__('Language CSV file "%s" cannot be read from or written to.', $this->getFileName()));
        }

        return $this;
    }

    /**
     * Write to file
     *
     * @param string $string
     * @return Osdave_LanguageCsv_Model_File
     */
    public function write($string)
    {
        if (is_null($this->_handler)) {
            Mage::exception('Osdave_LanguageCsv', Mage::helper('languagecsv')->__('Language csv file handler was unspecified.'));
        }

        try {
            gzwrite($this->_handler, $string);
        } catch (Exception $e) {
            Mage::exception('Osdave_LanguageCsv', Mage::helper('languagecsv')->__('An error occurred while writing to the language csv file "%s".', $this->getFileName()));
        }

        return $this;
    }

    /**
     * Close open Language CSV file
     *
     * @return Osdave_LanguageCsv_Model_File
     */
    public function close()
    {
        @fclose($this->_handler);
        $this->_handler = null;

        return $this;
    }

    /**
     * Delete language csv file
     *
     * @throws Osdave_LanguageCsv_Exception
     */
    public function deleteFile()
    {
        if (!$this->exists()) {
            Mage::throwException(Mage::helper('languagecsv')->__("CSV Language file does not exist."));
        }

        $ioProxy = new Varien_Io_File();
        $ioProxy->open(array('path'=>$this->getPath()));
        $ioProxy->rm($this->getName());
        return $this;
    }

}