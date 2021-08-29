<?php

namespace dbx12\yii2MockDatabase;

use RuntimeException;
use Yii;

/**
 * This class mocks the \yii\db\Connection which provides a connection to a database. With this version, you can force
 * failures in your connection.
 *
 * @package dbx12\yii2MockDatabase
 */
class Connection extends \yii\db\Connection
{
    public $commandClass = Command::class;

    public string $flagFile = '@tests/_output/mockConnection.dat';

    protected string $flagPath;
    protected bool $sharingEnabled = false;
    protected int $failTheNextCommands = -1;
    protected int $passTheNextCommands = PHP_INT_MAX;

    public function init(): void
    {
        $this->setupCommandMap();
        $this->prepareFlagPath();
    }

    protected function prepareFlagPath(): void
    {
        $this->flagPath = Yii::getAlias($this->flagFile);
    }

    protected function setupCommandMap(): void
    {
        $newMap       = [];
        $defaultClass = Command::class;
        $exceptions   = [
            'sqlite'  => SQLiteCommand::class,
            'sqlite2' => SQLiteCommand::class,
            'oci'     => OciCommand::class,
        ];
        foreach ($this->commandMap as $driverName => $commandClass) {
            if (array_key_exists($driverName, $exceptions)) {
                $newMap[$driverName] = $exceptions[$driverName];
            } else {
                $newMap[$driverName] = $defaultClass;
            }
        }
        $this->commandMap = $newMap;
    }

    /**
     * Configure the connection to fail the next $count commands. Defaults to the next command alone.
     * After the number of commands you have set were forced to fail, normal behavior resumes.
     * Be careful: Yii might perform commands on its own and expend the number you've set faster than you expected.
     *
     * @param int $count Number of commands to fail
     */
    public function failNextCommand(int $count = 1): void
    {
        $this->failTheNextCommands = $count;
        $this->passTheNextCommands = PHP_INT_MAX;
        $this->write();
    }

    public function passNextCommand(int $count = 1): void
    {
        $this->failTheNextCommands = 0;
        $this->passTheNextCommands = $count;
        $this->write();
    }

    /**
     * All subsequent commands should fail now.
     */
    public function failAlways(): void
    {
        $this->failTheNextCommands = PHP_INT_MAX;
        $this->passTheNextCommands = 0;
        $this->write();
    }

    /**
     * All subsequent commands will pass now.
     */
    public function passAlways(): void
    {
        $this->failTheNextCommands = 0;
        $this->passTheNextCommands = PHP_INT_MAX;
        $this->write();
    }

    public function shouldFail(): bool
    {
        $this->load();
        if ($this->failTheNextCommands > 0) {
            $this->failTheNextCommands--;
            $this->write();
            return true;
        }
        if ($this->passTheNextCommands > 0) {
            $this->passTheNextCommands--;
            $this->write();
            return false;
        }
        return true;
    }

    /**
     * Keeps the current configuration in sync with other database connections using the same flag file.
     */
    public function startSharing(): void
    {
        $this->sharingEnabled = true;
        $this->write();
    }

    /**
     * Stops synchronization with other database connections using the same flag file.
     */
    public function stopSharing(): void
    {
        $this->sharingEnabled = false;
    }

    protected function write(): void
    {
        if (!$this->sharingEnabled) {
            return;
        }
        $data = [
            'failNext' => $this->failTheNextCommands,
            'passNext' => $this->passTheNextCommands,
        ];
        if (file_put_contents($this->flagPath, serialize($data)) === false) {
            //@codeCoverageIgnoreStart
            throw new RuntimeException('Could not share mock database configuration');
            //@codeCoverageIgnoreEnd
        }
    }

    protected function load(): void
    {
        if (!$this->sharingEnabled || !is_readable($this->flagPath)) {
            return;
        }
        $contents = file_get_contents($this->flagPath);
        if (!empty($contents)) {
            $data                      = unserialize($contents, ['allowed_classes' => false]);
            $this->passTheNextCommands = $data['passNext'];
            $this->failTheNextCommands = $data['failNext'];
        }
    }
}
