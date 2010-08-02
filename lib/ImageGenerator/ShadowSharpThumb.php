<?php
/**
 * ImageGenerator to create the shadowsharpthumb view (sharp corners, shadowed)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */
class Ansel_ImageGenerator_ShadowSharpThumb extends Ansel_ImageGenerator
{
    public $need = array('DropShadow');

    /**
     *
     * @return boolean
     */
    protected function _create()
    {
        $this->_image->resize(min($GLOBALS['conf']['thumbnail']['width'], $this->_dimensions['width']),
                              min($GLOBALS['conf']['thumbnail']['height'], $this->_dimensions['height']),
                              true);

        /* Don't bother with these effects for a stack image
         * (which will have a negative gallery_id). */
        if ($this->_image->gallery > 0) {
            if (is_null($this->_style)) {
                $gal = $GLOBALS['injector']->getInstance('Ansel_Storage')->getScope()->getGallery($this->_image->gallery);
                $styleDef = $gal->getStyle();
            } else {
                $styleDef = Ansel::getStyleDefinition($this->_style);
            }

            try {
                $this->_image->addEffect('Border', array('bordercolor' => '#333', 'borderwidth' => 1));
                $this->_image->addEffect('DropShadow',
                                         array('background' => $styleDef['background'],
                                               'padding' => 5,
                                               'distance' => 8,
                                               'fade' => 2));
                $this->_image->applyEffects();
            } catch (Horde_Image_Exception $e) {
                throw new Ansel_Exception($e);
            }

            return $this->_image->getHordeImage();
        }
    }

}