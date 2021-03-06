<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController
 *
 * @package Mautic\CampaignBundle\Controller
 */
class AjaxController extends CommonAjaxController
{

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function updateConnectionsAction (Request $request)
    {
        $session        = $this->factory->getSession();
        $campaignId     = InputHelper::clean($request->request->get('campaignId'));
        $connections    = $session->get('mautic.campaign.'.$campaignId.'.events.connections', array());
        $source         = str_replace('CampaignEvent_', '', InputHelper::clean($request->request->get('source')));
        $target         = str_replace('CampaignEvent_', '', InputHelper::clean($request->request->get('target')));
        $ep             = InputHelper::clean($request->request->get('sourceEndpoint'));
        $sourceEndpoint = substr($ep, 0, strrpos($ep, ' '));
        $ep             = InputHelper::clean($request->request->get('targetEndpoint'));
        $targetEndpoint = substr($ep, 0, strrpos($ep, ' '));
        $remove         = InputHelper::int($request->request->get('remove'));

        $connections[$source][$sourceEndpoint][$target] = ($remove) ? '' : $targetEndpoint;
        $session->set('mautic.campaign.' . $campaignId . '.events.connections', $connections);

        //update the source's canvasSettings
        $events = $session->get('mautic.campaign.' . $campaignId . '.events.modified', array());
        if (isset($events[$source])) {
            $events[$source]['canvasSettings']['endpoints'][$sourceEndpoint][$target] = $targetEndpoint;
            $session->set('mautic.campaign.' . $campaignId . '.events.modified', $events);
        }

        $label = '';
        if (!$remove) {
            $event = $events[$target];
            if (isset($event['triggerMode'])) {
                $translator = $this->factory->getTranslator();

                if ($event['triggerMode'] == 'interval') {
                    $label = $translator->trans('mautic.campaign.connection.trigger.interval.label', array(
                        '%number%' => $event['triggerInterval'],
                        '%unit%'   => $translator->transChoice('mautic.campaign.event.intervalunit.' . $event['triggerIntervalUnit'], $event['triggerInterval'])
                    ));
                } elseif ($event['triggerMode'] == 'date') {
                    /** @var \Mautic\CoreBundle\Templating\Helper\DateHelper $dh */
                    $dh    = $this->container->get('mautic.helper.template.date');
                    $label = $translator->trans('mautic.campaign.connection.trigger.date.label', array(
                        '%full%' => $dh->toFull($event['triggerDate']),
                        '%time%' => $dh->toTime($event['triggerDate']),
                        '%date%' => $dh->toShort($event['triggerDate'])
                    ));
                }
            }
        }

        return $this->sendJsonResponse(array('connections' => $connections, 'events' => $events, 'label' => $label));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function updateCoordinatesAction (Request $request)
    {
        $session    = $this->factory->getSession();
        $x          = InputHelper::int($request->request->get('droppedX'));
        $y          = InputHelper::int($request->request->get('droppedY'));
        $campaignId = InputHelper::int($request->request->get('campaignId'));
        $id         = str_replace('CampaignEvent_', '', InputHelper::clean($request->request->get('eventId')));

        //update the source's canvasSettings
        $events = $session->get('mautic.campaign.' . $campaignId . '.events.modified', array());
        if (isset($events[$id])) {
            $events[$id]['canvasSettings']['droppedX'] = $x;
            $events[$id]['canvasSettings']['droppedY'] = $y;
            $session->set('mautic.campaign.' . $campaignId . '.events.modified', $events);
        }

        return $this->sendJsonResponse(array('events' => $events));
    }
}