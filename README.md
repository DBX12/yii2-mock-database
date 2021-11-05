# Mock database failures for Yii 2

This library provides adapted connections and command classes for Yii2 database connections. With it, you can force
database failures in your tests to check how your application behaves when the database is not reachable for example.

## Setup and usage

1. Install with `composer require --dev dbx12/yii2-mock-database`
2. Define the `\dbx12\yii2MockDatabase\Connection` class as your database connection class.
3. During the tests, call `Yii::$app->db->failAlways()` to fail all subsequent commands. Make sure you've loaded your
   fixtures before doing that.
4. Return to normal behavior by calling `Yii::$app->db->passAlways()`
5. Optionally register the CleanupExtension in your phpunit.xml to get rid of old `mockDatabase.dat` files (see below)

## Other methods

- `failNextCommand($count = 1)` allows you to only **fail** a given number of commands and then return to "normal behavior"
- `passNextCommand($count = 1)` allows you to only **pass** a given number of commands and then return to "always fail"

## Cleanup Extension

The cleanup extension deletes all files given to it in the first argument after the last test. You can use it to clean
your output directory and prevent old files from affecting future test runs. All paths must be either absolute or
relative to the working directory of phpunit. A configuration example is in the code block below, it assumes your working
directory is the project root (you call phpunit as `vendor/bin/phpunit`).

```xml
<extension class="dbx12\yii2MockDatabase\CleanupExtension">
   <arguments>
      <array>
         <!-- The three lines below specify that the file tests/_output/mockDatabase.dat is to be deleted after the last test -->
         <element key="0">
            <string>tests/_output/mockDatabase.dat</string>
         </element>
         <!-- You could add another <element> tag like above here to delete additional files -->
      </array>
   </arguments>
</extension>
```
