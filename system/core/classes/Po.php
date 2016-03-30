<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Raúl Ferràs https://github.com/raulferras
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\classes;

class Po
{

    /**
     * Separators
     * @var array
     */
    protected $options = array(
        'multiline-glue' => '<##EOL##>',
        'context-glue' => '<##EOC##>'
    );

    /**
     * Convert .po file into array
     * @param string $file Path to .po file
     * @return mixed
     */
    public function read($file)
    {
        if (!($fd = fopen($file, "rb"))) {
            return false;
        }

        $count = 0;
        $previous = null;
        $state = null;
        $first = true;
        $new = false;
        $hash = array();
        $entry = array();

        while (!feof($fd)) {

            $line = trim($line = fgets($fd, 10 * 1024));

            $split = preg_split('/\s+/ ', $line, 2);
            $key = $split[0];

            if ($line === '' || ($key == 'msgid' && isset($entry['msgid']))) {

                if ($new) {
                    $count++;
                    continue;
                }

                if ($first) {
                    $first = false;
                    if (!$this->isHeader($entry)) {
                        $hash[] = $entry;
                    }
                } else {
                    $hash[] = $entry;
                }

                $previous = null;
                $state = null;
                $new = true;
                $entry = array();

                if ($line === '') {
                    $count++;
                    continue;
                }
            }

            $new = false;
            $data = isset($split[1]) ? $split[1] : null;

            switch ($key) {
                case 'msgctxt':
                case 'msgid':
                case 'msgid_plural':
                    $state = $key;
                    $entry[$state][] = $data;
                    break;
                case 'msgstr':
                    $state = 'msgstr';
                    $entry[$state][] = $data;
                    break;
                default:

                    if (0 === strpos($key, "#")) {
                        continue; // Strip comments
                    }

                    if (strpos($key, 'msgstr[') !== false) {
                        $state = $key;
                        $entry[$state][] = $data;
                    } else {
                        switch ($state) {
                            case 'msgctxt':
                            case 'msgid':
                            case 'msgid_plural':
                            case (strpos($state, 'msgstr[') !== false):

                                if (is_string($entry[$state])) {
                                    $entry[$state] = array($entry[$state]);
                                }

                                $entry[$state][] = $line;
                                break;
                            case 'msgstr':
                                if ($entry['msgid'] == "\"\"") {
                                    $entry['msgstr'][] = trim($line, '"');
                                } else {
                                    $entry['msgstr'][] = $line;
                                }
                                break;
                            default:
                                return false;
                        }
                    }
                    break;
            }

            $count++;
        }

        fclose($fd);

        if ($state == 'msgstr') {
            $hash[] = $entry;
        }

        $temp = $hash;
        $entries = array();
        
        foreach ($temp as $entry) {
            foreach ($entry as &$v) {
                $or = $v;
                $v = $this->clean($v);
                if ($v === false) {
                    return false;
                }
            }

            if (isset($entry['msgid']) && count(preg_grep('/^msgstr/', array_keys($entry)))) {
                $id = $this->getEntryId($entry);
                $entries[$id] = $entry;
            }
        }

        return $entries;
    }

    /**
     * Determine if entry is header
     * @param array $entry
     * @return boolean
     */
    protected function isHeader(array $entry)
    {

        if (empty($entry) || !isset($entry['msgstr'])) {
            return false;
        }

        $ids = array(
            'Project-Id-Version:' => false,
            'PO-Revision-Date:' => false,
            'MIME-Version:' => false,
        );

        $count = count($ids);
        $keys = array_keys($ids);
        $items = 0;

        foreach ($entry['msgstr'] as $str) {

            $tokens = explode(':', $str);
            $tokens[0] = trim($tokens[0], "\"") . ':';

            if (in_array($tokens[0], $keys)) {
                $items++;
                unset($ids[$tokens[0]]);
                $keys = array_keys($ids);
            }
        }

        return ($items == $count) ? true : false;
    }

    /**
     * Clean up string
     * @param mixed $x Input
     * @return string Output
     */
    protected function clean($x)
    {
        if (is_array($x)) {
            foreach ($x as $k => $v) {
                $x[$k] = $this->clean($v);
            }
        } else {

            if ($x == '') {
                return '';
            }

            if ($x[0] == '"') {
                $x = substr($x, 1, -1);
            }

            $x = stripcslashes($x);
        }

        return $x;
    }

    /**
     * 
     * @param array $entry
     * @return type
     */
    protected function getEntryId(array $entry)
    {
        if (isset($entry['msgctxt'])) {
            return implode($this->options['multiline-glue'], (array) $entry['msgctxt']) . $this->options['context-glue'] . implode($this->options['multiline-glue'], (array) $entry['msgid']);
        }

        return implode($this->options['multiline-glue'], (array) $entry['msgid']);
    }

}
