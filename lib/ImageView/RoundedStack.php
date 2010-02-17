 <?php
/**
 * ImageView to create the gallery image stacks.
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 *
 */
class Ansel_ImageView_RoundedStack extends Ansel_ImageView
 {
    public $need = array('PhotoStack');

    /**
     *
     * @return Horde_Image
     */
    protected function _create()
    {
        $imgobjs = $this->_getStackImages();
        $style = $this->_params['style'];
        $params = array('width' => 100,
                        'height' => 100,
                        'background' => $style['background']);

        $baseImg = Ansel::getImageObject($params);

        try {
            $baseImg->addEffect(
                'PhotoStack',
                array('images' => $imgobjs,
                      'resize_height' => $GLOBALS['conf']['thumbnail']['height'],
                      'padding' => 0,
                      'background' => $style['background'],
                      'type' => 'rounded'));

            $baseImg->applyEffects();
            $baseImg->resize($GLOBALS['conf']['thumbnail']['width'],
                             $GLOBALS['conf']['thumbnail']['height']);
        } catch (Horde_Image_Exception $e) {
            return false;
        }

        return $baseImg;
    }

}
