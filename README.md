# Entity clone

Understand a *entity* like a register in the database, a *thing* that you need to manipulate.

This package has created aiming the need to facilitate the data replication between databases.

## Unit tests

The unit tests are seted up by default to consume local variable that must point to a database. It reads the following environment variables:

* ENTITYCLONE_DB_HOST: the database host.
* ENTITYCLONE_DB_PORT: the database port.
* ENTITYCLONE_DB_USER: the database user.
* ENTITYCLONE_DB_PASSWORD: the database password.
