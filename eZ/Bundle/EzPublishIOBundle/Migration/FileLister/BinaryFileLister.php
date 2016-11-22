<?php

/**
 * File containing the BinaryFileLister class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory;
use eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface;
use Iterator;
use LimitIterator;
use Psr\Log\LoggerInterface;

class BinaryFileLister extends FileLister implements FileListerInterface
{
    /** @var \eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileIteratorInterface */
    private $fileList;

    public function __construct(
        HandlerFactory $metadataHandlerFactory,
        HandlerFactory $binarydataHandlerFactory,
        LoggerInterface $logger = null,
        Iterator $fileList
    ) {
        $this->fileList = $fileList;
        $this->fileList->rewind();

        parent::__construct($metadataHandlerFactory, $binarydataHandlerFactory, $logger);
    }

    public function countFiles()
    {
        return count($this->fileList);
    }

    public function loadMetadataList($limit = null, $offset = null)
    {
        $metadataList = [];
        $fileLimitList = new LimitIterator($this->fileList, $offset, $limit);

        foreach ($fileLimitList as $fileId) {
            $metadataList[] = $this->getSPIBinaryForMetadata([
                'path' => $fileId,
                'size' => null,
                'timestamp' => null,
            ]);
        }

        return $metadataList;
    }
}
