<?php

class PipitStripe_Products {
    public $file_path;
    public $dir;

    function __construct() {
        $this->dir = PERCH_PATH . '/pipit_stripe';
        $this->file_path = PerchUtil::file_path($this->dir . '/products.json');
    }
    
    



    /**
     * Get all products from cached file if exists, otherwise from Stripe API
     * @return array|boolean
     */
    public function get(){
        if(file_exists($this->file_path)) {
            PerchUtil::debug('Getting products from cached file');

            $json = file_get_contents($this->file_path);
            $products = json_decode($json, 1);
            if($products === NULL) {
                PerchUtil::debug('Could not read cache file', 'notice');
            }
        } 
        
        if(!file_exists($this->file_path) || !$products) {
            $products = $this->get_from_stripe();
        }


        array_walk($products, function(&$item){
            if(isset($item['created'])) {
                $item['created'] = date('Y-m-d H:i:s', $item['created']);
            }

            if(isset($item['updated'])) {
                $item['updated'] = date('Y-m-d H:i:s', $item['updated']);
            }

            if(isset($item['metadata'])) {
                // create meta_*
                foreach($item['metadata'] as $key => $val) {
                    $item['meta_' . PerchUtil::urlify($key, '_')] = $val;
                }

                // Perch repeater friendly array
                array_walk($item['metadata'], function(&$val, $key) {
                    $val = [
                        'label' => $key,
                        'value' => $val
                    ];
                });

                $item['metadata'] = array_values($item['metadata']);
            }

        });


        if($products) return $products;
        return false;
    }





    /**
     * Get a single product
     * @param string $productID
     * @return array|boolean
     */
    public function get_product($productID) {
        $products = $this->get();
        
        $products = array_filter($products, function($item) use($productID) {
            if($item['id'] == $productID) return $item;
        });

        $products = array_values($products);
        # PerchUtil::debug($products);

        if(isset($products[0])) return $products[0];
        return false;
    }






    /**
     * Get products from Stripe API
     * @param int $count
     * @return array|boolean
     */
    public function get_from_stripe($count = 100, $return_cache_result=false) {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        PerchUtil::debug('Getting products from Stripe API');
        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);
        $Products = \Stripe\Product::all(['limit' => $count]);

        
        if(isset($Products->data)) {
            //update cache
            $cache_result = $this->update_cache($Products->data);
            if($return_cache_result) return $cache_result;

            return $Products->data;
        }
        return false;
    }





    /**
     * Update JSON file in perch/pipit_stripe
     * @param array $data
     */
    function update_cache($data) {
        if(!is_dir($this->dir)) {
            if(!mkdir($this->dir)) {
                $result['result'] = 'FAILED';
                $result['message'] = 'Could not create directory ' . $this->dir;
                return $result;
            }
        }


        // save to JSON file
        $data_json = json_encode($data);
        if($data_json === NULL) {
            $result['result'] = 'FAILED';
            $result['message'] = 'Could not JSON encode data';
            return $result;
        }


        if(!file_put_contents($this->file_path, $data_json)) {
            $result['result'] = 'FAILED';
            $result['message'] = 'Could not write file ' . $this->file_path;
            return $result;
        }


        $result['result'] = 'OK';
        $result['message'] = 'Products saved.';
        return $result;
    }
}