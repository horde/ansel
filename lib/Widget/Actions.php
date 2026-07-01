<?php

/**
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Michael J Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */
/**
 * Ansel_Widget_Actions:: class to wrap the display of gallery actions
 *
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Michael J Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */
class Ansel_Widget_Actions extends Ansel_Widget_Base
{
    /**
     * @var string
     */
    protected $_supported_views = ['Gallery'];

    /**
     *
     * @see ansel/lib/Widget/Ansel_Widget_Base#html()
     */
    public function html()
    {
        global $injector, $registry;

        $view = $injector->getInstance('Horde_View');
        $view->addTemplatePath(ANSEL_TEMPLATES . '/widgets');
        $view->title = _("Gallery Actions");
        $view->background = $this->_style->background;
        $view->toggle_url = Horde::selfUrl(true, true)
            ->add('actionID', 'show_actions')
            ->link(
                [
                    'id' => 'gallery-actions-toggle',
                    'class' => ($GLOBALS['prefs']->getValue('show_actions') ? 'hide' : 'show'),
                ]
            );

        $id = $this->_view->gallery->id;
        $galleryurl = Horde::url('gallery.php')->add('gallery', $id);

        // Slideshow
        if (empty($this->_params['hide_slideshow'])
            && $this->_view->gallery->hasFeature('slideshow')
            && $this->_view->gallery->countImages()) {

            // Slideshow link
            if (!empty($this->_params['slideshow_link'])) {
                $view->slideshow_url = str_replace(
                    ['%i', '%g'],
                    [array_pop($this->_view->gallery->listImages(0, 1)), $id],
                    urldecode($this->_params['slideshow_link'])
                );
            } else {
                // Get any date info the gallery has
                $date = $this->_view->gallery->getDate();
                $imgs = $this->_view->gallery->listImages(0, 1);
                $view->slideshow_url = Horde::url('view.php')
                    ->add(array_merge(
                        ['gallery' => $id,
                            'image' => array_pop($imgs),
                            'view' => 'Slideshow'],
                        $date
                    ));
            }
        }

        // Upload and new subgallery Urls
        if ($this->_view->gallery->hasFeature('upload')
            && $this->_view->gallery->hasPermission($registry->getAuth(), Horde_Perms::EDIT)) {

            $view->uploadurl_link = Horde::url('img/upload.php')->add(
                [
                    'gallery' => $id,
                    'page' => !empty($this->_view->_params['page']) ? $this->_view->_params['page'] : 0]
            )->link(['class' => 'widget']);

            if ($this->_view->gallery->hasFeature('subgalleries')) {
                $view->subgallery_link = $galleryurl->copy()
                    ->add(['actionID' => 'addchild', 'url' => Horde::signUrl(Horde::selfUrl(true, false, true))])
                    ->link(['class' => 'widget']);
            }
        }
        $this->_getGalleryActions($view);
        $injector->getInstance('Horde_Core_Factory_Imple')
            ->create(
                'Ansel_Ajax_Imple_ToggleGalleryActions',
                ['id' => 'gallery-actions-toggle']
            );

        return $view->render('actions');
    }

