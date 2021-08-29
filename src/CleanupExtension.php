<?php

namespace dbx12\yii2MockDatabase;

use PHPUnit\Runner\AfterLastTestHook;

/**
 * Cleans the test environment by deleting the dat files after test execution.
 * Files must be specified either by absolute paths or paths relative to the working dir phpunit is called from (usually
 * your project root).
 *
 * Configuration example in phpunit.xml to delete the file tests/_output/mockDatabase.dat
 *
 * ```xml
 *  <extension class="dbx12\yii2MockDatabase\CleanupExtension">
 *      <arguments>
 *          <array>
 *              <element key="0">
 *                  <string>tests/_output/mockDatabase.dat</string>
 *              </element>
 *          </array>
 *      </arguments>
 *  </extension>
 * ```
 *
 */
class CleanupExtension implements AfterLastTestHook
{

    protected array $flagFilePaths;

    public function __construct(array $paths = [])
    {
        $this->flagFilePaths = $paths;
    }

    public function executeAfterLastTest(): void
    {
        foreach ($this->flagFilePaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
