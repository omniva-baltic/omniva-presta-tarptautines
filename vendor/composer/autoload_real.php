<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit6a340c3a0fb20ab345baffb413ba267c
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit6a340c3a0fb20ab345baffb413ba267c', 'loadClassLoader'), true, false);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit6a340c3a0fb20ab345baffb413ba267c', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit6a340c3a0fb20ab345baffb413ba267c::getInitializer($loader));

        $loader->register(false);

        return $loader;
    }
}
