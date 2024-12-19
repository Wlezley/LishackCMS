<?php

declare(strict_types=1);

namespace App\Models;

class Article
{
}


/*

Table 'article':
================
id                  INT, AUTO_INCREMENT, PK Primární klíč
title               VARCHAR(255)            Název článku
name_url            VARCHAR(255)            Webalizovaný název pro URL
content             TEXT                    Obsah článku
published           TINYINT(1)              Viditelnost článku (1 = ano, 0 = ne)
published_at        DATETIME                Datum a čas publikování
updated_at          DATETIME                Datum a čas poslední aktualizace
user_id             INT                     ID uživatele, který článek vytvořil
robots              VARCHAR(255)            SEO robots hodnota (např. noindex, nofollow)
canonical_url       VARCHAR(255)            Canonical URL
og_title            VARCHAR(255)            OG meta tag og:title
og_description      VARCHAR(255)            OG meta tag og:description
og_image            VARCHAR(255)            OG meta tag og:image
og_url              VARCHAR(255)            OG meta tag og:url
og_type             VARCHAR(255)            OG meta tag og:type
meta_title          VARCHAR(255)            HTML <title> tag
meta_description    VARCHAR(255)            HTML meta description

*/
