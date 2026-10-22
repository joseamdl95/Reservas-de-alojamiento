<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigFactory
{
    private static ?Environment $twig = null;

    public static function getTwig(): Environment
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/../templates');
            self::$twig = new Environment($loader, [
                'cache' => false,
                'debug' => true,
            ]);
        }

        return self::$twig;
    }
}
