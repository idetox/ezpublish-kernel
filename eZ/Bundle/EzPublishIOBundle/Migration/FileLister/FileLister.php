<?php

/**
 * File containing the FileLister class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

use eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface;
use eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandler;
use eZ\Publish\SPI\IO\BinaryFile;

abstract class FileLister extends MigrationHandler implements FileListerInterface
{
    protected function getSPIBinaryForMetadata($metadata)
    {
        $spiBinaryFile = new BinaryFile();
        $spiBinaryFile->id = $metadata['path'];
        $spiBinaryFile->size = $metadata['size'];

        if (isset($metadata['timestamp'])) {
            $spiBinaryFile->mtime = new \DateTime('@' . $metadata['timestamp']);
        }

        return $spiBinaryFile;
    }
}
