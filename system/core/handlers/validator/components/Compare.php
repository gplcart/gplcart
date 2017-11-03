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
use gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate product comparison data
 */
class Compare extends ComponentValidator
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param ProductModel $product
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            ProductModel $product)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->product = $product;
    }

    /**
     * Performs full product comparison data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function compare(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductCompare();
        return $this->getResult();
    }

    /**
     * Validates a compared product ID
     * @return boolean
     */
    protected function validateProductCompare()
    {
        $field = 'product_id';
        $label = $this->language->text('Product');
        $product_id = $this->getSubmitted($field);

        if (empty($product_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($product_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

}
