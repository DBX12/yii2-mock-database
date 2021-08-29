<?php

namespace dbx12\yii2MockDatabase;

/**
 * Class Command
 * This class mocks the default command class called by \dbx12\yii2MockDatabase\Connection.
 *
 * @package dbx12\yii2MockDatabase
 * @property \dbx12\yii2MockDatabase\Connection $db
 */
class Command extends \yii\db\Command
{
    public function execute()
    {
        if ($this->db->shouldFail()) {
            return false;
        }
        //@codeCoverageIgnoreStart
        return parent::execute();
        //@codeCoverageIgnoreEnd
    }
}
