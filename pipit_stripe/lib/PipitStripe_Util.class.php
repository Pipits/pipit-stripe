<?php

class PipitStripe_Util {
    
    /**
     * 
     */
    public function template($default_opts = [], $opts = [], $data = [], $return_html = false) {
        $opts = array_merge($default_opts, $opts);
        $render_html = true;
        $return = false;
        $html = '';

        if (isset($opts['skip-template']) && $opts['skip-template']==true) {
            $return = true;
            $render_html = false;

            if (isset($opts['return-html'])&& $opts['return-html']==true) {
                $render_html = true;
            }
        }


        if($render_html) {
            $API  = new PerchAPI(1.0, 'pipit_stripe');
            $Template = $API->get('Template');
            $Template->set('stripe/' . $opts['template'], 'stripe');

            if (!PerchUtil::is_assoc($data)) {
                $html = $Template->render_group($data, true);
            } else {
                $html = $Template->render($data, true);
            }

            // layout includes, forms, etc
            $html = $Template->apply_runtime_post_processing($html);

            if ($return && $render_html) {
                $data['html'] = $html;
            }
        }


        if($return_html) return $html;
        if($return) return $data;
        echo $html;
        PerchUtil::flush_output();
    }


}