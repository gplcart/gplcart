<?php

    /*
     * 
     * 

            $combinations = $this->product->getCombinations($product_id);

            if (!$combinations) {
                continue;
            }

            foreach ($combinations as $combination_id => $combination) {

                $title = array();
                foreach ($combination['fields'] as $field_value_id) {
                    $field_value = $this->field_value->get($field_value_id);
                    if ($field_value) {
                        $title[] = $field_value['title'];
                    }
                }

                $combination['title'] = implode('/', $title);
                $combination['product_id'] = $combination_id;
                $combination['currency'] = $product['currency'];

                $fields = $this->getFields($options['mapping'], $combination);

                if (isset($fields['price'])) {
                    $fields['price'] = $this->price->decimal($fields['price'], $product['currency']);
                }

                Tool::writeCsv($options['file'], $fields);
            }
     * 
     * 
     * 
     * 
     */
