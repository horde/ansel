<?php

/**
 * ImageGenerator to create the thumb view (plain, resized thumbnails).
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_ImageGenerator_Thumb extends Ansel_ImageGenerator
{
    public function __construct($params)
    {
        parent::__construct($params);
        $this->title = _("Basic Thumbnails");
    }

    /**
     *
     * @return Horde_Image
     */
    protected function _create()
    {
        $config = $injector->get('AnselConfig');
        $this->_image->resize(
            min($config->get('thumbnail.width'), $this->_dimensions['width']),
            min($config->get('thumbnail.height'), $this->_dimensions['height']),
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
