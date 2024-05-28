<?php

namespace  AppBundle\LiipFilters\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class OptPostProcessor implements PostProcessorInterface
{
    /**
     * @param BinaryInterface $binary
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     *
     * @see      Implementation taken from Assetic\Filter\optipngFilter
     */
    public function process(BinaryInterface $binary)
    {
        $nodeDir = __DIR__ . '/../../../node/';
        $input = $nodeDir . 'tmp/' . ($name = md5(microtime()) . '.' . $binary->getFormat());

        if ($binary instanceof FileBinaryInterface) {
            copy($binary->getPath(), $input);
        } else {
            file_put_contents($input, $binary->getContent());
        }


        $command  = 'node ' . $nodeDir . 'ImageOptimiser.js'
                . ' -f ' . $input
                . ' -r ' . $nodeDir . 'tmp/'
                . ' -t ' . $name ;

        $process = new Process($command);
        $process->run();

        $result = new Binary(file_get_contents($input), $binary->getMimeType(), $binary->getFormat());

        unlink($input);

        return $result;
    }
}
