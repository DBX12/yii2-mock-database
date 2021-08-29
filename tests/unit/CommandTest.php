<?php

namespace dbx12\yii2MockDatabase\tests\unit;

use dbx12\yii2MockDatabase\Command;
use dbx12\yii2MockDatabase\Connection;
use dbx12\yii2MockDatabase\tests\ReflectionHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \dbx12\yii2MockDatabase\Command
 */
class CommandTest extends TestCase
{
    use ReflectionHelpers;

    protected Connection|MockObject $connection;
    protected Command $command;

    /**
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(Connection::class)->onlyMethods(['shouldFail'])->getMock();
        $this->command = new Command();
        $this->setInaccessibleProperty($this->command, 'db', $this->connection);
    }

    /**
     * @covers ::execute
     * @throws \yii\db\Exception
     */
    public function testExecute(): void
    {
        $this->connection->expects($this->once())->method('shouldFail')->willReturn(true);
        $this->command->execute();
    }
}
