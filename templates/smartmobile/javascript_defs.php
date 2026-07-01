<?php

global $prefs, $registry;

$ansel_webroot = $registry->get('webroot');
$horde_webroot = $registry->get('webroot', 'horde');
$style = Ansel::getStyleDefinition('ansel_mobile');

/* Variables used in core javascript files. */
$code['conf'] = [
    'SESSION_ID' => SID,
    'thumbWidth' => ($style->width) ? $style->width : 75,
    'thumbHeight' => ($style->height) ? $style->height : 75,
    'user' => $GLOBALS['registry']->convertUsername($GLOBALS['registry']->getAuth(), false),
    'name' => $registry->get('name'),
];

// List of top level galleries
$gallerylist = $GLOBALS['injector']->getInstance('Ansel_Storage')->listGalleries(['all_levels' => false, 'attributes' => $registry->getAuth()]);
$galleries = [];

foreach ($gallerylist as $gallery) {
    $galleries[] = $gallery->toJson();
}
$code['conf']['galleries'] = $galleries;

/* Gettext strings used in core javascript files. */
$code['text'] = [
    'ajax_error' => _("Error when communicating with the server."),
];
echo $GLOBALS['page_output']->addInlineJsVars([
    'var Ansel' => $code,
], ['top' => true]);
