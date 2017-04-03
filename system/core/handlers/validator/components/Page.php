<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

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
     * Constructor
     * @param PageModel $page
     * @param CategoryModel $category
     */
    public function __construct(PageModel $page, CategoryModel $category)
    {
        parent::__construct();

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
        $this->validateStatusComponent();
        $this->validateTitleComponent();
        $this->validateDescriptionPage();
        $this->validateMetaTitleComponent();
        $this->validateMetaDescriptionComponent();
        $this->validateTranslationComponent();
        $this->validateStoreIdComponent();
        $this->validateCategoryPage();
        $this->validateUserIdComponent();
        $this->validateImagesComponent();
        $this->validateAliasComponent();

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
            $vars = array('@name' => $this->language->text('Page'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
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
        $description = $this->getSubmitted('description');

        if ($this->isUpdating() && !isset($description)) {
            return null;
        }

        if (empty($description) || mb_strlen($description) > 65535) {
            $vars = array('@min' => 1, '@max' => 65535, '@field' => $this->language->text('Description'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('description', $error);
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
        $category_id = $this->getSubmitted('category_id');

        if (empty($category_id)) {
            return null; // Category ID is not required
        }

        if (!is_numeric($category_id)) {
            $vars = array('@field' => $this->language->text('Category'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('category_id', $error);
            return false;
        }

        $category = $this->category->get($category_id);

        if (empty($category['category_id'])) {
            $vars = array('@name' => $this->language->text('Category'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('category_id', $error);
            return false;
        }

        return true;
    }

}
