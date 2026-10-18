<?php

/**
 * Report by letter api class
 *
 * @author  Duck <duck@obala.net>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_Report_letter extends Ansel_Report
{
    /**
     * Report
     */
    public function report($message, $users = [])
    {
        $config = $injector->get('AnselConfig');
        if (!empty($users)) {
            // We are sending a report to to the gallery owner
            $admins = [$users];
        } elseif (empty($config->get('report_content.users'))) {
            $admins = $this->getAdmins();
            if (empty($admins)) {
                return true;
            }
        } else {
            $admins = $config->get('report_content.users');
        }

        $title = $this->getTitle();
        $message = $this->getMessage($message);

        return $GLOBALS['registry']->callByPackage(
            'letter',
            'sendMessage',
            [$admins,
                ['title' => $title,
                    'content' => $message]]
        );
    }
}
