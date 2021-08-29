<?php

namespace dbx12\yii2MockDatabase;

/**
 * Class Command
 * This class mocks the command class for SQLite databases called by \dbx12\yii2MockDatabase\Connection.
 *
 * @package dbx12\yii2MockDatabase
 * @property \dbx12\yii2MockDatabase\Connection $db
 */
class SQLiteCommand extends \yii\db\sqlite\Command
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
