<?php

/**
 * Description of Collection
 *
 * @author david
 */
class Osdave_LanguageCsv_Model_File_Collection extends Varien_Data_Collection_Filesystem
{
    /**
     * Folder, where all language files are stored are stored
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Set collection specific parameters and make sure language files folder will exist
     */
    public function __construct()
    {
        parent::__construct();

        $this->_baseDir = Mage::getBaseDir('var') . DS . 'languagecsv';

        // check for valid base dir
        $ioProxy = new Varien_Io_File();
        $ioProxy->mkdir($this->_baseDir);
        if (!is_file($this->_baseDir . DS . '.htaccess')) {
            $ioProxy->open(array('path' => $this->_baseDir));
            $ioProxy->write('.htaccess', 'deny from all', 0644);
        }

        // set collection specific params
        $this
            ->setOrder('name', self::SORT_ORDER_ASC)
            ->addTargetDir($this->_baseDir)
            ->setFilesFilter('/^[a-zA-Z0-9\-\_]+\.' . preg_quote(Osdave_LanguageCsv_Model_File::LANGUAGE_FILE_EXTENSION, '/') . '$/')
            ->setCollectRecursively(false)
        ;
    }

    /**
     * Get language files-specific data from model for each row
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        foreach (Mage::getSingleton('languagecsv/file')->load($row['basename'], $this->_baseDir)
            ->getData() as $key => $value) {
            $row[$key] = $value;
        }
        $row['size'] = filesize($filename);
        return $row;
    }
}