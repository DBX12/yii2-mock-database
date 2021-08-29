<?php

namespace dbx12\yii2MockDatabase;

/**
 * Class OciCommand
 * This class mocks the command class for Oracle databases called by \dbx12\yii2MockDatabase\Connection.
 *
 * @package dbx12\yii2MockDatabase
 * @property \dbx12\yii2MockDatabase\Connection $db
 */
class OciCommand extends \yii\db\oci\Command
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
