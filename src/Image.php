<?php

namespace Pampapay\PhpWatermark;

use Imagick;
use Pampapay\PhpWatermark\Exception\FileNotFoundException;
use function PHPUnit\Framework\isNull;

class Image
{
    private Imagick $data;

    /**
     * @throws \ImagickException
     * @throws FileNotFoundException
     */
    public function __construct(string $filename, string $directory = null)
    {
        if(null === $directory) {
            $directory = realpath(__DIR__);
        }

        $file = sprintf('%s/%s', $directory, $filename);

        if(!file_exists($file)) {
            throw new FileNotFoundException(sprintf('The file \'%s\' does not exists.', $file));
        }

        $this->data = new Imagick($file);
    }

    /**
     * @throws \ImagickException
     */
    public function addTextWatermark(string $text, float $fontSize = null): self
    {
        if(is_null($fontSize)) {
            $fontSize = $this->data->getImageResolution()['y'];
        }

        $watermark = new Watermark($text, $fontSize);

        $pixelIterator = $this->data->getPixelRegionIterator(0, 0, $watermark->getWidth(), $watermark->getHeight());
        $luminosity = 0;
        $iterator = 0;

        foreach ($pixelIterator as $row) {
            foreach ($row as $pixel) {
                $hsl = $pixel->getHSL();
                $luminosity += $hsl['luminosity'];
                $iterator++;
            }
        }

        $luminosity = $luminosity / $iterator;

        $watermark->generate($luminosity);

        $this->data->compositeImage($watermark->getImage(), Imagick::COMPOSITE_OVER, 0, 0);

        return $this;
    }

    /**
     * @throws \ImagickException
     */
    public function saveCopy(string $filename, bool|string $outputDir): void
    {
        $file = sprintf('%s/%s', $outputDir, $filename);

        $this->data->writeImage($file);
    }
}