    /**
     * Helper function for generating the gallery actions selection widget.
     *
     * @param Horde_View $view  The view object.
     */
    protected function _getGalleryActions(&$view)
    {
        global $registry, $conf;

        $id = $this->_view->gallery->id;
        $galleryurl = Horde::url('gallery.php')->add('gallery', $id);
        $selfurl = Horde::selfUrl(true, false, true);
        $view->count = $count = $this->_view->gallery->countImages();

        $date = $this->_view->gallery->getDate();

        // Buid the url parameters to the zip link
        $view_params = [
            'gallery' => $this->_view->gallery->id,
            'view' => 'Gallery',
            'slug' => $this->_view->gallery->get('slug'),
            'page' => (!empty($this->_view->_params['page']) ? $this->_view->_params['page'] : 0)];

        // Append the date information to the parameters if we need it
        $view_params = array_merge($view_params, $date);

        // Bookmark link
        if ($registry->hasMethod('bookmarks/getAddUrl')) {
            $api_params = [
                'url' => Ansel::getUrlFor('view', $view_params, true),
                'title' => $this->_view->gallery->get('name')];

            try {
                $view->bookmark_url = new Horde_Url($registry->bookmarks->getAddUrl($api_params));
            } catch (Horde_Exception $e) {
            }
        }

        // Download as ZIP link
        if (!empty($conf['gallery']['downloadzip'])
            && $this->_view->gallery->canDownload()
            && $count
            && $this->_view->gallery->hasFeature('zipdownload')) {

            $zip_params = array_merge(['actionID' => 'downloadzip'], $date);
            $view->zip_url = $galleryurl->copy()->add($zip_params)->link(['class' => 'widget']);
        }

        // Image upload, subgalleries, captions etc..
        if ($this->_view->gallery->hasPermission($registry->getAuth(), Horde_Perms::EDIT)) {
            $view->hasEdit = true;
            $view->properties_url = $galleryurl->copy()->add(['actionID' => 'modify', 'url' => Horde::signUrl($selfurl)])->link(['class' => 'widget']);
            if ($count) {
                if ($this->_view->gallery->hasFeature('image_captions')) {
                    $params = array_merge(['gallery' => $id], $date);
                    $view->captions_url = Horde::url('gallery/captions.php')->add($params)->link(['class' => 'widget']);
                }
                if ($this->_view->gallery->hasFeature('sort_images')) {
                    $view->sort_url = Horde::url('gallery/sort.php')->add(array_merge(['gallery' => $id], $date))->link(['class' => 'widget']);
                    $view->resetsort_url = Horde::url('gallery/sort.php')->add(array_merge(['gallery' => $id, 'action' => 'Reset'], $date))->link(['class' => 'widget']);
                }
                $view->regenerate_url = $galleryurl->copy()->add(['actionID' => 'generateThumbs'])->link(['class' => 'widget']);
                $view->regenerate_all = $galleryurl->copy()->add(['actionID' => 'deleteCache'])->link(['class' => 'widget']);
                if ($conf['faces']['driver'] && $conf['faces']['driver'] !== 'user' && $this->_view->gallery->hasFeature('faces')) {
                    $view->faces_url = Horde::url('faces/gallery.php')->add(
                        array_merge($date, ['gallery' => $id, 'page' => (!empty($this->_view->_params['page']) ? $this->_view->_params['page'] : 0)])
                    )
                            ->link(['class' => 'widget']);
                }
            }
            if ($this->_view->gallery->hasFeature('stacks')) {
                $view->gendefault_url = $galleryurl->copy()->add(['actionID' => 'generateDefault', 'url' => Horde::signUrl($selfurl)])->link(['class' => 'widget']);
            }
        }

        if ($registry->getAuth()
            && $this->_view->gallery->get('owner') == $registry->getAuth()) {

            $url = new Horde_Url('#');
            $view->perms_link = $url->link(['class' => 'popup widget', 'onclick' => Horde::popupJs(Horde::url('perms.php'), ['params' => ['cid' => $this->_view->gallery->id], 'urlencode' => true]) . 'return false;']);

        } elseif (!empty($conf['report_content']['driver'])
                  && (($conf['report_content']['allow'] == 'authenticated' && $registry->isAuthenticated())
                  || $conf['report_content']['allow'] == 'all')) {

            $view->report_url = Horde::url('report.php')->add('gallery', $id)->link(['class' => 'widget']);
        }

        if ($this->_view->gallery->hasPermission($registry->getAuth(), Horde_Perms::DELETE)) {
            $view->have_delete = true;
            $view->deleteall_url = $galleryurl->copy()->add('actionID', 'empty')->link(['class' => 'widget']);
            $view->deletegallery_url = $galleryurl->copy()->add('actionID', 'delete')->link(['class' => 'widget']);
        }
    }
}
