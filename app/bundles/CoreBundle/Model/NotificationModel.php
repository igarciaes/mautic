<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Model;

use Mautic\CoreBundle\Entity\Notification;
use Mautic\UserBundle\Entity\User;

/**
 * Class NotificationModel
 */
class NotificationModel extends FormModel
{

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\CoreBundle\Entity\AuditLogRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticCoreBundle:Notification');
    }

    /**
     * Write a notification
     *
     * @param        $message    Message of the notification
     * @param        $type       Optional $type to ID the source of the notification
     * @param        $isRead     Add unread indicator
     * @param        $header     Header for message
     * @param string $iconClass  Font Awesome CSS class for the icon (e.g. fa-eye)
     * @param DateTime $datetime Date the item was created
     * @param null   $user       User object; defaults to current user
     */
    public function addNotification($message, $type = null, $isRead = true, $header = null, $iconClass = null, \DateTime $datetime = null, User $user = null)
    {
        if ($user === null) {
            $user = $this->factory->getUser();
        }

        if ($user === null || !$user->getId()) {
            //ensure notifications aren't written for non users
            return;
        }

        $notification = new Notification();
        $notification->setType($type);
        $notification->setIsRead($isRead);
        $notification->setHeader($header);
        $notification->setMessage($message);
        $notification->setIconClass($iconClass);
        $notification->setUser($user);
        if ($datetime == null) {
            $datetime = new \DateTime();
        }
        $notification->setDateAdded($datetime);
        $this->saveEntity($notification);
    }

    /**
     * @param null $key
     */
    public function getNotifications($afterId = null, $key = null)
    {
        $filter = array(
            'force' => array(
                array(
                    'column' => 'n.user',
                    'expr'   => 'eq',
                    'value'  => $this->factory->getUser()
                )
            )
        );

        if ($key != null) {
            $filter['force'][] = array(
                'column' => 'n.key',
                'expr'   => 'eq',
                'value'  => $key
            );
        }

        if ($afterId != null) {
            $filter['force'][] = array(
                'column' => 'n.id',
                'expr'   => 'gt',
                'value'  => (int) $afterId
            );
        }

        $args = array(
            'filter' => $filter,
            'ignore_paginator' => true,
            'hydration_mode' => 'HYDRATE_ARRAY'
        );

        return $this->getEntities($args);
    }

    /**
     * Mark notifications read for a user
     */
    public function markAllRead()
    {
        $this->getRepository()->markAllReadForUser($this->factory->getUser()->getId());
    }

    /**
     * Clears a notification for a user
     *
     * @param $id Notification to clear; will clear all if empty
     */
    public function clearNotification($id)
    {
        $this->getRepository()->clearNotificationsForUser($this->factory->getUser()->getId(), $id);
    }

    /**
     * Get content for notifications
     *
     * @return array
     */
    public function getNotificationContent($afterId = null)
    {
        if ($this->factory->getUser()->isGuest) {
            return array(array(), false, '');
        }

        $notifications = $this->getNotifications($afterId);

        $showNewIndicator = false;

        //determine if the new message indicator should be shown
        foreach ($notifications as $n) {
            if (!$n['isRead']) {
                $showNewIndicator = true;
                break;
            }
        }

        // Check for updates
        $updateMessage = '';
        if (!$this->factory->getParameter('security.disableUpdates') && $this->factory->getUser()->isAdmin()) {
            $session = $this->factory->getSession();

            //check to see when we last checked for an update
            $lastChecked = $session->get('mautic.update.checked', 0);

            if (time() - $lastChecked > 3600) {
                $session->set('mautic.update.checked', time());

                /** @var \Mautic\CoreBundle\Helper\UpdateHelper $updateHelper */
                $updateHelper = $this->factory->getHelper('update');
                $updateData   = $updateHelper->fetchData();

                // If the version key is set, we have an update
                if (isset($updateData['version'])) {
                    $translator    = $this->factory->getTranslator();
                    $updateMessage = $translator->trans($updateData['message'], array('%version%' => $updateData['version'], '%announcement%' => $updateData['announcement']));
                }
            }
        }

        return array($notifications, $showNewIndicator, $updateMessage);
    }
}
