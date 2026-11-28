<?php

use Horde\Compress\CompressFactory;
use Horde\Core\Config\ConfigLoader;
use Horde\Compress\Driver\Zip as CompressZip;
use Horde\Compress\Exception as CompressException;
use Horde\Util\Util;

/**
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author  Michael J Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */
/**
 * The Ansel_View_Upload:: class provides a view for handling image uploads.
 *
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author  Michael J Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

class Ansel_View_Upload
{
    /**
     *
     * @var array
     */
    protected $_params;

    /**
     *
     * @var Ansel_Gallery
     */
    protected $_gallery;

    /**
     * Force the older, non-javascript uploader view.
     *
     * @var boolean
     */
    protected $_forceNoScript = false;

    /**
     * Flag for when we already output the carousel code.
     *
     * @var boolean
     */
    protected $_haveCarousel = false;

    /**
     * Initialize the view. Needs the following parameters:
     * <pre>
     *   'browse_button' - Dom id of button to open file system browser.
     *   'target'        - Url of the target page to upload images to.
     *   'drop_target'   - Dom id of the element to receive drag and drop images.
     *   'gallery'       - The gallery id we are uploading to.
     * </pre>
     * @param <type> $params
     */
    public function __construct(array $params = [])
    {
        $this->_params = $params;
        $this->_gallery = $this->_params['gallery'];
        if (!empty($params['forceNoScript'])) {
            $this->_forceNoScript = true;
        }

        Ansel::initJSVariables();

        global $page_output;
        $page_output->addScriptFile('scriptaculous/effects.js', 'horde');
        $page_output->addScriptFile('carousel.js');
    }

    public function run()
    {
        // Check for file upload
        $this->_handleFileUpload();

        global $page_output;
        $page_output->addScriptFile('uploader.js');

        $startText = _("Upload");
        $addText = _("Add Images");
        $header = _("Upload to gallery");
        $returnText = _("View Gallery");
        $subText = _("Add files to the upload queue and click the start button.");
        $sizeError = _("File size error.");
        $typeError = _("File type error.");
        $maxFileSize = $this->_uploadByteLimit();

        $this->_params['target']->add('gallery', $this->_params['gallery']->id);
        $target = json_encode((string) $this->_params['target']);
        $dropTarget = json_encode($this->_params['drop_target']);
        $returnTarget = json_encode((string) $this->_params['return_target']);
        $galleryId = json_encode((string) $this->_gallery->id);
        $text = json_encode([
            'start' => $startText,
            'add' => $addText,
            'header' => $header,
            'returnButton' => $returnText,
            'subheader' => $subText,
            'size' => $sizeError,
            'type' => $typeError,
            'filename' => _("Filename"),
            'status' => _("Status"),
            'sizeLabel' => _("Size"),
        ]);
        $js = <<< EOT
                    var uploader = new AnselUploader({
                        target: {$target},
                        drop_target: {$dropTarget},
                        container: 'anseluploader',
                        container_class: 'uploaderContainer',
                        return_target: {$returnTarget},
                        max_file_size: {$maxFileSize},
                        text: {$text}
                    },
                    {
                        uploadcomplete: function(files) {
                            Ansel.uploadedImages = files;
                            if (Ansel.conf.havetwitter) {
                                var tweet = document.getElementById('twitter');
                                if (tweet) {
                                    tweet.classList.toggle('hidden');
                                }
                            }
                        }
                    });
                    uploader.init();
                    document.getElementById('twitter').addEventListener('click', function() {
                        HordeCore.doAction(
                            'uploadNotification',
                            {
                                s: 'twitter',
                                g: {$galleryId}
                            },
                            {
                                callback: function(r) {
                                    document.getElementById('twitter').style.display = 'none';
                                }
                            }
                        );
                    });
            EOT;

        $js .= $this->_doCarouselSetup();
        $page_output->addInlineScript($js, true);
    }

    /**
     * Handle uploads from non-js browsers
     */
    public function handleLegacy()
    {
        global $conf, $notification, $page_output, $browser;

        $vars = Horde_Variables::getDefaultVariables();
        $form = new Ansel_Form_Upload($vars, _("Upload photos"));

        // Output the carousel JS in case we are here because the user
        // explicitly selected the old uploader.
        $js = $this->_doCarouselSetup();
        if (!empty($js)) {
            $page_output->addInlineScript($js, true);
        }

        if ($form->validate($vars)) {
            $valid = true;
            $uploaded = 0;
            $info = $form->getInfo($vars);

            // Remember the ids of the images we uploaded so we can autogen
            $image_ids = [];
            for ($i = 0; $i <= $conf['image']['num_uploads'] + 1; ++$i) {
                if (empty($info['file' . $i]['file'])) {
                    continue;
                }

                try {
                    $GLOBALS['browser']->wasFileUploaded('file' . $i);
                } catch (Horde_Browser_Exception $e) {
                    if (!empty($info['file' . $i]['error'])) {
                        $notification->push(
                            sprintf(_("There was a problem uploading the photo: %s"), $info['file' . $i]['error']),
                            'horde.error'
                        );
                    } elseif (!filesize($info['file' . $i]['file'])) {
                        $notification->push(
                            _("The uploaded file appears to be empty. It may not exist on your computer."),
                            'horde.error'
                        );
                    }
                    $valid = false;
                    continue;
                }

                // Check for a compressed file.
                if (in_array(
                    $info['file' . $i]['type'],
                    [
                        'x-extension/zip',
                        'application/x-compressed',
                        'application/x-zip-compressed',
                        'application/zip']
                )
                    || Horde_Mime_Magic::filenameToMime($info['file' . $i]['name']) == 'application/zip') {

                    $this->_handleZip($info['file' . $i]['name']);

                } else {
                    // Read in the uploaded data.
                    $data = file_get_contents($info['file' . $i]['file']);

                    // Try and make sure the image is in a recognizeable
                    // format.
                    if (getimagesize($info['file' . $i]['file']) === false) {
                        $notification->push(
                            _("The file you uploaded does not appear to be a valid photo."),
                            'horde.error'
                        );
                        continue;
                    }

                    // Add the image to the gallery
                    $image_data = [
                        'image_filename' => $info['file' . $i]['name'],
                        'image_caption' => $vars->get('image' . $i . '_desc'),
                        'image_type' => $info['file' . $i]['type'],
                        'data' => $data,
                        'tags' => (isset($info['image' . $i . '_tags']) ? explode(',', $info['image' . $i . '_tags']) : [])];
                    try {
                        $image_ids[] = $this->_gallery->addImage(
                            $image_data,
                            (bool) $vars->get('image' . $i . '_default')
                        );
                        ++$uploaded;
                    } catch (Ansel_Exception $e) {
                        $notification->push(
                            sprintf(_("There was a problem saving the photo: %s"), $e->getMessage()),
                            'horde.error'
                        );
                        $valid = false;
                    }
                }
            }

            // Try to autogenerate some views and tell the user what happened.
            if ($uploaded) {
                $qtask = new Ansel_Queue_ProcessThumbs($image_ids);
                $queue = $GLOBALS['injector']->getInstance('Horde_Queue_Storage');
                $queue->add($qtask);

                // postupload hook if needed
                try {
                    /**
                     * ARCHITECTURE VIOLATION: Using deprecated Horde::callHook()
                     * @deprecated Use $GLOBALS['injector']->getInstance('Horde_Core_Hooks')->callHook() instead
                     * @see Horde_Deprecated::callHook()
                     */
Horde::callHook('postupload', [$image_ids], 'ansel');
                } catch (Horde_Exception_HookNotSet $e) {
                }

                $notification->push(sprintf(ngettext("%d photo was uploaded.", "%d photos were uploaded.", $uploaded), $uploaded), 'horde.success');
            } elseif ($vars->get('submitbutton') != _("Cancel")) {
                $notification->push(_("You did not select any photos to upload."), 'horde.error');
            }

            if ($valid) {
                // Return to the gallery view.
                Ansel::getUrlFor(
                    'view',
                    [
                        'gallery' => $this->_gallery->id,
                        'slug' => $this->_gallery->get('slug'),
                        'view' => 'Gallery',
                        'page' => $page],
                    true
                )->redirect();
                exit;
            }
        }

        Horde::startBuffer();
        include ANSEL_TEMPLATES . '/image/upload.inc';

        return ($this->_forceNoScript ? '' : '<noscript>') . Horde::endBuffer() . ($this->_forceNoScript ? '' : '</noscript>');
    }

    /**
     * Return javascript needed to initialize the carousel.
     *
     * @return string  The javascript code.
     */
    protected function _doCarouselSetup()
    {
        if ($this->_haveCarousel) {
            return '';
        }

        $this->_haveCarousel = true;
        $previewUrl = Horde::url('img/upload_preview.php')
            ->add('gallery', $this->_gallery->id);

        $js = <<<EOT
                       Ajax.Response.prototype._getHeaderJSON = function() {
                        var nbElements = {$this->_gallery->countImages()};
                        var from = this.request.parameters.from;
                        var to   = Math.min(nbElements, this.request.parameters.to);
                        return {html: this.responseText, from: from, to: to, more: to != nbElements};
                    }

                    function runCarousel() {
                        updateCarouselSize();
                        carousel = new UI.Ajax.Carousel("horizontal_carousel", { url: "{$previewUrl->toString(true)}", elementSize: 115 })
                            .observe("request:started", function() {
                                $('spinner').show().morph("opacity:0.8", {duration:0.5});
                            })
                            .observe("request:ended", function() {
                                $('spinner').morph("opacity:0", {duration:0.5, afterFinish: function(obj) { obj.element.hide(); }});
                            });
                    }
                    function resized() {
                        updateCarouselSize();
                        if (carousel) {
                            carousel.updateSize();
                        }
                    }
                    function updateCarouselSize() {
                        var dim = $('anseluploader').getDimensions();
                        $("horizontal_carousel").style.width = dim.width + "px";
                        $$("#horizontal_carousel .container").first().style.width =  (dim.width - 50) + "px";
                    }

                    Event.observe(window, 'resize', resized);
                    carousel = null;
                    runCarousel();
            EOT;

        return $js;
    }

    /**
     * Checks for a file uploaded via the asynchronous uploader.
     * Handle if found and send the server json response.
     */
    protected function _handleFileUpload()
    {
        $filename = Util::getFormData('name');
        if (!$filename) {
            return;
        }

        $requestType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
        if ($requestType === '' || $requestType === 'application/octet-stream') {
            $source = 'php://input';
        } elseif (strpos($requestType, 'multipart') !== false) {
            $uploadError = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $source = $_FILES['file']['tmp_name'] ?? '';
            if ($uploadError !== UPLOAD_ERR_OK || $source === '') {
                $this->_sendUploadJson('400', _("The uploaded file could not be read."));
            }
        } else {
            $this->_sendUploadJson('400', _("The uploaded file could not be read."));
        }

        [$temp, $out] = $this->_copyUploadToTemp($source);
        $data = stream_get_contents($out);
        fclose($out);

        $magicDb = $GLOBALS['injector']->get(ConfigLoader::class)->load('horde')->get('mime.magic_db');
        $type = $this->_detectUploadMime($temp, $filename, is_string($magicDb) ? $magicDb : null);
        $nameType = Horde_Mime_Magic::filenameToMime($filename);
        $zipTypes = [
            'x-extension/zip',
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
        ];

        $image_ids = [];
        if (in_array($type, $zipTypes, true) || in_array($nameType, $zipTypes, true)) {
            try {
                $image_ids = $this->_handleZip($temp);
            } catch (Ansel_Exception $e) {
                $this->_sendUploadJson('400', $e->getMessage());
            }
        } else {
            if (getimagesize($temp) === false) {
                $this->_sendUploadJson('400', _("Not a valid, supported image file."));
            }

            try {
                $image_ids = [$this->_gallery->addImage([
                    'image_filename' => $filename,
                    'image_type' => $type,
                    'data' => $data,
                ])];
            } catch (Ansel_Exception $e) {
                $this->_sendUploadJson('400', _("Not a valid, supported image file."));
            }
        }

        if (!$image_ids) {
            $this->_sendUploadJson('400', _("Not a valid, supported image file."));
        }

        $qtask = new Ansel_Queue_ProcessThumbs($image_ids);
        $queue = $GLOBALS['injector']->getInstance('Horde_Queue_Storage');
        $queue->add($qtask);

        $this->_sendUploadJson('200');
    }

    /**
     * Copy an uploaded byte stream into a temporary file.
     *
     * Sends a JSON error and exits when the stream cannot be opened.
     *
     * @param string $source  php://input or a PHP upload tmp path.
     *
     * @return array{0: string, 1: resource}  Temp path and a handle rewound to the start.
     */
    private function _copyUploadToTemp(string $source): array
    {
        $temp = Util::getTempFile('', true);
        $out = fopen($temp, 'w+');
        if (!$out) {
            $this->_sendUploadJson('500', _("The uploaded file could not be read."));
        }

        $in = fopen($source, 'rb');
        if (!$in) {
            fclose($out);
            $this->_sendUploadJson('500', _("The uploaded file could not be read."));
        }

        stream_copy_to_stream($in, $out);
        rewind($out);
        fclose($in);

        return [$temp, $out];
    }

    /**
     * Detect the MIME type of an uploaded file from its contents, then its name.
     */
    private function _detectUploadMime(string $path, string $filename, ?string $magicDb): string
    {
        $type = Horde_Mime_Magic::analyzeFile($path, $magicDb);
        if (!$type) {
            $type = Horde_Mime_Magic::filenameToMime($filename);
        }

        return $type ?: 'application/octet-stream';
    }

    /**
     * Emit the uploader JSON document and stop the request.
     *
     * An empty error is an object so the client can read error.message.
     */
    private function _sendUploadJson(string $status, ?string $error = null): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'error' => $error === null ? new stdClass() : ['message' => $error],
        ]);
        exit;
    }

    /**
     * Smallest configured PHP limit that applies to one uploaded file, in bytes.
     *
     * A limit of 0 means that setting is unlimited and is ignored.
     */
    private function _uploadByteLimit(): int
    {
        $uploadMax = $this->_iniToBytes((string) ini_get('upload_max_filesize'));
        $postMax = $this->_iniToBytes((string) ini_get('post_max_size'));
        if ($uploadMax > 0 && $postMax > 0) {
            return min($uploadMax, $postMax);
        }

        return max($uploadMax, $postMax);
    }

    /**
     * Convert a php.ini size such as "8M" to bytes.
     */
    private function _iniToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;
        switch ($unit) {
            case 'g':
                $number *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $number *= 1024 * 1024;
                break;
            case 'k':
                $number *= 1024;
                break;
        }

        return (int) $number;
    }

    /**
     * Indicates if the specified filename is a known meta file type.
     *
     * @param string $filename
     *
     * @return boolean
     */
    protected function _isMetaFile($filename)
    {
        /* Skip some known metadata files. */
        $len = strlen($filename);
        if ($filename[$len - 1] == '/'
            || $filename == 'Thumbs.db'
            || strrpos($filename, '.DS_Store') === ($len - 9)
            || strrpos($filename, '.localized') === ($len - 10)
            || strpos($filename, '__MACOSX/') !== false) {

            return true;
        }

        return false;
    }

    /**
     * Handle extracting images from uploaded zip files.
     *
     * @param string $filename  The local path to the zip file.
     *
     * @return array  An array of the resulting image_ids.
     * @throws Ansel_Exception
     */
    private function _handleZip($filename)
    {
        $image_ids = [];

        /* See if we can use the zip extension for reading the file. */
        if (Util::extensionExists('zip')) {
            $zip = new ZipArchive();
            if ($zip->open($filename) !== true) {
                throw new Ansel_Exception(_("Could not open zip archive."));
            }

            /* Iterate the archive */
            for ($z = 0; $z < $zip->numFiles; $z++) {
                $zinfo = $zip->statIndex($z);
                if ($this->_isMetaFile($zinfo['name'])) {
                    continue;
                }

                /* Extract the image */
                $stream = $zip->getStream($zinfo['name']);
                $zdata = stream_get_contents($stream);
                if (!strlen($zdata)) {
                    throw new Ansel_Exception(_("Could not extract image data from zip archive."));
                }

                /* Save the image */
                $image_id = $this->_gallery->addImage(
                    [
                        'image_filename' => $zinfo['name'],
                        'image_caption' => '',
                        'data' => $zdata]
                );
                $image_ids[] = $image_id;
                unset($zdata);
            }
            $zip->close();
            unset($zip);
        } else {
            /* Receiving zip data, but extension not loaded */
            $data = file_get_contents($filename);

            /* Get list of images */
            try {
                $zip = (new CompressFactory())->create('zip');
                $files = $zip->decompress($data, ['action' => CompressZip::ZIP_LIST]);
            } catch (CompressException $e) {
                throw new Ansel_Exception($e);
            }

            /* Iterate the archive */
            foreach ($files as $key => $zinfo) {
                if ($this->_isMetaFile($zinfo['name'])) {
                    continue;
                }

                /* Extract the image */
                try {
                    $result = $zip->decompress(
                        $data,
                        [
                            'action' => CompressZip::ZIP_DATA,
                            'info' => $files,
                            'key' => $key]
                    );
                    $zdata = is_array($result) ? $result['data'] : $result;
                } catch (CompressException $e) {
                    throw new Ansel_Exception($e);
                }

                /* Add the image */
                $image_id = $this->_gallery->addImage(
                    [
                        'image_filename' => $zinfo['name'],
                        'image_caption' => '',
                        'data' => $zdata]
                );
                $image_ids[] = $image_id;
                unset($zdata);
            }
            unset($zip);
            unset($data);
        }

        return $image_ids;
    }

}
