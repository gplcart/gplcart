<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Command line utilities
 */
class Cli
{

    /**
     * Parses command line arguments
     * Based on work by Patrick Fisher <patrick@pwfisher.com>
     * 
     * @param array|string $argv
     * @return array
     */
    public function parse($argv)
    {
        if (is_string($argv)) {
            $argv = array_map('trim', explode(' ', trim($argv)));
        }

        array_shift($argv);

        $out = array();
        for ($i = 0, $j = count($argv); $i < $j; $i++) {

            $key = null;

            $arg = $argv[$i];
            if (substr($arg, 0, 2) === '--') {

                $pos = strpos($arg, '=');

                if ($pos === false) {
                    $key = substr($arg, 2);
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i++;
                    } else {
                        $value = isset($out[$key]) ? $out[$key] : true;
                    }

                    $out[$key] = $value;
                    continue;
                }

                $key = substr($arg, 2, $pos - 2);
                $value = substr($arg, $pos + 1);
                $out[$key] = $value;
                continue;
            }

            if (substr($arg, 0, 1) === '-') {

                if (substr($arg, 2, 1) === '=') {
                    $key = substr($arg, 1, 1);
                    $value = substr($arg, 3);
                    $out[$key] = $value;
                    continue;
                }

                $chars = str_split(substr($arg, 1));

                foreach ($chars as $char) {
                    $key = $char;
                    $value = isset($out[$key]) ? $out[$key] : true;
                    $out[$key] = $value;
                }

                if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                    $out[$key] = $argv[$i + 1];
                    $i++;
                }

                continue;
            }

            $value = $arg;
            $out[] = $value;
        }

        return $out;
    }

    /**
     * Displays the progress bar
     * @param type $done
     * @param type $total
     */
    public function progress($done, $total)
    {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }

}
