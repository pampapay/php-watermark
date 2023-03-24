<?php

namespace Pampapay\PhpWatermark;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class Watermark
{
    private Imagick $data;

    private string $font;

    private int $width;

    private int $height;

    private ImagickDraw $draw;

    private Imagick $temporal;

    private string $text;

    public function __construct(string $text, float $fontSize = 20, ?string $font = null)
    {
        if(is_null($font)) {
            $font = sprintf('%s/fonts/Roboto-Regular.ttf', realpath(__DIR__));
        }

        $this->font = $font;
        $this->text = $text;

        $this->draw = new ImagickDraw();
        $this->temporal = new Imagick();

        $this->draw->setGravity(Imagick::GRAVITY_CENTER);
        $this->draw->setFont($this->font);
        $this->draw->setFontSize($fontSize);
        
        $metrics = $this->temporal->queryFontMetrics($this->draw, $this->text);

        $this->width = intval( $metrics["textWidth"] + 15 );
        $this->height = intval( $metrics["textHeight"] + 15 );
    }

    public function getImage(): Imagick
    {
        return $this->data;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function generate(float $luminosity): self
    {
        $textColor = new ImagickPixel('white');
        $shadowColor = new ImagickPixel('black');

        if($luminosity > 0.5) {
            $textColor = new ImagickPixel('black');
            $shadowColor = new ImagickPixel('white');
        }

        $this->draw->setFillColor($textColor);

        $this->temporal->newImage( $this->width, $this->height, new ImagickPixel( "transparent" ));
        $this->temporal->setImageFormat('png');
        $this->temporal->annotateImage($this->draw, 5, 5, 0, $this->text);
        
        $this->data = $this->temporal->clone();
        $this->data->setImageBackgroundColor($shadowColor);
        $this->data->shadowImage(80, 2, 2, 2);
        $this->data->compositeImage($this->temporal, Imagick::COMPOSITE_OVER, 0, 0);

        return $this;
    }
}