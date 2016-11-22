<?php

/**
 * File containing the LegacyStorageFileIterator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileIterator;

use eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReaderInterface;
use eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileIteratorInterface;

/**
 * Iterator for entries in legacy's file tables.
 *
 * The returned items are filename of binary/media files (video/87c2bfd00.wmv).
 */
class LegacyStorageFileIterator implements FileIteratorInterface
{
    /** @var mixed Last fetched item. */
    private $item;

    /** @var int Iteration cursor on $statement. */
    private $cursor;

    /**
     * The storage prefix used by legacy, usually the vardir + the 'storage' folder.
     * Example: var/ezdemo_site/storage.
     *
     * @var string
     */
    private $prefix;

    /** @var \eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReaderInterface Used to get file rows. */
    private $rowReader;

    /**
     * @param \eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReaderInterface $rowReader
     * @param string $storageDir Folder, relative to the root, where files are stored. Example: var/ezdemo_site/storage
     * @param string $filesDir Folder where files are stored, within the storage dir. Example: 'original'
     */
    public function __construct(FileRowReaderInterface $rowReader, $storageDir, $filesDir)
    {
        $this->prefix = $storageDir . '/' . $filesDir;
        $this->rowReader = $rowReader;
    }

    public function current()
    {
        return $this->item;
    }

    public function next()
    {
        $this->fetchRow();
    }

    public function key()
    {
        return $this->cursor;
    }

    public function valid()
    {
        return ($this->cursor < $this->count());
    }

    public function rewind()
    {
        $this->cursor = -1;
        $this->rowReader->init();
        $this->fetchRow();
    }

    public function count()
    {
        return $this->rowReader->getCount();
    }

    /**
     * Fetches the next item from the resultset, moves the cursor forward, and removes the prefix from the file id.
     */
    private function fetchRow()
    {
        ++$this->cursor;
        $fileId = $this->rowReader->getRow();

        if (substr($fileId, 0, strlen($this->prefix)) == $this->prefix) {
            $fileId = ltrim(substr($fileId, strlen($this->prefix)), '/');
        }

        $this->item = $fileId;
    }
}
