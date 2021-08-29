<?php

namespace dbx12\yii2MockDatabase\tests\unit;

use dbx12\yii2MockDatabase\Connection;
use dbx12\yii2MockDatabase\tests\ReflectionHelpers;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \dbx12\yii2MockDatabase\Connection
 */
class ConnectionTest extends TestCase
{
    use ReflectionHelpers;

    protected string $datFile = TEST_ROOT . '/_output/mockConnection.dat';

    protected MockBuilder $connMockBuilder;

    protected function setUp(): void
    {
        $this->connMockBuilder = $this
            ->getMockBuilder(Connection::class)
            ->enableOriginalConstructor();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->datFile)) {
            unlink($this->datFile);
        }
    }

    /**
     * @covers ::passAlways
     * @throws \ReflectionException
     */
    public function testPassAlways(): void
    {
        $conn = $this->connMockBuilder->onlyMethods(['write'])->getMock();
        $conn->expects($this->once())->method('write');

        $conn->passAlways();
        $this->assertEquals(0, $this->getInaccessibleProperty($conn, 'failTheNextCommands'), 'Updated value is correct');
        $this->assertEquals(PHP_INT_MAX, $this->getInaccessibleProperty($conn, 'passTheNextCommands'), 'Updated value is correct');
    }

    /**
     * @covers ::failAlways
     * @throws \ReflectionException
     */
    public function testFailAlways(): void
    {
        $conn = $this->connMockBuilder->onlyMethods(['write'])->getMock();
        $conn->expects($this->once())->method('write');

        $conn->failAlways();
        $this->assertEquals(PHP_INT_MAX, $this->getInaccessibleProperty($conn, 'failTheNextCommands'), 'Updated value is correct');
        $this->assertEquals(0, $this->getInaccessibleProperty($conn, 'passTheNextCommands'), 'Updated value is correct');
    }

    /**
     * @covers ::passNextCommand
     * @throws \ReflectionException
     */
    public function testPassNextCommand(): void
    {
        $conn = $this->connMockBuilder->onlyMethods(['write'])->getMock();
        $conn->expects($this->once())->method('write');

        $conn->passNextCommand(42);
        $this->assertEquals(0, $this->getInaccessibleProperty($conn, 'failTheNextCommands'), 'Updated value is correct');
        $this->assertEquals(42, $this->getInaccessibleProperty($conn, 'passTheNextCommands'), 'Updated value is correct');
    }

    /**
     * @covers ::shouldFail
     */
    public function testShouldFail(): void
    {
        $conn = $this->connMockBuilder->onlyMethods(['write'])->getMock();
        $conn->expects($this->exactly(7))->method('write');

        $this->assertFalse($conn->shouldFail(), 'Don\'t fail by default');
        $conn->failNextCommand();
        $this->assertTrue($conn->shouldFail(), 'Fail the next command');
        $this->assertFalse($conn->shouldFail(), 'Succeed after one failure');
        $this->assertFalse($conn->shouldFail(), 'Succeed again after one failure');

        $conn->passNextCommand();
        $this->assertFalse($conn->shouldFail(), 'Succeed one command');
        $this->assertTrue($conn->shouldFail(), 'Fail after one success');
        $this->assertTrue($conn->shouldFail(), 'Fail again after one success');
    }

    /**
     * @covers ::failNextCommand
     * @throws \ReflectionException
     */
    public function testFailNextCommand(): void
    {
        $conn = $this->connMockBuilder->onlyMethods(['write'])->getMock();
        $conn->expects($this->once())->method('write');

        $conn->failNextCommand(42);
        $this->assertEquals(42, $this->getInaccessibleProperty($conn, 'failTheNextCommands'), 'Updated value is correct');
        $this->assertEquals(PHP_INT_MAX, $this->getInaccessibleProperty($conn, 'passTheNextCommands'), 'Updated value is correct');
    }

    /**
     * @covers ::setupCommandMap
     * @throws \ReflectionException
     * @noinspection ClassConstantCanBeUsedInspection
     */
    public function testSetupCommandMap(): void
    {
        $conn = $this->connMockBuilder->disableOriginalConstructor()->getMock();
        $this->invokeMethod($conn, 'setupCommandMap');
        $expected = [
            'cubrid'  => 'dbx12\yii2MockDatabase\Command',
            'dblib'   => 'dbx12\yii2MockDatabase\Command',
            'mssql'   => 'dbx12\yii2MockDatabase\Command',
            'mysql'   => 'dbx12\yii2MockDatabase\Command',
            'mysqli'  => 'dbx12\yii2MockDatabase\Command',
            'oci'     => 'dbx12\yii2MockDatabase\OciCommand',
            'pgsql'   => 'dbx12\yii2MockDatabase\Command',
            'sqlite'  => 'dbx12\yii2MockDatabase\SQLiteCommand',
            'sqlite2' => 'dbx12\yii2MockDatabase\SQLiteCommand',
            'sqlsrv'  => 'dbx12\yii2MockDatabase\Command',
        ];
        $this->assertEquals($expected, $conn->commandMap, 'Command map updated correctly');
    }

    /**
     * @covers ::init
     */
    public function testInit(): void
    {
        $conn = $this->connMockBuilder
            ->disableOriginalConstructor()
            ->onlyMethods(['setupCommandMap', 'prepareFlagPath'])
            ->getMock();
        $conn->expects($this->once())->method('setupCommandMap');
        $conn->expects($this->once())->method('prepareFlagPath');
        $conn->init();
    }

    /**
     * @covers ::prepareFlagPath
     */
    public function testPrepareFlagPath(): void
    {
        $conn = $this->connMockBuilder->disableOriginalConstructor()->getMock();
        $this->invokeMethod($conn, 'prepareFlagPath');
        $this->assertEquals($this->datFile, $this->getInaccessibleProperty($conn, 'flagPath'), 'Alias is resolved');
    }

    /**
     * @covers ::startSharing
     * @throws \ReflectionException
     */
    public function testStartSharing(): void
    {
        $conn = $this->connMockBuilder->onlyMethods(['write'])->getMock();
        $conn->expects($this->once())->method('write');

        $conn->startSharing();
        $this->assertTrue(
            $this->getInaccessibleProperty($conn, 'sharingEnabled'),
            'sharingEnabled is set to true'
        );
    }

    /**
     * @covers ::stopSharing
     * @throws \ReflectionException
     */
    public function testStopSharing(): void
    {
        $conn = new Connection();

        $conn->stopSharing();
        $this->assertFalse(
            $this->getInaccessibleProperty($conn, 'sharingEnabled'),
            'sharingEnabled is set to false'
        );
    }

    /**
     * @covers ::write
     * @throws \ReflectionException
     */
    public function testWrite(): void
    {
        $conn = new Connection();

        $conn->failNextCommand(10);
        $this->invokeMethod($conn, 'write');
        $this->assertFileDoesNotExist($this->datFile, 'sharingEnabled is false, file should not exist');
        // enable sharing and write again
        $this->setInaccessibleProperty($conn, 'sharingEnabled', true);
        $this->invokeMethod($conn, 'write');
        $this->assertFileExists($this->datFile, 'File exists');
        $expectedContent = serialize(['failNext' => 10, 'passNext' => PHP_INT_MAX,]);
        $actualContent   = file_get_contents($this->datFile);
        $this->assertEquals($expectedContent, $actualContent);
    }

    /**
     * @covers ::load
     * @throws \ReflectionException
     */
    public function testLoad(): void
    {
        $conn = new Connection();
        file_put_contents($this->datFile, serialize(['passNext' => 200, 'failNext' => 200]));

        $this->setInaccessibleProperty($conn, 'passTheNextCommands', 100);
        $this->setInaccessibleProperty($conn, 'failTheNextCommands', 100);
        $this->invokeMethod($conn, 'load');
        $this->assertEquals(100,
            $this->getInaccessibleProperty($conn, 'passTheNextCommands'),
            'sharingEnabled is false, should not overwrite passTheNextCommands'
        );
        $this->assertEquals(100,
            $this->getInaccessibleProperty($conn, 'failTheNextCommands'),
            'sharingEnabled is false, should not overwrite failTheNextCommands'
        );

        // set sharingEnabled to true
        $this->setInaccessibleProperty($conn, 'sharingEnabled', true);
        $this->invokeMethod($conn, 'load');
        $this->assertEquals(200,
            $this->getInaccessibleProperty($conn, 'passTheNextCommands'),
            'sharingEnabled is true, should overwrite passTheNextCommands'
        );
        $this->assertEquals(200,
            $this->getInaccessibleProperty($conn, 'failTheNextCommands'),
            'sharingEnabled is true, should overwrite failTheNextCommands'
        );
    }
}
