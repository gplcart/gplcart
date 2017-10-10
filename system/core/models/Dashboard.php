<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Handler;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related admin dashboard
 */
class Dashboard extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of dashboard handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static('dashboard.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = require GC_CONFIG_DASHBOARD;
        $this->hook->attach('dashboard.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Adds a dashboard record
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('dashboard.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('dashboard', $data);

        $this->hook->attach('dashboard.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a dashboard
     * @param integer $dashboard_id
     * @param array $data
     * @return boolean
     */
    public function update($dashboard_id, array $data)
    {
        $result = null;
        $this->hook->attach('dashboard.update.before', $dashboard_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('dashboard', $data, array('dashboard_id' => $dashboard_id));
        $this->hook->attach('dashboard.update.after', $dashboard_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns a dashboard record by a user ID
     * @param integer $user_id
     * @param bool $active
     * @return array
     */
    public function getByUser($user_id, $active = true)
    {
        $sql = 'SELECT * FROM dashboard WHERE user_id=?';
        $result = $this->db->fetch($sql, array($user_id), array('unserialize' => 'data'));

        $handlers = $this->getHandlers();

        if (empty($result['data'])) {
            $result['data'] = $handlers;
        } else {
            $result['data'] = array_replace_recursive($handlers, $result['data']);
        }

        foreach ($result['data'] as $handler_id => &$handler) {
            if ($active && empty($handler['status'])) {
                unset($result['data'][$handler_id]);
                continue;
            }

            $handler['title'] = $this->language->text($handler['title']);
            $handler['data'] = Handler::call($handlers, $handler_id, 'data');
        }

        gplcart_array_sort($result['data']);

        $this->hook->attach('dashboard.get.user', $result, $this);
        return $result;
    }

    /**
     * Add/update a dashboard record for a user
     * @param integer $user_id
     * @param array $data
     * @return bool|integer
     */
    public function setByUser($user_id, array $data)
    {
        $existing = $this->getByUser($user_id);

        if (isset($existing['dashboard_id'])) {
            return $this->update($existing['dashboard_id'], array('data' => $data));
        }

        return $this->add(array('user_id' => $user_id, 'data' => $data));
    }

    /**
     * Deletes a dashboard record
     * @param integer $dashboard_id
     * @return boolean
     */
    public function delete($dashboard_id)
    {
        $result = null;
        $this->hook->attach('dashboard.delete.before', $dashboard_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('dashboard', array('dashboard_id'));
        $this->hook->attach('dashboard.delete.after', $dashboard_id, $result, $this);
        return (bool) $result;
    }

}
