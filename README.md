# Shopware Migrations Plugin

## How does it work?

```bash
$ php bin/console netcom:migrations
```

## Getting started

1. Install the plugin in Shopware under `custom/plugins/`
2. Activate the plugin in your Shopware backend
3. Navigate to your Shopware root directory and start using the command.

## Available commands

### netcom:migrations:create

Usage:
```bash
$ php bin/console netcom:migrations:create <version> <name>
```

This command will create a new migration class in the `Migrations/` directory.
The migration class is created from `Resources/Stubs/MigrationClass.stub`, in case you want to modify it.

### netcom:migrations:status

Usage:
```bash
$ php bin/console netcom:migrations:status
```

This command shows the status of all migrations.

Example output:
```plain
$ php bin/console netcom:migrations:status

Pending migrations (in order which they would be executed):
 --------- --------- ------------------------------------------- ----------- ------------ 
  status    version   migration                                   startDate   finishDate  
 --------- --------- ------------------------------------------- ----------- ------------ 
  pending   1.0.0     20180201134801_ImportProductionCategories                           
  pending   1.0.0     20180201145401_ImportProductionArticles                             
 --------- --------- ------------------------------------------- ----------- ------------ 

Finished migrations (in order which they have been exectued):
 ---------- --------- ----------------------------------------------- --------------------- --------------------- 
  status     version   migration                                       startDate             finishDate           
 ---------- --------- ----------------------------------------------- --------------------- --------------------- 
  finished   1.0.0     20180131173842_ImportProductionShopwareConfig   2018-02-02 12:41:58   2018-02-02 12:41:58  
 ---------- --------- ----------------------------------------------- --------------------- ---------------------
```

### netcom:migrations:migrate:up

Usage:
```bash
$ php bin/console netcom:migrations:migrate:up [<version>]
```

This command runs through all pending migrations and executes their "up" method.

### netcom:migrations:migrate:down

Usage:
```bash
$ php bin/console netcom:migrations:migrate:down [<rollbackSteps>]
```

This command runs through all pending migrations and executes their "down" method.

## Writing Migrations

The migrations should extend the \NetcomMigrations\Components\Migrations\Migration class and have access to the container. 

Example migration class (filed under `Migrations/1.0.0/20180131173842_AddCustomerAttributeIsTwitterFollower.php`):
```php
<?php

use \NetcomMigrations\Components\Migrations\Migration;

/**
 * Class ImportProductionShopwareConfig
 */
class AddCustomerAttributeIsTwitterFollower20180131173842 extends Migration
{
    const ATTRIBUTE_TABLE = 's_user_attributes';
    const COLUMN_NAME = 'is_twitter_follower';
    
    /** @var \Shopware\Bundle\AttributeBundle\Service\CrudService $attributeCrudService */
    private $attributeCrudService;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->attributeCrudService = $this->getContainer()->get('shopware_attribute.crud_service');
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->getIO()->writeln(
            \sprintf('Creating new attribute "%s" in table "%s"',self::COLUMN_NAME,self::ATTRIBUTE_TABLE)
        );
        
        $this->attributeCrudService->update(self::ATTRIBUTE_TABLE, self::COLUMN_NAME, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->getIO()->writeln(
            \sprintf('Deleting attribute "%s" from table "%s"', self::COLUMN_NAME, self::ATTRIBUTE_TABLE)
        );
        
        $this->attributeCrudService->delete(self::ATTRIBUTE_TABLE, self::COLUMN_NAME);
    }
}
```

## Customizing the migration template

You can find the default migration template in `Resources/Stubs/MigrationClass.stub`. 

New variables have to be defined in `\NetcomMigrations\Command\CreateCommand` where the `\NetcomMigrations\Components\StubGenerator::generate()` method is called.

## ToDo

- Implement a DI container tag that allows other plugins to register a MigrationStructCollector which would support providing migrations in the context of plugins.
- Add CLI option to `MigrateDownCommand` and `MigrateUpCommand` to toggle the maintenance mode while migrations are running.
- Add CLI option to `MigrateDownCommand` and `MigrateUpCommand` for auto-rollback if a migration fails.

## Contributing

Feel free to fork and send pull requests!

## Licence

This project uses the [GNU General Public License v3.0](LICENCE.md).