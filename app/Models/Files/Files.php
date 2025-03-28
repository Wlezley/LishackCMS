<?php

declare(strict_types=1);

namespace App\Models;

class Files extends BaseModel
{
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
name_url        VARCHAR(255)                        URL souboru
original_name   VARCHAR(255)                        Původní jméno souboru
mime            VARCHAR(50)                         Datový typ souboru
description     TEXT                                Popis souboru
owner_id        INT(11)         <user.id>           ID vlastníka (uživatele)
created         DATETIME                            Datum vytvoření
modified        DATETIME                            Datum poslední změny
modified_by     INT(11)         <user.id>           ID uživatele poslední změny
removed         DATETIME                            Datum odstranění
removed_by      INT(11)         <user.id>           ID uživatele který soubor odstranil


Table storage_tree:
-------------------
id              INT(11)                             ID adresáře (0 == root, je nutný pro správnou funkci souborového systému)
parent_id       INT(11)         <storage_tree.id>   ID nadřazeného adresáře (0 == root)
position        INT(11)                             Pozice souboru ve složce (vlastní řazení)
name            VARCHAR(255)                        Jméno adresáře
name_url        VARCHAR(255)                        URL adresáře
description     TEXT                                Popis adresáře
owner_id        INT(11)         <user.id>           ID vlastníka (uživatele)
created         DATETIME                            Datum vytvoření
modified        DATETIME                            Datum poslední změny
modified_by     INT(11)         <user.id>           ID uživatele poslední změny
removed         DATETIME                            Datum odstranění
removed_by      INT(11)         <user.id>           ID uživatele který adresář odstranil



*/
