<?php

declare(strict_types=1);

namespace Models;

class Files
{
    public function __construct()
    {
    }


    /*
    TODO LIST:
    ----------
    Read file
    Write file
    Data sotrage structure (directory tree)
    Database catalog

    TODO from 09-10-2024:
    Rozdělit na hlavní třídu Storage a potomky StorageFiles, StorageDatabase, atd. (např. StorageGdrive, StorageAWS, StorageOneDrive)
    */

}


/*

Table storage_data:
-------------------
id              INT(11)                             ID dat
data            BLOB                                Raw data


Table storage_files:
--------------------
id              INT(11)                             ID souboru
tree_id         INT(11)         <storage_tree.id>   ID adresáře, do kterého je soubor zařazen (0 == root)
position        INT(11)                             Pozice souboru ve složce (vlastní řazení)
name            VARCHAR(255)                        Jméno souboru
mime            VARCHAR(50)                         Datový typ souboru
original_name   VARCHAR(255)                        Původní jméno souboru
description     TEXT                                Popis souboru
owner_id        INT(11)         <user.id>           ID vlastníka (uživatele)
created         DATETIME                            Datum vytvoření
uploaded        DATETIME                            Datum nahrání
modified        DATETIME                            Datum poslední změny
modified_by     INT(11)         <user.id>           ID uživatele poslední změny
removed         DATETIME                            Datum odstranění
removed_by      INT(11)         <user.id>           ID uživatele který soubor odstranil


Table storage_tree:
-------------------
id              INT(11)                             ID adresáře (0 == root, je nutný pro správnou funkci souborového systému)
name            VARCHAR(255)                        Jméno adresáře
description     TEXT                                Popis adresáře
parent_id       INT(11)         <storage_tree.id>   ID nadřazeného adresáře (0 == root)
owner_id        INT(11)         <user.id>           ID vlastníka (uživatele)
created         DATETIME                            Datum vytvoření
modified        DATETIME                            Datum poslední změny
modified_by     INT(11)         <user.id>           ID uživatele poslední změny
removed         DATETIME                            Datum odstranění
removed_by      INT(11)         <user.id>           ID uživatele který adresář odstranil



*/
