<?php

/**
 * File containing the ImageFileLister class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger\ImageFileList;
use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory;
use eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface;
use eZ\Publish\Core\IO\IOServiceInterface;
use Iterator;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use LimitIterator;
use Psr\Log\LoggerInterface;

class ImageFileLister extends FileLister implements FileListerInterface
{
    /** @var ImageFileList */
    private $imageFileList;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator */
    private $variationPathGenerator;

    /** @var \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration */
    private $filterConfiguration;

    public function __construct(
        HandlerFactory $metadataHandlerFactory,
        HandlerFactory $binarydataHandlerFactory,
        LoggerInterface $logger = null,
        Iterator $imageFileList,
        IOServiceInterface $ioService,
        VariationPathGenerator $variationPathGenerator,
        FilterConfiguration $filterConfiguration
    ) {
        $this->imageFileList = $imageFileList;
        $this->ioService = $ioService;
        $this->variationPathGenerator = $variationPathGenerator;
        $this->filterConfiguration = $filterConfiguration;

        $this->imageFileList->rewind();

        parent::__construct($metadataHandlerFactory, $binarydataHandlerFactory, $logger);
    }

    public function countFiles()
    {
        return count($this->imageFileList);
    }

    public function loadMetadataList($limit = null, $offset = null)
    {
        $metadataList = [];
        $imageLimitList = new LimitIterator($this->imageFileList, $offset, $limit);
        $aliasNames = array_keys($this->filterConfiguration->all());

        foreach ($imageLimitList as $originalImageId) {
            $metadataList[] = $this->getSPIBinaryForMetadata([
                'path' => $originalImageId,
                'size' => null,
                'timestamp' => null,
            ]);

            foreach ($aliasNames as $aliasName) {
                $variationImageId = $this->variationPathGenerator->getVariationPath($originalImageId, $aliasName);
                if ($this->ioService->exists($variationImageId)) {
                    $metadataList[] = $this->getSPIBinaryForMetadata([
                        'path' => $variationImageId,
                        'size' => null,
                        'timestamp' => null,
                    ]);
                }
            }
        }

        return $metadataList;
    }
}
