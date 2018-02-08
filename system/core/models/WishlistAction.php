<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\Hook;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\models\Wishlist as WishlistModel;

/**
 * Manages basic behaviors and data related to wishlist actions
 */
class WishlistAction
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Wishlist model instance
     * @var \gplcart\core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * @param Hook $hook
     * @param WishlistModel $wishlist
     * @param TranslationModel $translation
     * @param UrlHelper $url
     */
    public function __construct(Hook $hook, WishlistModel $wishlist, TranslationModel $translation,
                                UrlHelper $url)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->wishlist = $wishlist;
        $this->translation = $translation;
    }

    /**
     * Adds a product to a wishlist and returns an array of result data
     * @param array $data
     * @return array
     */
    public function add(array $data)
    {
        $result = array();
        $this->hook->attach('wishlist.add.product.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if ($this->wishlist->exists($data)) {
            return $this->getResultErrorExists($data);
        }

        if ($this->wishlist->exceedsLimit($data['user_id'], $data['store_id'])) {
            return $this->getResultErrorExceeds($data);
        }

        $data['wishlist_id'] = $this->wishlist->add($data);

        if (empty($data['wishlist_id'])) {
            return $this->getResultError();
        }

        $result = $this->getResultAdded($data);
        $this->hook->attach('wishlist.add.product.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Delete a product from the wishlist
     * @param array $data
     * @return array
     */
    public function delete(array $data)
    {
        $result = array();
        $this->hook->attach('wishlist.delete.product.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (!$this->wishlist->delete($data)) {
            return $this->getResultError();
        }

        $result = $this->getResultDelete($data);
        $this->hook->attach('wishlist.delete.product.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of resulting data when a product has been added to the wishlist
     * @param array $data
     * @return array
     */
    protected function getResultAdded(array $data)
    {
        $options = array(
            'user_id' => $data['user_id'],
            'store_id' => $data['store_id']
        );

        $href = $this->url->get('wishlist');
        $existing = $this->wishlist->getList($options);

        return array(
            'redirect' => '',
            'severity' => 'success',
            'quantity' => count($existing),
            'wishlist_id' => $data['wishlist_id'],
            'message' => $this->translation->text('Product has been added to your <a href="@url">wishlist</a>', array(
                '@url' => $href)));
    }

    /**
     * Returns an array of resulting data when a user exceeds the max allowed number of products in the wishlist
     * @param array $data
     * @return array
     */
    protected function getResultErrorExceeds(array $data)
    {
        $vars = array('%num' => $this->wishlist->getLimit($data['user_id']));

        return array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->translation->text("You're exceeding limit of %num items", $vars)
        );
    }

    /**
     * Returns an array of resulting data when a product already exists in the wishlist
     * @param array $data
     * @return array
     */
    protected function getResultErrorExists(array $data)
    {
        $vars = array('@url' => $this->url->get('wishlist'));

        return array(
            'redirect' => '',
            'severity' => 'warning',
            'wishlist_id' => $data['wishlist_id'],
            'message' => $this->translation->text('Product already in your <a href="@url">wishlist</a>', $vars)
        );
    }

    /**
     * Returns an array of resulting data when a product has been deleted from a wishlist
     * @param array $data
     * @return array
     */
    protected function getResultDelete(array $data)
    {
        unset($data['product_id']);

        $existing = $this->wishlist->getList($data);

        return array(
            'message' => '',
            'severity' => 'success',
            'quantity' => count($existing),
            'redirect' => empty($existing) ? 'wishlist' : ''
        );
    }

    /**
     * Returns an array of resulting data when a error occurred during an action
     * @return array
     */
    protected function getResultError()
    {
        return array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->translation->text('An error occurred')
        );
    }

}
