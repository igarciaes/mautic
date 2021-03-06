<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('mautic_calendar_index', new Route('/calendar', array(
    '_controller' => 'MauticCalendarBundle:Default:index'
)));

$collection->add('mautic_calendar_action', new Route('/calendar/{objectAction}',
    array(
        '_controller' => 'MauticCalendarBundle:Default:execute'
    )
));

return $collection;