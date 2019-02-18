# Tables addon for Cockpit CMS

Manage SQL tables with 1:m and m:n relations in [Cockpit CMS][1].

This addon is in alpha state and I need a few more work days until it can be used in production.

## Features

* automatic detection of all available tables in the database
* automatic detection of foreign key relations
* automatic generation of field schema with basic type detection (boolean, number, text, textarea, date)
  * with basic validation detection (required)
* automatic `LEFT OUTER JOIN` to display 1:m related fields in entries and entry views
* if a m:n relation is detected, an extra field is created with a select field for the related content
* The automatically generated field schema can be adjusted by changing the field settings, like
  * changing text to textarea
  * make a field required, that wasn't setup as nullable in the database
  * change a relation field to a number field to avoid the automatic join
  * remove a m:n extra field, to avoid displaying it in a m:n helper table
  * ...

## Features (enhancement)

* save/delete values to/in related m:n tables - buggy at the moment (2019-02-18)
* split relation-select field automatically, if the related column contains a lot of rows
* user and group rights management
* If you update your database and reset the field schema to auto-generate the relations, your personal changes in the field schema are lost. To do: make the field schema updatable with new database table schema.
* rewrite the relations field to store less duplicated data in the field options and to make it reusable with Collections
* RestApi

## Requirements

### Cockpit

see [Cockpit's requirements][2]

* PHP >= 7.0
* PDO + SQLite (or MongoDB)
* GD extension
* mod_rewrite enabled (on apache)

### Tables addon

* PDO
* MySQL version ???
* InnoDB schema for MySQL tables (may function, with different schema, but I didn't test it yet)
* All tables must have a *single* column as primary key, which auto-increments. Choose a name, you want - it's not necessary, to name it `id`.

## Installation

Copy the folder `Tables` into `path/to/cockpit/addons`

## Usage/Configuration

* Your database with foreign keys exists already.
* Install [Cockpit CMS][3].
* Copy this addon into Cockpit's addon folder.
* Add your database credentials to Cockpit's config file `/config/config.yaml`.

```yaml
tables:
  db:
    host: localhost
    database: database_name
    user: root
    password: *****
    prefix: myprefix_ # experimental, not implemented completely
```

If you don't need Cockpit's core modules, disable them in the config:

```yaml
modules.disabled:
    - Collections
    - Singletons
    - Forms
```

## Credits

* I reused a big part of the [Collections module][4] from Cockpit CMS and modified it. Thanks @aheinze
* I used a minimalistic PDO wrapper from [phpdelusions.net][5]. Thanks @colshrapnel

## License

to do...




[1]: https://github.com/agentejo/cockpit/
[2]: https://github.com/agentejo/cockpit/#requirements
[3]: https://github.com/agentejo/cockpit/#installation
[4]: https://github.com/agentejo/cockpit/tree/next/modules/Collections
[5]: https://phpdelusions.net/pdo/pdo_wrapper#static_instance

*[1:m]: one-to-many
*[m:n]: many-to-many
