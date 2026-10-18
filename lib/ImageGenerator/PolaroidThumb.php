<?php

/**
 * ImageGenerator to create the shadowsharpthumb view (sharp corners, shadowed)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_ImageGenerator_PolaroidThumb extends Ansel_ImageGenerator
{
    public $need = ['PolaroidImage'];

    public function __construct($params)
    {
        parent::__construct($params);
        $this->title = _("Polaroids");
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

        /* Don't bother with these effects for a custom gallery key image
           (which will have a negative gallery_id). */
        if ($this->_image->gallery > 0) {
            if (is_null($this->_style)) {
                $gal = $GLOBALS['injector']->getInstance('Ansel_Storage')->getGallery($this->_image->gallery);
                $styleDef = $gal->getStyle();
            } else {
                $styleDef = $this->_style;
            }
            try {
                $this->_image->addEffect(
                    'PolaroidImage',
                    ['background' => $styleDef->background,
                        'padding' => 5]
                );
                if ($config->get('thumbnail.unsharp') && Ansel::isAvailable('Unsharpmask')) {
                    $this->_image->addEffect(
                        'Unsharpmask',
                        ['radius' => $config->get('thumbnail.radius'),
                            'threshold' => $config->get('thumbnail.threshold'),
                            'amount' => $config->get('thumbnail.amount')]
                    );
                }
                $this->_image->applyEffects();
            } catch (Horde_Image_Exception $e) {
                throw new Ansel_Exception($e);
            }

            return true;
        }
    }

}
