<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\User as ModelsUser;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate various user related data
 */
class User
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsUser $user
     */
    public function __construct(ModelsLanguage $language, ModelsUser $user)
    {
        $this->user = $user;
        $this->language = $language;
    }

    /**
     * Checks if a product in the database
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function emailExists($value, array $options = array())
    {
        if (empty($value) && empty($options['required'])) {
            return true;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user)) {
            return $this->language->text('E-mail does not exist');
        }
        
        if (!empty($options['status']) && empty($user['status'])) {
            return $this->language->text('E-mail does not exist');
        }

        return array('result' => $user);
    }
    
    /**
     * Checks if an email is unique
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function emailUnique($value, array $options = array())
    {
        if (isset($options['data']['email']) && ($options['data']['email'] === $value)) {
            return true;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user)) {
            return true;
        }

        return $this->language->text('E-mail already exists');
    }
    
    /**
     * Checks if a name is unique
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function nameUnique($value, array $options = array())
    {
        if (isset($options['data']['name']) && ($options['data']['name'] === $value)) {
            return true;
        }

        $user = $this->user->getByName($value);

        if (empty($user)) {
            return true;
        }

        return $this->language->text('Name already exists');
    }

}
