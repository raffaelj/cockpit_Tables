## user groups and access control lists

to do...

* create user groups
* create users
* set permissions
* configure mailer for user password reset
* ...

## config.yaml example

```json
groups:
    manager:
        cockpit:
            backend: true
        tables:
            admin: false
            manage: true
            create: true
            table_edit: true
            entries_view: true
            entries_edit: true
            entries_create: true
            entries_delete: true
    assistant:
        cockpit:
            backend: true
        tables:
            admin: false
            entries_view: true
            entries_edit: true
            entries_create: true
    guest:
        cockpit:
            backend: true
        tables:
            admin: false
            manage: false
            create: false
            delete: false
            table_edit: false
            entries_view: true
            entries_edit: false
            entries_create: false
            entries_delete: false
```

## Settings/UI

...

## permissions per table

...

### public

not implemented, yet

...

### entries_view vs. populate

entries_view: list tables
populate: can see table content via `/tables/find` and list fields of referenced table

...
