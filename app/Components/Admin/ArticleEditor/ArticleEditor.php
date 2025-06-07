<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\ArticleException;
use App\Models\ArticleManager;
use App\Models\CategoryManager;
use App\Models\Helpers\StringHelper;
use App\Models\UrlGenerator;
use App\Models\UserException;
use App\Models\UserManager;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

class ArticleEditor extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    /** @var ArticleManager */
    private ArticleManager $articleManager;

    /** @var CategoryManager */
    private CategoryManager $categoryManager;

    /** @var UserManager */
    private UserManager $userManager;

    private string $origin;

    /** @var callable(string, int): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function createComponentForm(): Form
    {
        $form = new Form();
        $form->setHtmlAttribute('autocomplete', 'off');

        if (!empty($this->param['id'])) {
            $form->addHidden('id', $this->param['id']);

            try {
                $this->param['category'] = $this->articleManager->getCategoryIdById((int) $this->param['id']);
                $this->param['user_name'] = $this->userManager->get((int) $this->param['user_id'])['full_name'];
            } catch (ArticleException $e) {
                $this->param['category'] = CategoryManager::MAIN_CATEGORY_ID;
            } catch (UserException $e) {
                $this->param['user_name'] = $this->t('author.unknown');
            }
        } else {
            try {
                $this->param['user_name'] = $this->presenter->getUser()->getIdentity()->getData()['full_name'];
            } catch (\Exception $e) {
                $this->param['user_name'] = $this->t('author.system');
            }
        }

        // THE TITLE
        $form->addText('title', $this->t('form.article.title'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.title'))
            ->setValue($this->param['title'] ?? '')
            ->setRequired();

        // CATEGORY
        $categorySelectOptions = $this->categoryManager->getCategorySelectData();
        $form->addSelect('category_id', $this->t('form.article.category'), $categorySelectOptions)
            ->setValue($this->param['category_id'] ?? CategoryManager::MAIN_CATEGORY_ID)
            ->setRequired();

        // COMMON ATTRIBUTES
        $form->addText('name_url', $this->t('form.article.name_url'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.name_url'))
            ->setValue($this->param['name_url'] ?? '');

        $form->addText('user_name', $this->t('form.article.author'))
            ->setHtmlAttribute('readonly')
            ->setValue($this->param['user_name'] ?? '');

        $form->addCheckbox('published', $this->t('form.article.published'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.published'))
            ->setValue($this->param['published'] ?? 1);

        $form->addDateTime('published_at', $this->t('form.article.published_at'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.published_at'))
            ->setValue($this->param['published_at'] ?? DateTime::from(null))
            ->setRequired();

        $updated_at = 'N/A';
        if ($this->param['updated_at']) {
            $updated_at = DateTime::from($this->param['updated_at'])->format('d.m.Y h:i');
        }
        $form->addText('updated_at', $this->t('form.article.updated_at'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.updated_at'))
            ->setHtmlAttribute('readonly', true)
            ->setValue($updated_at);

        // META TAGS / SEO
        $form->addText('robots', $this->t('form.article.robots'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.robots'))
            ->setValue($this->param['robots'] ?? 'index, follow');

        $form->addText('canonical_url', $this->t('form.article.canonical_url'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.canonical_url'))
            ->setValue($this->param['canonical_url'] ?? '');

        $form->addText('meta_title', $this->t('form.article.meta_title'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.meta_title'))
            ->setValue($this->param['meta_title'] ?? '');

        $form->addText('meta_description', $this->t('form.article.meta_description'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.meta_description'))
            ->setValue($this->param['meta_description'] ?? '');

        // OPEN GRAPH PROTOCOL
        $form->addText('og_title', $this->t('form.article.og_title'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.og_title'))
            ->setValue($this->param['og_title'] ?? '');

        $form->addText('og_description', $this->t('form.article.og_description'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.og_description'))
            ->setValue($this->param['og_description'] ?? '');

        $form->addText('og_image', $this->t('form.article.og_image'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.og_image'))
            ->setValue($this->param['og_image'] ?? '');

        $form->addText('og_type', $this->t('form.article.og_type'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.og_type'))
            ->setValue($this->param['og_type'] ?? '');

        // TINY MCE
        $form->addTextArea('content', $this->t('form.article.content'))
            ->setValue($this->param['content'] ?? '');

        $saveBtnName = ($this->origin == self::OriginCreate)
            ? $this->t('form.article.create')
            : $this->t('form.article.save');

        $form->addSubmit('save', $saveBtnName)
            ->onClick[] = [$this, 'processSave'];

        $form->addSubmit('copy', $this->t('form.article.save-copy'))
            ->onClick[] = [$this, 'processSaveAsCopy'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $data = (array)$values;
        unset($data['user_name'], $data['updated_at']);

        if (empty($data['name_url'])) {
            $data['name_url'] = StringHelper::webalize($data['title']);
        }

        if (!StringHelper::isWebalized($data['name_url'])) {
            $data['name_url'] = StringHelper::webalize($data['name_url']);
        }

        $required = [
            ['name' => 'title', 'label.key' => 'form.article.title'],
            ['name' => 'category_id', 'label.key' => 'form.article.category_id'],
            ['name' => 'name_url', 'label.key' => 'form.article.name_url'],
            ['name' => 'published_at', 'label.key' => 'form.article.published_at'],
        ];

        foreach ($required as $item) {
            if (empty($data[$item['name']])) {
                $label = $this->t($item['label.key']);
                call_user_func($this->onError, $this->tf('error.form.missing-required.settings', $label));
                return;
            }
        }

        if ($this->origin == self::OriginEdit) {
            $articleId = (int) $data['id'];
            $data['name_url'] = $this->urlGenerator->generateUniqueNameUrl($data['name_url'], $articleId);

            try {
                $this->articleManager->update($articleId, $data);
                call_user_func($this->onSuccess, $this->t('success.form.article-saved'), $articleId);
            } catch (ArticleException $e) {
                call_user_func($this->onError, $e->getMessage());
            }
        } else {
            unset($data['id']);
            $data['user_id'] = $this->presenter->getUser()->getId();
            $data['name_url'] = $this->urlGenerator->generateUniqueNameUrl($data['name_url']);

            try {
                $newArticleId = $this->articleManager->create($data);
                call_user_func($this->onSuccess, $this->t('success.form.article-created'), $newArticleId);
            } catch (ArticleException $e) {
                call_user_func($this->onError, $e->getMessage());
            }
        }
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSaveAsCopy(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $this->setOrigin(self::OriginCreate);
        $this->processSave($form, $values);
    }

    public function render(): void
    {
        $this->template->origin = $this->origin;
        $this->template->setFile(__DIR__ . '/ArticleEditor.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function setArticleManager(ArticleManager $articleManager): void
    {
        $this->articleManager = $articleManager;
    }

    public function setCategoryManager(CategoryManager $categoryManager): void
    {
        $this->categoryManager = $categoryManager;
    }

    public function setUrlGenerator(UrlGenerator $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function setUserManager(UserManager $userManager): void
    {
        $this->userManager = $userManager;
    }
}
