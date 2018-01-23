<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\Page as PageModel;

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

        $this->unsetSubmitted('update');

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

        $data = $this->page->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Page'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a page description
     * @return boolean|null
     */
    protected function validateDescriptionPage()
    {
        $field = 'description';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 65535) {
            $this->setErrorLengthRange($field, $this->translation->text('Description'), 1, 65535);
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
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        $label = $this->translation->text('Category');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $category = $this->category->get($value);

        if (empty($category['category_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

}
