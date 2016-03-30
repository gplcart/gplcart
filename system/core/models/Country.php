<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\classes\Tool;
use core\classes\Cache;
use core\models\Language;

class Country
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Language $language
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Language $language, Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->language = $language;
        $this->db = $this->config->db();
    }

    /**
     * Returns an array of country format
     * @param string $country_code
     * @param bool $only_enabled
     * @return type
     */
    public function getFormat($country_code, $only_enabled = false)
    {
        $country = $this->get($country_code);

        if (empty($country['format'])) {
            $format = $this->defaultFormat();
            Tool::sortWeight($format);
        } else {
            $format = $country['format'];
        }

        if ($only_enabled) {
            $format = array_filter($format, function($item) {
                return !empty($item['status']);
            });
        }
        return $format;
    }

    /**
     * Loads a country from the database
     * @param string $country_code
     * @return array
     */
    public function get($country_code)
    {
        $country = &Cache::memory("country.$country_code");
        
        if(isset($country)){
            return $country;
        }
        
        $this->hook->fire('get.country.before', $country_code);

        $sth = $this->db->prepare('SELECT * FROM country WHERE code=:code');
        $sth->execute(array(':code' => $country_code));

        $country = $sth->fetch(PDO::FETCH_ASSOC);

        if ($country) {
            $country['format'] = unserialize($country['format']);
            $default_format = $this->defaultFormat();
            $country['format'] = Tool::merge($default_format, $country['format']);
            $country['default'] = $this->isDefault($country_code);
            Tool::sortWeight($country['format']);
        }

        $this->hook->fire('get.country.after', $country_code, $country);
        return $country;
    }

    /**
     * Returns the default country format
     * @return array
     */
    public function defaultFormat()
    {
        return array(
            'country' => array(
                'name' => $this->language->text('Country'),
                'required' => 0, 'weight' => 0,
                'status' => 1
            ),
            'state_id' => array(
                'name' => $this->language->text('State'),
                'required' => 0, 'weight' => 1,
                'status' => 1
            ),
            'city_id' => array(
                'name' => $this->language->text('City'),
                'required' => 1,
                'weight' => 2,
                'status' => 1
            ),
            'address_1' => array(
                'name' => $this->language->text('Address'),
                'required' => 1,
                'weight' => 3,
                'status' => 1
            ),
            'address_2' => array(
                'name' => $this->language->text('Additional address'),
                'required' => 0,
                'weight' => 4,
                'status' => 0
            ),
            'phone' => array(
                'name' => $this->language->text('Phone'),
                'required' => 1,
                'weight' => 5,
                'status' => 1
            ),
            'postcode' => array(
                'name' => $this->language->text('Post code'),
                'required' => 1,
                'weight' => 6,
                'status' => 1
            ),
            'first_name' => array(
                'name' => $this->language->text('First name'),
                'required' => 1,
                'weight' => 7,
                'status' => 1
            ),
            'middle_name' => array(
                'name' => $this->language->text('Middle name'),
                'required' => 1,
                'weight' => 8,
                'status' => 1
            ),
            'last_name' => array(
                'name' => $this->language->text('Last name'),
                'required' => 1,
                'weight' => 9,
                'status' => 1
            ),
            'company' => array(
                'name' => $this->language->text('Company'),
                'required' => 0,
                'weight' => 10,
                'status' => 0
            ),
            'fax' => array(
                'name' => $this->language->text('Fax'),
                'required' => 0,
                'weight' => 11,
                'status' => 0
            ),
        );
    }

    /**
     * Returns a default country code
     * @return string
     */
    public function getDefault()
    {
        return $this->config->get('country', '');
    }
    
    /**
     * Whether a country is default
     * @param string $country_code
     * @return boolean
     */
    public function isDefault($country_code)
    {
        return ($country_code === $this->getDefault());
    }

    /**
     * Adds a country
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.country.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'format' => !empty($data['format']) ? serialize($data['format']) : serialize(array()),
            'status' => !empty($data['status']),
            'weight' => !empty($data['weight']) ? (int) $data['weight'] : 0,
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'code' => $data['code'],
        );

        if (!empty($data['default'])) {
            $this->config->set('country', $data['code']);
        }

        $country_id = $this->db->insert('country', $values);
        $this->hook->fire('add.country.after', $data, $country_id);
        return $country_id;
    }

    /**
     * Updates a country
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $this->hook->fire('update.country.before', $code, $data);

        if (empty($code)) {
            return false;
        }

        $values = array();

        if (!empty($data['format'])) {
            $values['format'] = serialize((array) $data['format']);
        }

        if (!empty($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (!empty($data['native_name'])) {
            $values['native_name'] = $data['native_name'];
        }

        if (isset($data['status'])) {
            
            if($this->isDefault($code)) {
                $data['status'] = 1;
            }
            
            $values['status'] = (int) $data['status'];
        }

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (!empty($data['default'])) {
            $this->config->set('country', $code);
        }

        $result = false;

        if ($values) {
            $result = $this->db->update('country', $values, array('code' => $code));
            $this->hook->fire('update.country.after', $code, $data, $result);
        }

        return (bool) $result;
    }

    /**
     * Deletes a country
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $this->hook->fire('delete.country.before', $code);

        if (empty($code)) {
            return false;
        }

        if (!$this->canDelete($code)) {
            return false;
        }

        $this->db->delete('country', array('code' => $code));
        $this->db->delete('zone', array('country' => $code));
        $this->db->delete('state', array('country' => $code));
        $this->db->delete('city', array('country' => $code));

        $this->hook->fire('delete.country.after', $code);

        return true;
    }

    /**
     * Returns true if a country can be deleted
     * @param string $code
     * @return boolean
     */
    public function canDelete($code)
    {
        $sth = $this->db->prepare('SELECT address_id FROM address WHERE country=:country');
        $sth->execute(array(':country' => $code));
        return !$sth->fetchColumn();
    }

    /**
     * Returns an array of country names
     * @param boolean $enabled
     * @return array
     */
    public function getNames($enabled = false)
    {
        $names = array();
        foreach ($this->getList(array('status' => $enabled)) as $code => $country) {
            $names[$code] = $country['native_name'];
        }

        return $names;
    }

    /**
     * Returns an array of countries
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        
        $list = &Cache::memory('countries.' . md5(serialize($data)));
        
        if(isset($list)){
            return $list;
        }
        
        $list = array();
        
        $sql = 'SELECT * ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(code) ';
        }

        $sql .= 'FROM country WHERE LENGTH(code) > 0';

        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['native_name'])) {
            $sql .= ' AND native_name LIKE ?';
            $where[] = "%{$data['native_name']}%";
        }

        if (isset($data['code'])) {
            $sql .= ' AND code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc')))) {

            switch ($data['sort']) {
                case 'name':
                    $sql .= " ORDER BY name {$data['order']}";
                    break;
                case 'native_name':
                    $sql .= " ORDER BY native_name {$data['order']}";
                    break;
                case 'code':
                    $sql .= " ORDER BY code {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY status {$data['order']}";
                    break;
                case 'weight':
                    $sql .= " ORDER BY weight {$data['order']}";
                    break;
            }
        } else {
            $sql .= ' ORDER BY weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }
        
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $country) {
            $country['format'] = unserialize($country['format']);
            $list[$country['code']] = $country;
            $list[$country['code']]['format'] += $this->defaultFormat();
        }

        $this->hook->fire('countries', $list);
        return $list;
    }

    /**
     * Returns an array of world countries
     * @param boolean $sort_name
     * @return array
     */
    public function countries($sort_name = false)
    {
        $countries = array(
            'AC' => array('name' => 'Ascension Island', 'native_name' => ''),
            'AD' => array('name' => 'Andorra', 'native_name' => ''),
            'AE' => array('name' => 'United Arab Emirates', 'native_name' => '‫الإمارات العربية المتحدة'),
            'AF' => array('name' => 'Afghanistan', 'native_name' => '‫افغانستان'),
            'AG' => array('name' => 'Antigua and Barbuda', 'native_name' => ''),
            'AI' => array('name' => 'Anguilla', 'native_name' => ''),
            'AL' => array('name' => 'Albania', 'native_name' => 'Shqipëri'),
            'AM' => array('name' => 'Armenia', 'native_name' => 'Հայաստան'),
            'AO' => array('name' => 'Angola', 'native_name' => ''),
            'AQ' => array('name' => 'Antarctica', 'native_name' => ''),
            'AR' => array('name' => 'Argentina', 'native_name' => ''),
            'AS' => array('name' => 'American Samoa', 'native_name' => ''),
            'AT' => array('name' => 'Austria', 'native_name' => 'Österreich'),
            'AU' => array('name' => 'Australia', 'native_name' => ''),
            'AW' => array('name' => 'Aruba', 'native_name' => ''),
            'AX' => array('name' => 'Aland Islands', 'native_name' => 'Åland'),
            'AZ' => array('name' => 'Azerbaijan', 'native_name' => 'Azərbaycan'),
            'BA' => array('name' => 'Bosnia and Herzegovina', 'native_name' => 'Босна и Херцеговина'),
            'BB' => array('name' => 'Barbados', 'native_name' => ''),
            'BD' => array('name' => 'Bangladesh', 'native_name' => 'বাংলাদেশ'),
            'BE' => array('name' => 'Belgium', 'native_name' => 'België'),
            'BF' => array('name' => 'Burkina Faso', 'native_name' => ''),
            'BG' => array('name' => 'Bulgaria', 'native_name' => 'България'),
            'BH' => array('name' => 'Bahrain', 'native_name' => '‫البحرين'),
            'BI' => array('name' => 'Burundi', 'native_name' => 'Uburundi'),
            'BJ' => array('name' => 'Benin', 'native_name' => 'Bénin'),
            'BL' => array('name' => 'Saint Barthélemy', 'native_name' => 'Saint-Barthélemy'),
            'BM' => array('name' => 'Bermuda', 'native_name' => ''),
            'BN' => array('name' => 'Brunei', 'native_name' => ''),
            'BO' => array('name' => 'Bolivia', 'native_name' => ''),
            'BQ' => array('name' => 'Caribbean Netherlands', 'native_name' => ''),
            'BR' => array('name' => 'Brazil', 'native_name' => 'Brasil'),
            'BS' => array('name' => 'Bahamas', 'native_name' => ''),
            'BT' => array('name' => 'Bhutan', 'native_name' => 'འབྲུག'),
            'BV' => array('name' => 'Bouvet Island', 'native_name' => ''),
            'BW' => array('name' => 'Botswana', 'native_name' => ''),
            'BY' => array('name' => 'Belarus', 'native_name' => 'Беларусь'),
            'BZ' => array('name' => 'Belize', 'native_name' => ''),
            'CA' => array('name' => 'Canada', 'native_name' => ''),
            'CC' => array('name' => 'Cocos (Keeling) Islands', 'native_name' => 'Kepulauan Cocos (Keeling)'),
            'CD' => array('name' => 'Congo (DRC)', 'native_name' => 'Jamhuri ya Kidemokrasia ya Kongo'),
            'CF' => array('name' => 'Central African Republic', 'native_name' => 'République centrafricaine'),
            'CG' => array('name' => 'Congo (Republic)', 'native_name' => 'Congo-Brazzaville'),
            'CH' => array('name' => 'Switzerland', 'native_name' => 'Schweiz'),
            'CI' => array('name' => 'Ivory Coast', 'native_name' => ''),
            'CK' => array('name' => 'Cook Islands', 'native_name' => ''),
            'CL' => array('name' => 'Chile', 'native_name' => ''),
            'CM' => array('name' => 'Cameroon', 'native_name' => 'Cameroun'),
            'CN' => array('name' => 'China', 'native_name' => '中国'),
            'CO' => array('name' => 'Colombia', 'native_name' => ''),
            'CP' => array('name' => 'Clipperton Island', 'native_name' => ''),
            'CR' => array('name' => 'Costa Rica', 'native_name' => ''),
            'CU' => array('name' => 'Cuba', 'native_name' => ''),
            'CV' => array('name' => 'Cape Verde', 'native_name' => 'Kabu Verdi'),
            'CW' => array('name' => 'Curaçao', 'native_name' => ''),
            'CX' => array('name' => 'Christmas Island', 'native_name' => ''),
            'CY' => array('name' => 'Cyprus', 'native_name' => 'Κύπρος'),
            'CZ' => array('name' => 'Czech Republic', 'native_name' => 'Česká republika'),
            'DE' => array('name' => 'Germany', 'native_name' => 'Deutschland'),
            'DG' => array('name' => 'Diego Garcia', 'native_name' => ''),
            'DJ' => array('name' => 'Djibouti', 'native_name' => ''),
            'DK' => array('name' => 'Denmark', 'native_name' => 'Danmark'),
            'DM' => array('name' => 'Dominica', 'native_name' => ''),
            'DO' => array('name' => 'Dominican Republic', 'native_name' => 'República Dominicana'),
            'DZ' => array('name' => 'Algeria', 'native_name' => '‫الجزائر'),
            'EA' => array('name' => 'Ceuta and Melilla', 'native_name' => 'Ceuta y Melilla'),
            'EC' => array('name' => 'Ecuador', 'native_name' => ''),
            'EE' => array('name' => 'Estonia', 'native_name' => 'Eesti'),
            'EG' => array('name' => 'Egypt', 'native_name' => '‫مصر'),
            'EH' => array('name' => 'Western Sahara', 'native_name' => '‫الصحراء الغربية'),
            'ER' => array('name' => 'Eritrea', 'native_name' => ''),
            'ES' => array('name' => 'Spain', 'native_name' => 'España'),
            'ET' => array('name' => 'Ethiopia', 'native_name' => ''),
            'FI' => array('name' => 'Finland', 'native_name' => 'Suomi'),
            'FJ' => array('name' => 'Fiji', 'native_name' => ''),
            'FK' => array('name' => 'Falkland Islands', 'native_name' => 'Islas Malvinas'),
            'FM' => array('name' => 'Micronesia', 'native_name' => ''),
            'FO' => array('name' => 'Faroe Islands', 'native_name' => 'Føroyar'),
            'FR' => array('name' => 'France', 'native_name' => ''),
            'GA' => array('name' => 'Gabon', 'native_name' => ''),
            'GB' => array('name' => 'United Kingdom', 'native_name' => ''),
            'GD' => array('name' => 'Grenada', 'native_name' => ''),
            'GE' => array('name' => 'Georgia', 'native_name' => 'საქართველო'),
            'GF' => array('name' => 'French Guiana', 'native_name' => 'Guyane française'),
            'GG' => array('name' => 'Guernsey', 'native_name' => ''),
            'GH' => array('name' => 'Ghana', 'native_name' => 'Gaana'),
            'GI' => array('name' => 'Gibraltar', 'native_name' => ''),
            'GL' => array('name' => 'Greenland', 'native_name' => 'Kalaallit Nunaat'),
            'GM' => array('name' => 'Gambia', 'native_name' => ''),
            'GN' => array('name' => 'Guinea', 'native_name' => 'Guinée'),
            'GP' => array('name' => 'Guadeloupe', 'native_name' => ''),
            'GQ' => array('name' => 'Equatorial Guinea', 'native_name' => 'Guinea Ecuatorial'),
            'GR' => array('name' => 'Greece', 'native_name' => 'Ελλάδα'),
            'GS' => array('name' => 'South Georgia & South Sandwich Islands', 'native_name' => ''),
            'GT' => array('name' => 'Guatemala', 'native_name' => ''),
            'GU' => array('name' => 'Guam', 'native_name' => ''),
            'GW' => array('name' => 'Guinea-Bissau', 'native_name' => 'Guiné Bissau'),
            'GY' => array('name' => 'Guyana', 'native_name' => ''),
            'HK' => array('name' => 'Hong Kong', 'native_name' => '香港'),
            'HM' => array('name' => 'Heard & McDonald Islands', 'native_name' => ''),
            'HN' => array('name' => 'Honduras', 'native_name' => ''),
            'HR' => array('name' => 'Croatia', 'native_name' => 'Hrvatska'),
            'HT' => array('name' => 'Haiti', 'native_name' => ''),
            'HU' => array('name' => 'Hungary', 'native_name' => 'Magyarország'),
            'IC' => array('name' => 'Canary Islands', 'native_name' => 'islas Canarias'),
            'ID' => array('name' => 'Indonesia', 'native_name' => ''),
            'IE' => array('name' => 'Ireland', 'native_name' => ''),
            'IL' => array('name' => 'Israel', 'native_name' => '‫ישראל'),
            'IM' => array('name' => 'Isle of Man', 'native_name' => ''),
            'IN' => array('name' => 'India', 'native_name' => 'भारत'),
            'IO' => array('name' => 'British Indian Ocean Territory', 'native_name' => ''),
            'IQ' => array('name' => 'Iraq', 'native_name' => '‫العراق'),
            'IR' => array('name' => 'Iran', 'native_name' => '‫ایران'),
            'IS' => array('name' => 'Iceland', 'native_name' => 'Ísland'),
            'IT' => array('name' => 'Italy', 'native_name' => 'Italia'),
            'JE' => array('name' => 'Jersey', 'native_name' => ''),
            'JM' => array('name' => 'Jamaica', 'native_name' => ''),
            'JO' => array('name' => 'Jordan', 'native_name' => '‫الأردن'),
            'JP' => array('name' => 'Japan', 'native_name' => '日本'),
            'KE' => array('name' => 'Kenya', 'native_name' => ''),
            'KG' => array('name' => 'Kyrgyzstan', 'native_name' => 'Кыргызстан'),
            'KH' => array('name' => 'Cambodia', 'native_name' => 'កម្ពុជា'),
            'KI' => array('name' => 'Kiribati', 'native_name' => ''),
            'KM' => array('name' => 'Comoros', 'native_name' => '‫جزر القمر'),
            'KN' => array('name' => 'Saint Kitts and Nevis', 'native_name' => ''),
            'KP' => array('name' => 'North Korea', 'native_name' => '조선 민주주의 인민 공화국'),
            'KR' => array('name' => 'South Korea', 'native_name' => '대한민국'),
            'KW' => array('name' => 'Kuwait', 'native_name' => '‫الكويت'),
            'KY' => array('name' => 'Cayman Islands', 'native_name' => ''),
            'KZ' => array('name' => 'Kazakhstan', 'native_name' => 'Казахстан'),
            'LA' => array('name' => 'Laos', 'native_name' => 'ລາວ'),
            'LB' => array('name' => 'Lebanon', 'native_name' => '‫لبنان'),
            'LC' => array('name' => 'Saint Lucia', 'native_name' => ''),
            'LI' => array('name' => 'Liechtenstein', 'native_name' => ''),
            'LK' => array('name' => 'Sri Lanka', 'native_name' => 'ශ්‍රී ලංකාව'),
            'LR' => array('name' => 'Liberia', 'native_name' => ''),
            'LS' => array('name' => 'Lesotho', 'native_name' => ''),
            'LT' => array('name' => 'Lithuania', 'native_name' => 'Lietuva'),
            'LU' => array('name' => 'Luxembourg', 'native_name' => ''),
            'LV' => array('name' => 'Latvia', 'native_name' => 'Latvija'),
            'LY' => array('name' => 'Libya', 'native_name' => '‫ليبيا'),
            'MA' => array('name' => 'Morocco', 'native_name' => '‫المغرب'),
            'MC' => array('name' => 'Monaco', 'native_name' => ''),
            'MD' => array('name' => 'Moldova', 'native_name' => 'Republica Moldova'),
            'ME' => array('name' => 'Montenegro', 'native_name' => 'Crna Gora'),
            'MF' => array('name' => 'Saint Martin', 'native_name' => ''),
            'MG' => array('name' => 'Madagascar', 'native_name' => 'Madagasikara'),
            'MH' => array('name' => 'Marshall Islands', 'native_name' => ''),
            'MK' => array('name' => 'Macedonia (FYROM)', 'native_name' => 'Македонија'),
            'ML' => array('name' => 'Mali', 'native_name' => ''),
            'MM' => array('name' => 'Myanmar (Burma)', 'native_name' => ''),
            'MN' => array('name' => 'Mongolia', 'native_name' => 'Монгол'),
            'MO' => array('name' => 'Macau', 'native_name' => '澳門'),
            'MP' => array('name' => 'Northern Mariana Islands', 'native_name' => ''),
            'MQ' => array('name' => 'Martinique', 'native_name' => ''),
            'MR' => array('name' => 'Mauritania', 'native_name' => '‫موريتانيا'),
            'MS' => array('name' => 'Montserrat', 'native_name' => ''),
            'MT' => array('name' => 'Malta', 'native_name' => ''),
            'MU' => array('name' => 'Mauritius', 'native_name' => 'Moris'),
            'MV' => array('name' => 'Maldives', 'native_name' => ''),
            'MW' => array('name' => 'Malawi', 'native_name' => ''),
            'MX' => array('name' => 'Mexico', 'native_name' => ''),
            'MY' => array('name' => 'Malaysia', 'native_name' => ''),
            'MZ' => array('name' => 'Mozambique', 'native_name' => 'Moçambique'),
            'NA' => array('name' => 'Namibia', 'native_name' => 'Namibië'),
            'NC' => array('name' => 'New Caledonia', 'native_name' => 'Nouvelle-Calédonie'),
            'NE' => array('name' => 'Niger', 'native_name' => 'Nijar'),
            'NF' => array('name' => 'Norfolk Island', 'native_name' => ''),
            'NG' => array('name' => 'Nigeria', 'native_name' => ''),
            'NI' => array('name' => 'Nicaragua', 'native_name' => ''),
            'NL' => array('name' => 'Netherlands', 'native_name' => 'Nederland'),
            'NO' => array('name' => 'Norway', 'native_name' => 'Norge'),
            'NP' => array('name' => 'Nepal', 'native_name' => 'नेपाल'),
            'NR' => array('name' => 'Nauru', 'native_name' => ''),
            'NU' => array('name' => 'Niue', 'native_name' => ''),
            'NZ' => array('name' => 'New Zealand', 'native_name' => ''),
            'OM' => array('name' => 'Oman', 'native_name' => '‫عُمان'),
            'PA' => array('name' => 'Panama', 'native_name' => ''),
            'PE' => array('name' => 'Peru', 'native_name' => 'Perú'),
            'PF' => array('name' => 'French Polynesia', 'native_name' => 'Polynésie française'),
            'PG' => array('name' => 'Papua New Guinea', 'native_name' => ''),
            'PH' => array('name' => 'Philippines', 'native_name' => ''),
            'PK' => array('name' => 'Pakistan', 'native_name' => '‫پاکستان'),
            'PL' => array('name' => 'Poland', 'native_name' => 'Polska'),
            'PM' => array('name' => 'Saint Pierre and Miquelon', 'native_name' => 'Saint-Pierre-et-Miquelon'),
            'PN' => array('name' => 'Pitcairn Islands', 'native_name' => ''),
            'PR' => array('name' => 'Puerto Rico', 'native_name' => ''),
            'PS' => array('name' => 'Palestine', 'native_name' => '‫فلسطين'),
            'PT' => array('name' => 'Portugal', 'native_name' => ''),
            'PW' => array('name' => 'Palau', 'native_name' => ''),
            'PY' => array('name' => 'Paraguay', 'native_name' => ''),
            'QA' => array('name' => 'Qatar', 'native_name' => '‫قطر'),
            'RE' => array('name' => 'Réunion', 'native_name' => 'La Réunion'),
            'RO' => array('name' => 'Romania', 'native_name' => 'România'),
            'RS' => array('name' => 'Serbia', 'native_name' => 'Србија'),
            'RU' => array('name' => 'Russia', 'native_name' => 'Россия'),
            'RW' => array('name' => 'Rwanda', 'native_name' => ''),
            'SA' => array('name' => 'Saudi Arabia', 'native_name' => '‫المملكة العربية السعودية'),
            'SB' => array('name' => 'Solomon Islands', 'native_name' => ''),
            'SC' => array('name' => 'Seychelles', 'native_name' => ''),
            'SD' => array('name' => 'Sudan', 'native_name' => '‫السودان'),
            'SE' => array('name' => 'Sweden', 'native_name' => 'Sverige'),
            'SG' => array('name' => 'Singapore', 'native_name' => ''),
            'SH' => array('name' => 'Saint Helena', 'native_name' => ''),
            'SI' => array('name' => 'Slovenia', 'native_name' => 'Slovenija'),
            'SJ' => array('name' => 'Svalbard and Jan Mayen', 'native_name' => 'Svalbard og Jan Mayen'),
            'SK' => array('name' => 'Slovakia', 'native_name' => 'Slovensko'),
            'SL' => array('name' => 'Sierra Leone', 'native_name' => ''),
            'SM' => array('name' => 'San Marino', 'native_name' => ''),
            'SN' => array('name' => 'Senegal', 'native_name' => 'Sénégal'),
            'SO' => array('name' => 'Somalia', 'native_name' => 'Soomaaliya'),
            'SR' => array('name' => 'Suriname', 'native_name' => ''),
            'SS' => array('name' => 'South Sudan', 'native_name' => '‫جنوب السودان'),
            'ST' => array('name' => 'Sao Tome and Principe', 'native_name' => 'São Tomé e Príncipe'),
            'SV' => array('name' => 'El Salvador', 'native_name' => ''),
            'SX' => array('name' => 'Sint Maarten', 'native_name' => ''),
            'SY' => array('name' => 'Syria', 'native_name' => '‫سوريا'),
            'SZ' => array('name' => 'Swaziland', 'native_name' => ''),
            'TA' => array('name' => 'Tristan da Cunha', 'native_name' => ''),
            'TC' => array('name' => 'Turks and Caicos Islands', 'native_name' => ''),
            'TD' => array('name' => 'Chad', 'native_name' => 'Tchad'),
            'TF' => array('name' => 'French Southern Territories', 'native_name' => 'Terres australes françaises'),
            'TG' => array('name' => 'Togo', 'native_name' => ''),
            'TH' => array('name' => 'Thailand', 'native_name' => 'ไทย'),
            'TJ' => array('name' => 'Tajikistan', 'native_name' => ''),
            'TK' => array('name' => 'Tokelau', 'native_name' => ''),
            'TL' => array('name' => 'Timor-Leste', 'native_name' => ''),
            'TM' => array('name' => 'Turkmenistan', 'native_name' => ''),
            'TN' => array('name' => 'Tunisia', 'native_name' => '‫تونس'),
            'TO' => array('name' => 'Tonga', 'native_name' => ''),
            'TR' => array('name' => 'Turkey', 'native_name' => 'Türkiye'),
            'TT' => array('name' => 'Trinidad and Tobago', 'native_name' => ''),
            'TV' => array('name' => 'Tuvalu', 'native_name' => ''),
            'TW' => array('name' => 'Taiwan', 'native_name' => '台灣'),
            'TZ' => array('name' => 'Tanzania', 'native_name' => ''),
            'UA' => array('name' => 'Ukraine', 'native_name' => 'Україна'),
            'UG' => array('name' => 'Uganda', 'native_name' => ''),
            'UM' => array('name' => 'U.S. Outlying Islands', 'native_name' => ''),
            'US' => array('name' => 'United States', 'native_name' => ''),
            'UY' => array('name' => 'Uruguay', 'native_name' => ''),
            'UZ' => array('name' => 'Uzbekistan', 'native_name' => 'Oʻzbekiston'),
            'VA' => array('name' => 'Vatican City', 'native_name' => 'Città del Vaticano'),
            'VC' => array('name' => 'St. Vincent & Grenadines', 'native_name' => ''),
            'VE' => array('name' => 'Venezuela', 'native_name' => ''),
            'VG' => array('name' => 'British Virgin Islands', 'native_name' => ''),
            'VI' => array('name' => 'U.S. Virgin Islands', 'native_name' => ''),
            'VN' => array('name' => 'Vietnam', 'native_name' => 'Việt Nam'),
            'VU' => array('name' => 'Vanuatu', 'native_name' => ''),
            'WF' => array('name' => 'Wallis and Futuna', 'native_name' => ''),
            'WS' => array('name' => 'Samoa', 'native_name' => ''),
            'XK' => array('name' => 'Kosovo', 'native_name' => 'Kosovë'),
            'YE' => array('name' => 'Yemen', 'native_name' => '‫اليمن'),
            'YT' => array('name' => 'Mayotte', 'native_name' => ''),
            'ZA' => array('name' => 'South Africa', 'native_name' => ''),
            'ZM' => array('name' => 'Zambia', 'native_name' => ''),
            'ZW' => array('name' => 'Zimbabwe', 'native_name' => '')
        );

        if ($sort_name) {
            uasort($countries, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        } else {
            ksort($countries);
        }

        return $countries;
    }

}
