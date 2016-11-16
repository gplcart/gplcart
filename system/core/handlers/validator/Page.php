<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Page as ModelsPage;
use core\models\Category as ModelsCategory;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate page data
 */
class Page extends BaseValidator
{

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Page model instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Constructor
     * @param ModelsPage $page
     * @param ModelsCategory $category
     */
    public function __construct(ModelsPage $page, ModelsCategory $category)
    {
        parent::__construct();

        $this->page = $page;
        $this->category = $category;
    }

    /**
     * Performs page data validation
     * @param array $submitted
     */
    public function page(array &$submitted, array $options = array())
    {
        $this->validatePage($submitted);
        $this->validateStatus($submitted);
        $this->validateTitle($submitted);
        $this->validateDescriptionPage($submitted);
        $this->validateMetaTitle($submitted);
        $this->validateMetaDescription($submitted);
        $this->validateTranslation($submitted);
        $this->validateStoreId($submitted);
        $this->validateCategoryPage($submitted);
        $this->validateUserId($submitted);
        $this->validateImages($submitted);
        $this->validateAliasPage($submitted);

        return $this->getResult();
    }

    /**
     * Validates a page to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validatePage(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $page = $this->page->get($submitted['update']);
            if (empty($page)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Page')));
                return false;
            }

            $submitted['update'] = $page;
        }

        return true;
    }

    /**
     * Validates a page description
     * @param array $submitted
     * @return boolean
     */
    protected function validateDescriptionPage(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['description'])) {
            return null;
        }

        if (empty($submitted['description']) || mb_strlen($submitted['description']) > 65535) {
            $options = array('@min' => 1, '@max' => 65535, '@field' => $this->language->text('Description'));
            $this->errors['description'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a category ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateCategoryPage(array &$submitted)
    {
        if (empty($submitted['category_id'])) {
            return null; // Category ID is not required
        }

        if (!is_numeric($submitted['category_id'])) {
            $options = array('@field' => $this->language->text('Category'));
            $this->errors['category_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $category = $this->category->get($submitted['category_id']);

        if (empty($category)) {
            $this->errors['category_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Category')));
            return false;
        }

        return true;
    }

    /**
     * Validates / creates an alias
     * @param array $submitted
     * @return boolean
     */
    protected function validateAliasPage(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        if (isset($submitted['alias'])//
                && isset($submitted['update']['alias'])//
                && ($submitted['update']['alias'] === $submitted['alias'])) {
            return true; // Do not check own alias on update
        }

        if (empty($submitted['alias']) && !empty($submitted['update'])) {
            $submitted['alias'] = $this->page->createAlias($submitted);
            return true;
        }

        return $this->validateAlias($submitted);
    }

}
