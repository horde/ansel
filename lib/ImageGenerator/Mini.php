<?php

/**
 * ImageGenerator to create the mini view.
 *
 * Copyright 2001-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.

 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_ImageGenerator_Mini extends Ansel_ImageGenerator
{
    /**
     *
     * @return Horde_Image
     */
    protected function _create()
    {
        $config = $injector->get('AnselConfig');

        if ($config->get('image.squaremini')) {
            $generator = Ansel_ImageGenerator::factory('SquareThumb', ['width' => min(50, $this->_dimensions['width']),
                'height' => min(50, $this->_dimensions['height']),
                'image' => $this->_image,
                'style' => $this->_params['style']]);
            return $generator->create();
        } else {
            $this->_image->resize(
                min(50, $this->_dimensions['width']),
                min(50, $this->_dimensions['height']),
                true
            );
            if ($config->get('thumbnail.unsharp') && Ansel::isAvailable('Unsharpmask')) {
                try {
                    $this->_image->addEffect(
                        'Unsharpmask',
                        ['radius' => $config->get('thumbnail.radius'),
                            'threshold' => $config->get('thumbnail.threshold'),
                            'amount' => $config->get('thumbnail.amount')]
                    );
                    $this->_image->applyEffects();
                } catch (Horde_Image_Exception $e) {
                    throw new Ansel_Exception($e);
                }
            }

            return $this->_image->getHordeImage();
        }
    }

}
