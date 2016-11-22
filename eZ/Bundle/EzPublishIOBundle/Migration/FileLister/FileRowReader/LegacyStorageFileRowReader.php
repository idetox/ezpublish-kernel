<?php

/**
 * File containing the LegacyStorageFileRowReader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReader;

use eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReaderInterface;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

abstract class LegacyStorageFileRowReader implements FileRowReaderInterface
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    private $dbHandler;

    /** @var \PDOStatement */
    private $statement;

    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    public function init()
    {
        $selectQuery = $this->dbHandler->createSelectQuery();
        $selectQuery->select('filename')->from($this->dbHandler->quoteTable($this->getStorageTable()));
        $this->statement = $selectQuery->prepare();
        $this->statement->execute();
    }

    /**
     * Returns the table name to store data in.
     *
     * @return string
     */
    abstract protected function getStorageTable();

    public function getRow()
    {
        return $this->statement->fetchColumn(0);
    }

    public function getCount()
    {
        return $this->statement->rowCount();
    }
}
