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
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate a product class data
 */
class ProductClass extends ComponentValidator
{

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param ProductClassModel $product_class
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            ProductClassModel $product_class)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->product_class = $product_class;
    }

    /**
     * Performs full product class validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function productClass(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductClass();
        $this->validateStatus();
        $this->validateTitle();

        return $this->getResult();
    }

    /**
     * Validates a product class ID
     * @return boolean|null
     */
    protected function validateProductClass()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->product_class->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Product class'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

}
