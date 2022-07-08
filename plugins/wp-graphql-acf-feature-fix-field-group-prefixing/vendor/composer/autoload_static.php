<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5d3b6ccf12f2a55d6ca3051497f869df
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPGraphQL\\ACF\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPGraphQL\\ACF\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WPGraphQL\\ACF\\Acf' => __DIR__ . '/../..' . '/src/Acf.php',
        'WPGraphQL\\ACF\\AcfSettings' => __DIR__ . '/../..' . '/src/AcfSettings.php',
        'WPGraphQL\\ACF\\Config' => __DIR__ . '/../..' . '/src/deprecated-class-config.php',
        'WPGraphQL\\ACF\\Fields\\AcfField' => __DIR__ . '/../..' . '/src/Fields/AcfField.php',
        'WPGraphQL\\ACF\\Fields\\File' => __DIR__ . '/../..' . '/src/Fields/File.php',
        'WPGraphQL\\ACF\\Fields\\FlexibleContent' => __DIR__ . '/../..' . '/src/Fields/FlexibleContent.php',
        'WPGraphQL\\ACF\\Fields\\Gallery' => __DIR__ . '/../..' . '/src/Fields/Gallery.php',
        'WPGraphQL\\ACF\\Fields\\Group' => __DIR__ . '/../..' . '/src/Fields/Group.php',
        'WPGraphQL\\ACF\\Fields\\Image' => __DIR__ . '/../..' . '/src/Fields/Image.php',
        'WPGraphQL\\ACF\\Fields\\PageLink' => __DIR__ . '/../..' . '/src/Fields/PageLink.php',
        'WPGraphQL\\ACF\\Fields\\PostObject' => __DIR__ . '/../..' . '/src/Fields/PostObject.php',
        'WPGraphQL\\ACF\\Fields\\Relationship' => __DIR__ . '/../..' . '/src/Fields/Relationship.php',
        'WPGraphQL\\ACF\\Fields\\Repeater' => __DIR__ . '/../..' . '/src/Fields/Repeater.php',
        'WPGraphQL\\ACF\\Fields\\Select' => __DIR__ . '/../..' . '/src/Fields/Select.php',
        'WPGraphQL\\ACF\\Fields\\Taxonomy' => __DIR__ . '/../..' . '/src/Fields/Taxonomy.php',
        'WPGraphQL\\ACF\\Fields\\User' => __DIR__ . '/../..' . '/src/Fields/User.php',
        'WPGraphQL\\ACF\\LocationRules' => __DIR__ . '/../..' . '/src/LocationRules.php',
        'WPGraphQL\\ACF\\Registry' => __DIR__ . '/../..' . '/src/Registry.php',
        'WPGraphQL\\ACF\\Types\\InterfaceType\\AcfFieldGroupInterface' => __DIR__ . '/../..' . '/src/Types/InterfaceType/AcfFieldGroupInterface.php',
        'WPGraphQL\\ACF\\Types\\ObjectType\\AcfFieldGroupConfig' => __DIR__ . '/../..' . '/src/Types/ObjectType/AcfFieldGroupConfig.php',
        'WPGraphQL\\ACF\\Types\\ObjectType\\AcfGoogleMap' => __DIR__ . '/../..' . '/src/Types/ObjectType/AcfGoogleMap.php',
        'WPGraphQL\\ACF\\Types\\ObjectType\\AcfLink' => __DIR__ . '/../..' . '/src/Types/ObjectType/AcfLink.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5d3b6ccf12f2a55d6ca3051497f869df::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5d3b6ccf12f2a55d6ca3051497f869df::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5d3b6ccf12f2a55d6ca3051497f869df::$classMap;

        }, null, ClassLoader::class);
    }
}