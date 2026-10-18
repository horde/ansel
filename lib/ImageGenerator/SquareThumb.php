<?php

/**
 * ImageGenerator to create a square thumbnail.
 *
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_ImageGenerator_SquareThumb extends Ansel_ImageGenerator
{
    public function __construct($params)
    {
        parent::__construct($params);
        $this->title = _("Square Thumbnails");
        if (empty($this->_params['width'])) {
            $this->_params['width'] = $this->_style->width;
        }
        if (empty($this->_params['height'])) {
            $this->_params['height'] = $this->_style->height;
        }
    }

    /**
     *
     * @return Horde_Image
     */
    protected function _create()
    {
        $config = $injector->get('AnselConfig');

        // Take the largest requested dimension
        if (empty($this->_params['width'])) {
            $size = min($config->get('thumbnail.height'), $config->get('thumbnail.width'));
        } else {
            $size = min($this->_params['width'], $this->_params['height']);
        }

        // Use smartcrop algorithm if we have it, otherwise a plain center crop.
        if (Ansel::isAvailable('SmartCrop') && $config->get('image.smartcrop')) {
            $this->_image->addEffect('SmartCrop', ['width' => $size, 'height' => $size]);
        } else {
            $this->_image->addEffect('CenterCrop', ['width' => $size, 'height' => $size]);
        }
        $this->_image->applyEffects();

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
