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
     * u userid of the user you wanna extract the ads 
     * 
     * u and q parameters are self excluding. Limits applies to both.
     */
    public function render_subito_widget( $attributes, $content = null ) {
        $query = isset($attributes['q']) ? $attributes['q'] : "";
        $cat = isset($attributes['c']) ? $attributes['c'] : "";
        $limit = isset($attributes['l']) ? $attributes['l'] : 5;
        //adding extract by user
        $user = isset($attributes['u']) ? $attributes['u'] : "";

        //endpoint creation
        if($query!='') {
            $endpoint="https://hades.subito.it/v1/search/items?q=".$query."&lim=".$limit."&bust-cache=".rand(5, 15);
        }

        if($user!='') {
            $endpoint="https://hades.subito.it/v1/search/items?uid=".$user."&lim=".$limit."&bust-cache=".rand(5, 15);
        }

        //sanify q
        $query=str_replace(" ","+",$query);
        if($limit=='') $limit=5;
        $output="";
        $output.= "<div style='display:block; text-align:center'>";
        
        if($cat) {
            $endpoint.="&c=".$cat;
        }
        $xml = file_get_contents($endpoint);
        $json_a = json_decode($xml, true);
        $counter = 0;
        $classnomobile = "";
        foreach($json_a["ads"] as $ad) {
            //add cladd to hide after the second ad on mobile
            if($counter>1) { $classnomobile=" subito-widget-nomobile"; }
            $thisad = $ad;
            $output.= "<a href='".$thisad["urls"]["default"]."?utm_source=subito-widget'>";
            $output.= "<div class='subito-box".$classnomobile."'>"; //ad-box
            $output.= "<div class='subito-widget-img' style='background-image:url(".$thisad["images"][0]["scale"][3]["secureuri"].");'></div>";
            $output.= "<div class='subito-widget-title'>".$ad["subject"]."</div>";
            $output.= "</div>"; //ad-box
            $output.= "</a>";
            $counter++;
        }

        $output.= "</div>";
        return $output;
    }

}

// Initialize the plugin
$personalize_login_pages_plugin = new Subito_Plugin();
register_activation_hook( __FILE__, array( 'Personalize_Login_Plugin', 'plugin_activated' ) );
?>
