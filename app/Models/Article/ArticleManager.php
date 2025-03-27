<?php

declare(strict_types=1);

namespace App\Models;

class ArticleManager extends BaseModel
{
    public const TABLE_NAME_ARTICLE = 'article';
    public const TABLE_NAME_ARTICLE_CATEGORY = 'article_category';
    public const TABLE_NAME_CATEGORY = 'category';

    /** @todo Make this value configurable? */
    public const MAIN_CATEGORY_ID = 1;

    /** @return array<string,mixed> */
    public function getById(int $id): array
    {
        $article = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('id', $id)
            ->fetch();

        if (!$article) {
            throw new ArticleException(
                "Article (ID '$id') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->toArray();
    }

    /** @return array<string,mixed> */
    public function getByNameUrl(string $name_url): array
    {
        $article = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('name_url', $name_url)
            ->fetch();

        if (!$article) {
            throw new ArticleException(
                "Article (name_url: '$name_url') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->toArray();
    }

    public function getIdByUrlAndCategory(string $nameUrl, int $categoryId): int
    {
        $articleId = $this->db->table(self::TABLE_NAME_ARTICLE_CATEGORY)
            ->select('article_id')
            ->where('article_name_url', $nameUrl)
            ->where('category_id', $categoryId)
            ->fetch();

        if (!$articleId) {
            throw new ArticleException(
                "Unable to find article ID by article_name_url: '$nameUrl' and category_id: '$categoryId'.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return (int) $articleId['article_id'];
    }

    /** @param array<string> $categoryUrlList */
    public function resolveCategoryId(array $categoryUrlList): int
    {
        $categoryId = self::MAIN_CATEGORY_ID;

        /** @todo Get from category manager */
        $categoryList = $this->db->table(self::TABLE_NAME_CATEGORY)
            ->select('id, parent_id, name_url')
            ->fetchAll();

        foreach ($categoryUrlList as $nameUrl) {
            $found = false;

            foreach ($categoryList as $category) {
                if ($category['name_url'] == $nameUrl && $category['parent_id'] == $categoryId) {
                    $found = true;
                    $categoryId = $category['id'];
                    break;
                }
            }

            if (!$found) {
                throw new ArticleException(
                    "Unable to find category ID for name_url: '$nameUrl' with parent_id: '$categoryId'.",
                    \Nette\Http\IResponse::S404_NotFound
                );
            }
        }

        return $categoryId;
    }

    public function generateUrl(int $id): string
    {
        $article_category = $this->db->table(self::TABLE_NAME_ARTICLE_CATEGORY)
            ->where('article_id', $id)
            ->fetch();

        if (!$article_category) {
            throw new ArticleException("Article ID $id is not listed in any category.", 1);
        }

        $name_url = $article_category['article_name_url'];
        $cID = $article_category['category_id'];

        if ($cID == self::MAIN_CATEGORY_ID) {
            if ($this->configManager->get('DEFAULT_PAGE') == $name_url) {
                return HOME_URL;
            } else {
                return HOME_URL . $name_url . '/';
            }
        }

        /** @todo Get from category manager */
        $categoryList = $this->db->table(self::TABLE_NAME_CATEGORY)
            ->select('id, parent_id, level, name_url')
            ->fetchAll();

        $limit = $categoryList[$cID]['level'];
        $parent_id = $categoryList[$cID]['id'];
        $name_url = $categoryList[$cID]['name_url'] . '/' . $name_url;

        for ($level = 1; $level < $limit; $level++) {
            if (!isset($categoryList[$parent_id])) {
                throw new ArticleException("Category ID $parent_id not found.", 1);
            }

            $parent_id = $categoryList[$parent_id]['parent_id'];
            $name_url = $categoryList[$parent_id]['name_url'] . '/' . $name_url;
        }

        return HOME_URL . $name_url . '/';
    }

    /** @return array<string> */
    public function normalizeCategoryUrl(string $categoryUrl): array
    {
        $categoryUrlListRaw = explode('/', $categoryUrl);
        $categoryUrlList = array_values(array_filter($categoryUrlListRaw));

        if (!empty($categoryUrl) && count($categoryUrlListRaw) !== count($categoryUrlList)) {
            $e = new ArticleException('Broken Category URL', \Nette\Http\IResponse::S301_MovedPermanently);
            $e->setCategoryUrl(implode('/', $categoryUrlList));
            throw $e;
        }

        return $categoryUrlList;
    }

    // #####################################
    // ###          DB HANDLERS          ###
    // #####################################

    /**
     * @param array<string,mixed> $data Article data
     * @param int|null $categoryId Article category ID, default is `ArticleManager::MAIN_CATEGORY_ID`.
     * @return int New article ID
     * @throws \InvalidArgumentException If any extra data keys are found.
     * @throws ArticleException If article cration failed.
     *
     * @todo Create something like... ArticleValidator::prepare($data)
     */
    public function create(array $data, ?int $categoryId = self::MAIN_CATEGORY_ID): int
    {
        $newArticle = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->insert($data);

        if (!$newArticle) {
            throw new ArticleException('Article creation failed.', 1);
        }

        if ($categoryId) {
            $this->db->table(self::TABLE_NAME_ARTICLE_CATEGORY)
                ->insert([
                    'article_id' => $newArticle['id'],
                    'article_name_url' => $data['name_url'],
                    'category_id' => $categoryId,
                ]);
        }

        return (int) $newArticle['id'];
    }

    /**
     * @param int $id Article ID
     * @param array<string,mixed> $data Article data
     * @throws \InvalidArgumentException If any extra data keys are found.
     *
     * @todo Create something like... ArticleValidator::prepare($data)
     */
    public function update(int $id, array $data): void
    {
        $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('id', $id)
            ->update($data);

        $this->db->table(self::TABLE_NAME_ARTICLE_CATEGORY)
            ->where('article_id', $id)
            ->update([
                'article_name_url' => $data['name_url']
            ]);
    }

    public function delete(int $id): void
    {
        $this->db->table(self::TABLE_NAME_ARTICLE_CATEGORY)
            ->where('article_id', $id)
            ->delete();

        $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('id', $id)
            ->delete();
    }
}
