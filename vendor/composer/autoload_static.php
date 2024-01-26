<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit47f477d178215b2ecd8369174189573c
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'IS\\PazarYeri\\Trendyol\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'IS\\PazarYeri\\Trendyol\\' => 
        array (
            0 => __DIR__ . '/..' . '/ismail0234/trendyol-php-api/IS/PazarYeri/Trendyol',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit47f477d178215b2ecd8369174189573c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit47f477d178215b2ecd8369174189573c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit47f477d178215b2ecd8369174189573c::$classMap;

        }, null, ClassLoader::class);
    }
}
