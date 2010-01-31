<?php
/**
 * Ansel application API.
 *
 * This file defines Horde's core API interface. Other core Horde libraries
 * can interact with Ansel through this API.
 *
 * Copyright 2004-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Jan Schneider <jan@horde.org>
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Ansel
 */

if (!defined('ANSEL_BASE')) {
    define('ANSEL_BASE', dirname(__FILE__) . '/..');
}

if (!defined('HORDE_BASE')) {
    /* If horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(ANSEL_BASE . '/config/horde.local.php')) {
        include ANSEL_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', ANSEL_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

class Ansel_Application extends Horde_Registry_Application
{
    /**
     * The application's version.
     *
     * @var string
     */
    public $version = 'H4 (2.0-git)';

    /**
     * Initialization function.
     *
     * Global variables defined:
     *   $ansel_db - TODO
     *   $ansel_storage - TODO
     *   $ansel_styles - TODO
     *   $ansel_vfs - TODO
     *   $cache - A Horde_Cache object.
     *
     * @throws Horde_Exception
     */
    protected function _init()
    {
        // Create a cache object if we need it.
        if ($GLOBALS['conf']['ansel_cache']['usecache']) {
            $GLOBALS['cache'] = $GLOBALS['injector']->getInstance('Horde_Cache');
        }

        // Create db, share, and vfs instances.
        $GLOBALS['ansel_db'] = Ansel::getDb();
        if (is_a($GLOBALS['ansel_db'], 'PEAR_Error')) {
            throw new Horde_Exception($GLOBALS['ansel_db']);
        }

        $GLOBALS['ansel_storage'] = new Ansel_Storage();
        $GLOBALS['ansel_vfs'] = Ansel::getVFS();

        // Get list of available styles for this client.
        $GLOBALS['ansel_styles'] = Ansel::getAvailableStyles();
        if ($logger = Horde::getLogger()) {
            $GLOBALS['ansel_vfs']->setLogger($logger, $GLOBALS['conf']['log']['priority']);
        }
    }

    /**
     * Returns a list of available permissions.
     *
     * @return array  An array describing all available permissions.
     */
    public function perms()
    {
        $perms = array();
        $perms['tree']['ansel']['admin'] = false;
        $perms['title']['ansel:admin'] = _("Administrators");

        return $perms;
    }

    /**
     * Special preferences handling on update.
     *
     * @param string $item      The preference name.
     * @param boolean $updated  Set to true if preference was updated.
     *
     * @return boolean  True if preference was updated.
     */
    public function prefsSpecial($item, $updated)
    {
        switch ($item) {
        case 'default_category_select':
            $default_category = Horde_Util::getFormData('default_category_select');
            if (!is_null($default_category)) {
                $GLOBALS['prefs']->setValue('default_category', $default_category);
                return true;
            }
            break;

        case 'default_gallerystyle_select':
            $default_style = Horde_Util::getFormData('default_gallerystyle_select');
            if (!is_null($default_style)) {
                $GLOBALS['prefs']->setValue('default_gallerystyle', $default_style);
                return true;
            }
            break;
        }

        return $updated;
    }

    /**
     * Generate the menu to use on the prefs page.
     *
     * @return Horde_Menu  A Horde_Menu object.
     */
    public function prefsMenu()
    {
        return Ansel::getMenu();
    }

}
