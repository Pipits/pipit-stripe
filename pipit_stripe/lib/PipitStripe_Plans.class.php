<?php

class PipitStripe_Plans {
    public $file_path;
    public $dir;

    function __construct() {
        $this->dir = PERCH_PATH . '/pipit_stripe';
        $this->file_path = PerchUtil::file_path($this->dir . '/plans.json');
    }
    
    


    /**
     * Get all plans from cached file if exists, otherwise directly from Stripe
     * 
     * @return array|boolean
     */
    public function get(){
        if(file_exists($this->file_path)) {
            PerchUtil::debug('Getting plans from cached file');

            $json = file_get_contents($this->file_path);
            $plans = json_decode($json, 1);
            if($plans === NULL) {
                PerchUtil::debug('Could not read cache file', 'notice');
            }
        } 
        
        if(!file_exists($this->file_path) || !$plans) {
            $plans = $this->get_from_stripe();
        }


        if($plans) {
            array_walk($plans, function(&$item){
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

                if(isset($item['amount'])) {
                    $item['amount_formatted'] = $item['amount'] / 100;
                }
    
            });
        }



        if($plans) return $plans;
        return false;
    }




    /**
     * Get all plans from Stripe API
     * 
     * @return array|boolean
     */
    public function get_from_stripe($count = 100, $return_cache_result=false) {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        PerchUtil::debug('Getting plans from Stripe API');
        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);
        $Plans = \Stripe\Plan::all(['limit' => $count]);

        
        if(isset($Plans->data)) {
            //update cache
            $cache_result = $this->update_cache($Plans->data);
            if($return_cache_result) return $cache_result;

            return $Plans->data;
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
        $result['message'] = 'Plans saved.';
        return $result;
    }




    /**
     * Get a single plan
     * 
     * @param string $planID
     * @return array|boolean
     */
    public function get_plan($planID) {
        $plans = $this->get();
        
        $plans = array_filter($plans, function($item) use($planID) {
            if($item['id'] == $planID) return $item;
        });

        return $plans;
    }



    /**
     * Get plans for a single product
     * @param string $productID
     * @return array|boolean
     */
    public function get_plans_for($productID) {
        $plans = $this->get();

        $plans = array_filter($plans, function($item) use($productID) {
            if($item['product'] == $productID) return $item;
        });

        return $plans;
    }

}