<?php

/**
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Ansel
 */

/**
 * Adds tables for the Sqlng share driver.
 *
 * Copyright 2011-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Ansel
 */
class AnselUpgradeSqlng extends Horde_Db_Migration_Base
{
    /**
     * Upgrade.
     */
    public function up()
    {
        $t = $this->createTable('ansel_sharesng', ['autoincrementKey' => 'share_id']);
        $t->column('share_name', 'string', ['limit' => 255, 'null' => false]);
        $t->column('share_owner', 'string', ['limit' => 255]);
        $t->column('share_flags', 'integer', ['default' => 0, 'null' => false]);
        $t->column('share_parents', 'text');
        $t->column('perm_creator_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_creator_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_creator_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_creator_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_default_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_default_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_default_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_default_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_guest_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_guest_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_guest_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_guest_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
        $t->column('attribute_name', 'string', ['limit' => 255, 'null' => false]);
        $t->column('attribute_desc', 'string', ['limit' => 255]);
        $t->column('attribute_default', 'integer');
        $t->column('attribute_default_type', 'string', ['limit' => 6]);
        $t->column('attribute_default_prettythumb', 'text');
        $t->column('attribute_style', 'text');
        $t->column('attribute_last_modified', 'integer');
        $t->column('attribute_date_created', 'integer');
        $t->column('attribute_images', 'integer', ['null' => false, 'default' => 0]);
        $t->column('attribute_has_subgalleries', 'integer', ['null' => false, 'default' => 0]);
        $t->column('attribute_slug', 'string', ['limit' => 255]);
        $t->column('attribute_age', 'integer', ['null' => false, 'default' => 0]);
        $t->column('attribute_download', 'string', ['limit' => 255]);
        $t->column('attribute_passwd', 'string', ['limit' => 255]);
        $t->column('attribute_faces', 'integer', ['null' => false, 'default' => 0]);
        $t->column('attribute_view_mode', 'string', ['limit' => 255, 'default' => 'Normal', 'null' => false]);
        $t->end();

        $this->addIndex('ansel_sharesng', ['share_name']);
        $this->addIndex('ansel_sharesng', ['share_owner']);
        $this->addIndex('ansel_sharesng', ['perm_creator_' . Horde_Perms::SHOW]);
        $this->addIndex('ansel_sharesng', ['perm_creator_' . Horde_Perms::READ]);
        $this->addIndex('ansel_sharesng', ['perm_creator_' . Horde_Perms::EDIT]);
        $this->addIndex('ansel_sharesng', ['perm_creator_' . Horde_Perms::DELETE]);
        $this->addIndex('ansel_sharesng', ['perm_default_' . Horde_Perms::SHOW]);
        $this->addIndex('ansel_sharesng', ['perm_default_' . Horde_Perms::READ]);
        $this->addIndex('ansel_sharesng', ['perm_default_' . Horde_Perms::EDIT]);
        $this->addIndex('ansel_sharesng', ['perm_default_' . Horde_Perms::DELETE]);
        $this->addIndex('ansel_sharesng', ['perm_guest_' . Horde_Perms::SHOW]);
        $this->addIndex('ansel_sharesng', ['perm_guest_' . Horde_Perms::READ]);
        $this->addIndex('ansel_sharesng', ['perm_guest_' . Horde_Perms::EDIT]);
        $this->addIndex('ansel_sharesng', ['perm_guest_' . Horde_Perms::DELETE]);

        $t = $this->createTable('ansel_sharesng_groups', ['autoincrementKey' => false]);
        $t->column('share_id', 'integer', ['null' => false]);
        $t->column('group_uid', 'string', ['limit' => 255, 'null' => false]);
        $t->column('perm_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
        $t->end();

        $this->addIndex('ansel_sharesng_groups', ['share_id']);
        $this->addIndex('ansel_sharesng_groups', ['group_uid']);
        $this->addIndex('ansel_sharesng_groups', ['perm_' . Horde_Perms::SHOW]);
        $this->addIndex('ansel_sharesng_groups', ['perm_' . Horde_Perms::READ]);
        $this->addIndex('ansel_sharesng_groups', ['perm_' . Horde_Perms::EDIT]);
        $this->addIndex('ansel_sharesng_groups', ['perm_' . Horde_Perms::DELETE]);

        $t = $this->createTable('ansel_sharesng_users', ['autoincrementKey' => false]);
        $t->column('share_id', 'integer', ['null' => false]);
        $t->column('user_uid', 'string', ['limit' => 255, 'null' => false]);
        $t->column('perm_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
        $t->column('perm_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
        $t->end();

        $this->addIndex('ansel_sharesng_users', ['share_id']);
        $this->addIndex('ansel_sharesng_users', ['user_uid']);
        $this->addIndex('ansel_sharesng_users', ['perm_' . Horde_Perms::SHOW]);
        $this->addIndex('ansel_sharesng_users', ['perm_' . Horde_Perms::READ]);
        $this->addIndex('ansel_sharesng_users', ['perm_' . Horde_Perms::EDIT]);
        $this->addIndex('ansel_sharesng_users', ['perm_' . Horde_Perms::DELETE]);

        $this->dataUp();
    }

    /**
     * Downgrade
     */
    public function down()
    {
        $this->dropTable('ansel_sharesng');
        $this->dropTable('ansel_sharesng_groups');
        $this->dropTable('ansel_sharesng_users');
    }

    public function dataUp()
    {
        $whos = ['creator', 'default', 'guest'];
        $perms = [Horde_Perms::SHOW,
            Horde_Perms::READ,
            Horde_Perms::EDIT,
            Horde_Perms::DELETE];

        $sql = 'INSERT INTO ansel_sharesng (share_id, share_owner, share_parents, share_name, '
            . 'share_flags, attribute_name, attribute_desc, attribute_default, '
            . 'attribute_default_type, attribute_default_prettythumb, attribute_style, '
            . 'attribute_last_modified, attribute_date_created, attribute_images, '
            . 'attribute_has_subgalleries, attribute_slug, attribute_age, '
            . 'attribute_download, attribute_passwd, attribute_faces, attribute_view_mode';

        $count = 0;
        foreach ($whos as $who) {
            foreach ($perms as $perm) {
                $sql .= ', perm_' . $who . '_' . $perm;
                $count++;
            }
        }
        $sql .= ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?' . str_repeat(', ?', $count) . ')';

        foreach ($this->select('SELECT * FROM ansel_shares') as $share) {
            $values = [$share['share_id'],
                $share['share_owner'],
                $share['share_parents'],
                $share['share_name'],
                $share['share_flags'],
                $share['attribute_name'],
                $share['attribute_desc'],
                $share['attribute_default'],
                $share['attribute_default_type'],
                $share['attribute_default_prettythumb'],
                $share['attribute_style'],
                $share['attribute_last_modified'],
                $share['attribute_date_created'],
                $share['attribute_images'],
                $share['attribute_has_subgalleries'],
                $share['attribute_slug'],
                $share['attribute_age'],
                $share['attribute_download'],
                $share['attribute_passwd'],
                $share['attribute_faces'],
                $share['attribute_view_mode'],
            ];
            foreach ($whos as $who) {
                foreach ($perms as $perm) {
                    $values[] = (bool) ($share['perm_' . $who] & $perm);
                }
            }
            $this->insert($sql, $values, null, 'share_id', $share['share_id']);
        }

        foreach (['user', 'group'] as $what) {
            $sql = 'INSERT INTO ansel_sharesng_' . $what . 's (share_id, ' . $what . '_uid';
            $count = 0;
            foreach ($perms as $perm) {
                $sql .= ', perm_' . $perm;
                $count++;
            }
            $sql .= ') VALUES (?, ?' . str_repeat(', ?', $count) . ')';

            foreach ($this->select('SELECT * FROM ansel_shares_' . $what . 's') as $share) {
                $values = [$share['share_id'],
                    $share[$what . '_uid']];
                foreach ($perms as $perm) {
                    $values[] = (bool) ($share['perm'] & $perm);
                }
                $this->insert($sql, $values);
            }
        }
    }
}
