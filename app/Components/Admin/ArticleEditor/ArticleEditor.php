<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\ArticleException;
use App\Models\ArticleManager;
use App\Models\CategoryManager;
use App\Models\Helpers\StringHelper;
use App\Models\UserException;
use App\Models\UserManager;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

class ArticleEditor extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;

    /** @var ArticleManager @inject */
    private ArticleManager $articleManager;

    /** @var CategoryManager @inject */
    private CategoryManager $categoryManager;

    /** @var UserManager @inject */
    private UserManager $userManager;

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
                $this->param['category'] = ArticleManager::MAIN_CATEGORY_ID;
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
        $form->addSelect('category', $this->t('form.article.category'), $categorySelectOptions)
            ->setValue($this->param['category'] ?? ArticleManager::MAIN_CATEGORY_ID)
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

        $form->addDateTime('updated_at', $this->t('form.article.updated_at'))
            ->setHtmlAttribute('placeholder', $this->t('form.article.updated_at'))
            ->setValue($this->param['updated_at'] ?? '');

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
        unset($data['user_name']);

        if (empty($data['name_url'])) {
            $data['name_url'] = StringHelper::webalize($data['title']);
        }

        if (!StringHelper::isWebalized($data['name_url'])) {
            $data['name_url'] = StringHelper::webalize($data['name_url']);
        }

        $required = [
            ['name' => 'title', 'label.key' => 'form.article.title'],
            ['name' => 'category', 'label.key' => 'form.article.category'],
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

        $categoryId = (int) $data['category'];
        unset($data['category']);

        if ($this->origin == self::OriginEdit) {
            $articleId = (int) $data['id'];

            try {
                $this->articleManager->update($articleId, $data, $categoryId);
                call_user_func($this->onSuccess, $this->t('success.form.article-saved'), $articleId);
            } catch (ArticleException $e) {
                call_user_func($this->onError, $e->getMessage());
            }
        } else {
            unset($data['id']);
            $data['user_id'] = $this->presenter->getUser()->getId();

            try {
                $newArticleId = $this->articleManager->create($data, $categoryId);
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

    public function setUserManager(UserManager $userManager): void
    {
        $this->userManager = $userManager;
    }
}
