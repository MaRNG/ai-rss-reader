<?php

declare(strict_types=1);

namespace App\Model\Image;


/** Obrázek je nepoužitelný (typ, poškozený soubor); opakovat nemá smysl */
class ImageException extends \RuntimeException
{
}
