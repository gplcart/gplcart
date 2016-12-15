<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Page as PageModel;
use core\models\Category as CategoryModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate page data
 */
class Page extends BaseValidator
{

    /**
     * Page model instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Category model instance
     * @var \core\models\Category $category
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
        $this->submitted = &$submitted;

        $this->validatePage($options);
        $this->validateStatus($options);
        $this->validateTitle($options);
        $this->validateDescriptionPage($options);
        $this->validateMetaTitle($options);
        $this->validateMetaDescription($options);
        $this->validateTranslation($options);
        $this->validateStoreId($options);
        $this->validateCategoryPage($options);
        $this->validateUserId($options);
        $this->validateImages($options);
        $this->validateAliasPage($options);

        return $this->getResult();
    }

    /**
     * Validates a page to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validatePage(array $options)
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
     * @param array $options
     * @return boolean|null
     */
    protected function validateDescriptionPage(array $options)
    {
        $description = $this->getSubmitted('description', $options);

        if ($this->isUpdating() && !isset($description)) {
            return null;
        }

        if (empty($description) || mb_strlen($description) > 65535) {
            $vars = array('@min' => 1, '@max' => 65535, '@field' => $this->language->text('Description'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('description', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a category ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateCategoryPage(array $options)
    {
        $category_id = $this->getSubmitted('category_id', $options);

        if (empty($category_id)) {
            return null; // Category ID is not required
        }

        if (!is_numeric($category_id)) {
            $vars = array('@field' => $this->language->text('Category'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('category_id', $error, $options);
            return false;
        }

        $category = $this->category->get($category_id);

        if (empty($category['category_id'])) {
            $vars = array('@name' => $this->language->text('Category'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('category_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates/creates an alias
     * @param array $options
     * @return boolean|null
     */
    protected function validateAliasPage(array $options)
    {
        if ($this->isError()) {
            return null; // Stop if a error has occured before
        }

        $updating = $this->getUpdating();
        $alias = $this->getSubmitted('alias', $options);

        if (isset($alias)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $alias)) {
            return true; // Do not check own alias on update
        }

        if (empty($alias) && $this->isUpdating()) {
            $data = $this->getSubmitted();
            $alias = $this->page->createAlias($data);
            $this->setSubmitted('alias', $alias, $options);
            return true;
        }

        return $this->validateAlias($options);
    }

}
