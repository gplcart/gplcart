<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Contains methods to directed acyclic graph manipulation
 */
class Graph
{

    /**
     * A three dimensional associated array, with the first keys being the names
     * of the vertices, these can be strings or numbers. The second key is
     * 'edges' and the third one are again vertices, each such key representing
     * an edge. Values of array elements are copied over.
     * @var array
     */
    protected $graph = array();

    /**
     * An associative array. The key 'last_visit_order' stores a list of the
     * vertices visited. The key components stores list of vertices belonging
     * to the same the component.
     * @var array
     */
    protected $state = array();

    /**
     * The component of the last vertex.
     * @var string
     */
    protected $component;

    /**
     * Determines which components require and are required by each component
     * @param array $items
     * @return array
     */
    public function build(array $items)
    {
        $this->graph = array(); // Reset all previous data

        foreach ($items as $id => $item) {

            $this->graph[$id]['edges'] = array();

            if (empty($item['dependencies'])) {
                continue;
            }

            foreach ($item['dependencies'] as $did => $ddata) {
                $this->graph[$id]['edges'][$did] = $ddata;
            }
        }

        $this->search();

        foreach ($this->graph as $id => $data) {
            $items[$id]['required_by'] = isset($data['reverse_paths']) ? $data['reverse_paths'] : array();
            $items[$id]['requires'] = isset($data['paths']) ? $data['paths'] : array();
            $items[$id]['sort'] = $data['weight'];
        }

        return $items;
    }

    /**
     * Sort a list of IDs according to their dependencies
     * Dependend items always go last
     * @param array $ids
     * @param array $list
     * @return array
     */
    public function sort(array $ids, array $list)
    {
        $data = array_flip(array_values($ids));

        while (list($key) = each($data)) {

            if (!isset($list[$key])) {
                return array();
            }

            $data[$key] = $list[$key]['sort'];

            foreach (array_keys($list[$key]['requires']) as $dependency) {
                if (!isset($data[$dependency])) {
                    $data[$dependency] = 0;
                }
            }
        }

        arsort($data);
        return array_keys($data);
    }

    /**
     * Performs a depth-first search and sort on a directed acyclic graph
     */
    protected function search()
    {
        $this->state = array(
            'last_visit_order' => array(),
            'components' => array(),
        );

        foreach ($this->graph as $start => $data) {
            $this->searchComponent($start);
        }

        $component_weights = array();

        foreach ($this->state['last_visit_order'] as $vertex) {
            $component = $this->graph[$vertex]['component'];
            if (!isset($component_weights[$component])) {
                $component_weights[$component] = 0;
            }
            $this->graph[$vertex]['weight'] = $component_weights[$component] --;
        }
    }

    /**
     * Performs a depth-first search on a graph
     * @param string $start
     * @return null
     */
    protected function searchComponent($start)
    {
        if (!isset($this->component)) {
            $this->component = $start;
        }

        if (isset($this->graph[$start]['paths'])) {
            return null;
        }

        $this->graph[$start]['paths'] = array();
        $this->graph[$start]['component'] = $this->component;
        $this->state['components'][$this->component][] = $start;

        if (isset($this->graph[$start]['edges'])) {
            foreach ($this->graph[$start]['edges'] as $end => $v) {
                $this->graph[$start]['paths'][$end] = $v;
                if (isset($this->graph[$end]['component']) && $this->component != $this->graph[$end]['component']) {
                    $new_component = $this->graph[$end]['component'];
                    foreach ($this->state['components'][$this->component] as $vertex) {
                        $this->graph[$vertex]['component'] = $new_component;
                        $this->state['components'][$new_component][] = $vertex;
                    }
                    unset($this->state['components'][$this->component]);
                    $this->component = $new_component;
                }

                if (isset($this->graph[$end])) {
                    $this->searchComponent($end);
                    $this->graph[$start]['paths'] += $this->graph[$end]['paths'];
                }
            }
        }

        foreach ($this->graph[$start]['paths'] as $end => $v) {
            if (isset($this->graph[$end])) {
                $this->graph[$end]['reverse_paths'][$start] = $v;
            }
        }

        $this->state['last_visit_order'][] = $start;
        return null;
    }

}
