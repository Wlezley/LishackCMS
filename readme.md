Lishack CMS 🦊
==============

*Content management system (CMS) based on Nette Framework 3.2*

Requirements
------------

- PHP 8.2
- MySQL 8.4 LTS or MariaDB >= 11.4 LTS
- Composer >= 2.5.1
- Node.js >= 22.12 LTS

Installation
------------

1) Download and install Composer by following the [official instructions](https://getcomposer.org/download/).

2) Install dependencies using Composer:

    *development environment*

	    $ composer install

    *production environment*

        $ composer install --no-dev

3) Make directories `temp/` and `log/` writable.

4) Copy the distributed configuration file `./config/local.neon.dist` to `./config/local.neon` and set up a database connection (`host`, `port`, `name`, `user`, `password`), etc...

5) Download and install [Node.js](https://nodejs.org/en/download)

6) Now you can install the modules and build the frontend:

        $ npm install
        $ npm run build:all

NPM command list
-----------------

- *Build **Website** assets:*

       $ npm run build

- *Build **Website** assets in **DEV** mode (with `--watch`):*

       $ npm run build:dev

- *Build **Admin** assets:*

       $ npm run build:admin

- *Build **Admin** assets in **DEV** mode (with `--watch`):*

       $ npm run build:admin:dev

- *Build **TinyMCE** bundle assets:*

       $ npm run build:tinymce

- *Build **TinyMCE** bundle assets in **DEV** mode (with `--watch`):*

       $ npm run build:tinymce:dev

- *Build **ALL** assets:*

       $ npm run build:all

- *Build **ALL** assets in **DEV** mode:*

       $ npm run build:all:dev

- *Delete ALL `dist` folders:*

       $ npm run drop:dist

- *Delete `temp/cache` folder:*

       $ npm run drop:cache

Why?
----

I was bored and that's why I started developing this CMS. I know it's not ideal, but I see it as practicing my skills and experience. Maybe it will be useful to someone. :D
