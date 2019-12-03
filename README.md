# Tables addon for Cockpit CMS

Manage SQL tables with one-to-many (1:m) and many-to-many (m:n) relations in [Cockpit CMS][1].

Cockpit is a leightweight, headless CMS. It's internal logic is based on MongoDB - a schemaless database. It has a wrapper to use the same logic with SQLite, <del>but there's no real SQL implementation, yet</del>. When I find the time, I'll test a setup with the new, experimental [SQL Driver addon][12] from [@piotr-cz][13].

The Tables addon adds the functionality, to *manage* a SQL database. Cockpit still needs MongoDB or SQLite for it's internal logic.

This addon needs a lot of cleanup and some restructuring. If you use it in production, be aware of possible structural changes. I can't guarantee for backwards compatibility in this early state. It is not performance optimized for large databases, yet.

Please send me some feedback, if you tested the addon.

[docs](docs/README.md)

## Features

* automatic detection of all available tables in the database
* automatic detection of foreign key relations
* automatic generation of field schema with basic type detection (boolean, number, text, textarea, date)
  * with basic validation detection
    * required (`NOT NULL`)
    * maxlength (`VARCHAR(100)`)
* automatic `LEFT OUTER JOIN` to display 1:m related fields in entries and entry views
* if a m:n relation is detected, an extra field is created with a select field for the related content
* save and delete values to/in related m:n tables
* The automatically generated field schema can be adjusted by changing the field settings, like
  * changing text to textarea
  * make a field required, that wasn't setup as nullable in the database
  * change a relation field to a number field to avoid the automatic join
  * remove a m:n extra field, to avoid displaying it in a m:n helper table
  * ...
* split relation-select field, if the related column contains a lot of rows (optional)
* user and group rights management
* spreadsheet export (ODS, CSV, XLSX)

## Features (enhancement, maybe, in the future...)

* RestApi
* graphical relation manager
* update database schema directly (create new table, add index etc.)
* spreadsheet import

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

Copy this repository into `/addons` and name it `Tables` or

```bash
cd path/to/cockpit
git clone https://github.com/raffaelj/cockpit_Tables.git addons/Tables
```

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
    password: SuperSecretPassword
```

Alternatively you can set the path to a config file.

```yaml
tables:
  db: /path/to/config.php
  # db: /path/to/config.ini
  # db: /path/to/config.yaml
```

If you don't need Cockpit's core modules, disable them in the config:

```yaml
modules.disabled:
    - Collections
    - Singletons
    - Forms
```

## Copyright and License

Copyright 2019 Raffael Jesche under the MIT license.

See [LICENSE][11] for more information.

## Credits and third party resources

I reused a big part of the [Collections module][4] from Cockpit CMS, which is released under the [MIT License][6], and modified it. Thanks at [Artur Heinze][7] and to all [contributors][8].

For exporting spreadsheets, I used [PhpSpreadsheet][9], which is released under the [LGPL 2.1 License][10].

I used a minimalistic PDO wrapper from [phpdelusions.net][5]. Thanks @colshrapnel

For a top scrollbar above tables I used the jQuery plugin [jqDoubleScroll][14] from [Antoine Vianey][15], which is dual licensed under the [MIT License][16] and [GPL License][17].

For syntax highlighting in the docs, I used [highlight.js][18] ([Github][19], [authors][20]), which is licensed under the [BSD 3-Clause License][21]


[1]: https://github.com/agentejo/cockpit/
[2]: https://github.com/agentejo/cockpit/#requirements
[3]: https://github.com/agentejo/cockpit/#installation
[4]: https://github.com/agentejo/cockpit/tree/next/modules/Collections
[5]: https://phpdelusions.net/pdo/pdo_wrapper#static_instance
[6]: https://github.com/agentejo/cockpit/blob/next/LICENSE
[7]: https://github.com/aheinze
[8]: https://github.com/agentejo/cockpit/graphs/contributors
[9]: https://github.com/PHPOffice/PhpSpreadsheet
[10]: https://github.com/PHPOffice/PhpSpreadsheet/blob/master/LICENSE
[11]: https://github.com/raffaelj/cockpit_Tables/blob/master/LICENSE
[12]: https://github.com/piotr-cz/cockpit-sql-driver
[13]: https://github.com/piotr-cz
[14]: https://github.com/avianey/jqDoubleScroll
[15]: https://github.com/avianey
[16]: http://www.opensource.org/licenses/mit-license.php
[17]: http://www.gnu.org/licenses/gpl.html
[18]: https://highlightjs.org/
[19]: https://github.com/highlightjs/highlight.js
[20]: https://github.com/highlightjs/highlight.js/blob/master/AUTHORS.en.txt
[21]: https://github.com/highlightjs/highlight.js/blob/master/LICENSE
