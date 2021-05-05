<?php
   /*
   Plugin Name: Subito Widget
   Plugin URI: --
   description: Plugin per mostrare ads da subito.it
   Version: 0.9
   Author: Fausto Dassenno
   Author URI: --
   License: GPL2
   */
class Subito_Plugin {
 
    /**
     * Initializes the plugin.
     *
     * To keep the initialization fast, only add filter and action
     * hooks in the constructor.
     */
    public function __construct() {
        add_shortcode( 'subito_widget', array( $this, 'render_subito_widget' ) );
    }


    /**
     * Widget generation
     *
     * In the wp page insert the shortcode [subito_widget]
     * Parameters:
     * q [mandatory] query. No whitespaces, use + instead. e.g.: audi+a4 
     * l number of ads rendered
     * c category, e.g.: 16 ( Abbigliamento ed Accessori )
     */
    public function render_subito_widget( $attributes, $content = null ) {
        $query = $attributes['q'];
        $cat = $attributes['c'];
        $limit = $attributes['l'];
        //sanify q
        $query=$query.str_replace(" ","+");
        if($limit=='') $limit=5;
        $output="";
        $output.= "<div style='display:flex'>";
        $endpoint="https://hades.subito.it/v1/search/items?q=".$query."&lim=".$limit."&bust-cache=".rand(5, 15);
        if($cat) {
            $endpoint.="&c=".$cat;
        }
        $xml = file_get_contents($endpoint);
        $json_a = json_decode($xml, true);
        if(count($json_a["ads"]==$limit)) {
        foreach($json_a["ads"] as $ad) {
            //var_dump($thisad["images"][0]["scale"][3]);
            //echo $thisad["images"][0]["scale"][3];
            $thisad = $ad;
            $output.= "<a href='".$thisad["urls"]["default"]."?utm_source=subito-widget'>";
            $output.= "<div id='subito-box' style='display:block; float:left; text-align:center; color:#3c4858; font-family: LFTEtica,-apple-system,system-ui,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif;'>"; //ad-box
            $output.= "<div style='margin-right:5px; background-image:url(".$thisad["images"][0]["scale"][3]["secureuri"]."); display: block; width: 150px; height: 150px; background-size: cover; background-color:#000;'></div>";
            $output.= "<div style='margin-top:10px; display:inline-block; max-width:130px; text-overflow: ellipsis; overflow:hidden; height: 90px;'>".$ad["subject"]."</div>";
            $output.= "</div>"; //ad-box
            $output.= "</a>";
        }
        }
        $output.= "</div>";
        return $output;
    }

}

// Initialize the plugin
$personalize_login_pages_plugin = new Subito_Plugin();
register_activation_hook( __FILE__, array( 'Personalize_Login_Plugin', 'plugin_activated' ) );
?>