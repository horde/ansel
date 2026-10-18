<?php

/**
 * Report using tickets
 *
 * @author  Duck <duck@obala.net>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_Report_tickets extends Ansel_Report
{
    /**
     * Report
     */
    public function report($message)
    {
        $config = $injector->get('AnselConfig');
        $info = array_merge(
            $config->get('report_content.ticket_params'),
            ['summary' => $this->getTitle(),
                'comment' => $message,
                'user_email' => $this->getUserEmail()]
        );

        return $GLOBALS['registry']->call('tickets/addTicket', [$info]);
    }

}
