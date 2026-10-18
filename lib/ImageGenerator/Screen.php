<?php

/**
 * ImageGenerator to create the screen view - image sized for slideshow view.
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_ImageGenerator_Screen extends Ansel_ImageGenerator
{
    /**
     *
     * @return boolean
     */
    protected function _create()
    {
        config = $injector->get('AnselConfig');
        $this->_image->resize(
            min($config->get('screen.width'), $this->_dimensions['width']),
            min($config->get('screen.height'), $this->_dimensions['height']),
            true
        );
        if ($config->get('screen.unsharp') && Ansel::isAvailable('Unsharpmask')) {
            try {
                $this->_image->addEffect(
                    'Unsharpmask',
                    ['radius' => $config->get('screen.radius'),
                        'threshold' => $config->get('screen.threshold'),
                        'amount' => $config->get('screen.amount')]
                );
                $this->_image->applyEffects();
            } catch (Horde_Image $e) {
                throw new Ansel_Exception($e);
            }
        }

        return $this->_image->getHordeImage();
    }

}
