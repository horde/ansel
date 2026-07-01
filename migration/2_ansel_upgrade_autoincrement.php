<?php

/**
 * Upgrade for autoincrement
 *
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author   Michael J. Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Ansel
 */
class AnselUpgradeAutoIncrement extends Horde_Db_Migration_Base
{
    public function up()
    {
        $this->changeColumn('ansel_images', 'image_id', 'autoincrementKey');
        if (in_array('ansel_images_seq', $this->tables())) {
            $this->dropTable('ansel_images_seq');
        }
        $this->changeColumn('ansel_faces', 'face_id', 'autoincrementKey');
        if (in_array('ansel_faces_seq', $this->tables())) {
            $this->dropTable('ansel_faces_seq');
        }
        $this->changeColumn('ansel_shares', 'share_id', 'autoincrementKey');
        if (in_array('ansel_shares_seq', $this->tables())) {
            $this->dropTable('ansel_shares_seq');
        }
        $this->changeColumn('ansel_tags', 'tag_id', 'autoincrementKey');
        if (in_array('ansel_tags_seq', $this->tables())) {
            $this->dropTable('ansel_tags_seq');
        }
    }

    public function down()
    {
        $tableList = $this->tables();

        $this->changeColumn('ansel_images', 'image_id', 'integer', ['null' => false, 'autoincrement' => false]);
        $this->changeColumn('ansel_faces', 'face_id', 'integer', ['null' => false, 'autoincrement' => false]);
        $this->changeColumn('ansel_shares', 'share_id', 'integer', ['null' => false, 'autoincrement' => false]);

        if (in_array('ansel_tags', $tableList)) {
            $this->changeColumn('ansel_tags', 'tag_id', 'integer', ['null' => false, 'autoincrement' => false]);
        }
    }

}
