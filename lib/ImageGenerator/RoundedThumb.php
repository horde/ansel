<?php

/**
 * ImageGenerator to create the prettythumb view (rounded, shadowed thumbnails).
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

use Horde\Ansel\AnselConfig;

class Ansel_ImageGenerator_RoundedThumb extends Ansel_ImageGenerator
{
    public $need = ['RoundCorners', 'DropShadow'];

    public function __construct($params)
    {
        parent::__construct($params);
        $this->title = _("Rounded Corners");
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

        /* Don't bother with these effects for a stack image
         * (which will have a negative gallery_id). */
        if ($this->_image->gallery > 0) {
            if (is_null($this->_style)) {
                $gal = $GLOBALS['injector']->getInstance('Ansel_Storage')->getGallery($this->_image->gallery);
                $styleDef = $gal->getStyle();
            } else {
                $styleDef = $this->_style;
            }

            try {
                /* Apply the effects - continue on error, but be sure to log */
                $this->_image->addEffect('RoundCorners', ['border' => 2,
                    'bordercolor' => '#333']);

                $this->_image->addEffect('DropShadow', ['background' => $styleDef->background,
                    'padding' => 5,
                    'distance' => 5,
                    'fade' => 3]);
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

            return $this->_image->getHordeImage();
        }
    }

}
