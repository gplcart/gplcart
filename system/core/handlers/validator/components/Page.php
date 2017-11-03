<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

// Parent
use gplcart\core\Config;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\models\Language as LanguageModel;
// New
use gplcart\core\models\Page as PageModel,
    gplcart\core\models\Category as CategoryModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate page data
 */
class Page extends ComponentValidator
{

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param PageModel $page
     * @param CategoryModel $category
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            PageModel $page, CategoryModel $category)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->page = $page;
        $this->category = $category;
    }

    /**
     * Performs page data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function page(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validatePage();
        $this->validateStatus();
        $this->validateTitle();
        $this->validateDescriptionPage();
        $this->validateMetaTitle();
        $this->validateMetaDescription();
        $this->validateTranslation();
        $this->validateStoreId();
        $this->validateCategoryPage();
        $this->validateUserId();
        $this->validateImages();
        $this->validateAlias();
        $this->validateUploadImages('page');

        return $this->getResult();
    }

    /**
     * Validates a page to be updated
     * @return boolean|null
     */
    protected function validatePage()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $page = $this->page->get($id);

        if (empty($page)) {
            $this->setErrorUnavailable('update', $this->language->text('Page'));
            return false;
        }

        $this->setUpdating($page);
        return true;
    }

    /**
     * Validates a page description
     * @return boolean|null
     */
    protected function validateDescriptionPage()
    {
        $field = 'description';
        $label = $this->language->text('Description');
        $description = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($description)) {
            return null;
        }

        if (empty($description) || mb_strlen($description) > 65535) {
            $this->setErrorLengthRange($field, $label, 1, 65535);
            return false;
        }
        return true;
    }

    /**
     * Validates a category ID
     * @return boolean|null
     */
    protected function validateCategoryPage()
    {
        $field = 'category_id';
        $label = $this->language->text('Category');
        $category_id = $this->getSubmitted($field);

        if (empty($category_id)) {
            return null; // Category ID is not required
        }

        if (!is_numeric($category_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $category = $this->category->get($category_id);

        if (empty($category['category_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }
        return true;
    }

}
