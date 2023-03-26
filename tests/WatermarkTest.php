<?php

namespace Pampapay\PhpWatermark\Tests;

use DirectoryIterator;
use Imagick;
use Pampapay\PhpWatermark\Image;
use Pampapay\PhpWatermark\Watermark;
use PHPUnit\Framework\TestCase;

class WatermarkTest extends TestCase
{
    private static string $outputDir;

    public static function tearDownAfterClass(): void
    {
        foreach (new DirectoryIterator(self::$outputDir) as $fileInfo) {
            if(!$fileInfo->isDot() && '.gitignore' !== $fileInfo->getBasename()) {
                 unlink($fileInfo->getPathname());
            }
        }
    }

    protected function setUp(): void
    {
        self::$outputDir = realpath(sprintf('%s/output/', __DIR__));
    }

    public function test_load_image_and_save_a_copy(): void
    {
        $imagesDir = realpath(sprintf('%s/images/', __DIR__));
        $filename = '/pexels-steve-johnson-1000366.jpg';

        $image = new Image($filename, $imagesDir);

        $image->saveCopy($filename, self::$outputDir);

        $this->assertFileExists(sprintf('%s/%s', self::$outputDir, $filename));
    }

    public function test_create_a_watermark_image_from_text_with_black_background(): void
    {
        $watermark = new Watermark('This is a sample watermark');
        $file = sprintf('%s/%s', self::$outputDir, 'sample_black.jpg');
        $image = $watermark
            ->generate(0.1)
            ->getImage();

        $image->writeImage($file);

        $this->assertFileExists($file);

        $image = new Imagick($file);

        $this->assertEquals('srgb(0,0,0)', $image->getImagePixelColor($image->getImageWidth(), $image->getImageHeight())->getColorAsString());
    }

    public function test_create_a_watermark_image_from_text_with_white_background(): void
    {
        $watermark = new Watermark('This is a sample watermark');
        $file = sprintf('%s/%s', self::$outputDir, 'sample_white.jpg');
        $image = $watermark
            ->generate(0.6)
            ->getImage();

        $image->writeImage($file);

        $this->assertFileExists($file);

        $image = new Imagick($file);

        $this->assertEquals('srgb(255,255,255)', $image->getImagePixelColor($image->getImageWidth(), $image->getImageHeight())->getColorAsString());
    }

    public function test_create_a_new_image_with_black_watermark_with_fixed_font_size(): void
    {
        $imagesDir = realpath(sprintf('%s/images/', __DIR__));
        $filename = '/pexels-steve-johnson-1000366.jpg';
        $image = new Image($filename, $imagesDir);

        $image
            ->addTextWatermark('Black sample text', 150)
            ->saveCopy($filename, self::$outputDir);

        $this->assertFileExists(
            sprintf('%s/%s', self::$outputDir, $filename)
        );
    }

    public function test_create_a_new_image_with_white_watermark_dynamic_font_size(): void
    {
        $imagesDir = realpath(sprintf('%s/images/', __DIR__));
        $filename = '/pexels-pixabay-87009.jpg';
        $image = new Image($filename, $imagesDir);

        $image
            ->addTextWatermark('White sample text')
            ->saveCopy($filename, self::$outputDir);

        $this->assertFileExists(
            sprintf('%s/%s', self::$outputDir, $filename)
        );
    }

    public function test_create_a_new_image_with_dynamic_font_size_on_small_image(): void
    {
        $imagesDir = realpath(sprintf('%s/images/', __DIR__));
        $filename = '/beach-1361907.jpg';
        $image = new Image($filename, $imagesDir);

        $image
            ->addTextWatermark('White sample text')
            ->saveCopy($filename, self::$outputDir);

        $this->assertFileExists(
            sprintf('%s/%s', self::$outputDir, $filename)
        );
    }
}