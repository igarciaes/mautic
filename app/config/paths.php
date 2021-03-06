<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$paths = array(
    //customizable
    'themes'       => 'themes',
    'assets'       => 'media',
    'addons'       => 'addons',
    'local_config' => '%kernel.root_dir%/config/local.php',
);

//allow easy overrides of the above
if (file_exists(__DIR__ . '/paths_local.php')) {
    include __DIR__ . '/paths_local.php';
}

//fixed
$paths = array_merge($paths, array(
    //remove /app from the root
    'root'         => substr($root, 0, -4),
    'app'          => 'app',
    'bundles'      => 'app/bundles',
    'vendor'       => 'vendor'
));