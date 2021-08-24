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
        add_shortcode( 'subito_teaser', array( $this, 'render_subito_teaser' ) );
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
        $output.= "<div class='subito-widget-wrapper' style='display:block; text-align:center'>";
        
        if($cat) {
            $endpoint.="&c=".$cat;
        }
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 
        $xml = file_get_contents($endpoint, false, stream_context_create($arrContextOptions));
        $json_a = json_decode($xml, true);
        
        $counter = 0;
        $price = 0;
        $classnomobile = "";
        foreach($json_a["ads"] as $ad) {
            //get the price
            foreach($ad["features"] as $feat) {             
                if($feat["uri"]=='/price') {
                    $price  = $feat["values"][0]["value"];
                }
            }
            //add cladd to hide after the second ad on mobile
            if($counter>1) { $classnomobile=" subito-widget-nomobile"; }
            $thisad = $ad;
            $output.= "<div class='subito-box".$classnomobile."'>"; //ad-box
            $output.= "<a href='".$thisad["urls"]["default"]."?utm_source=subito-widget'>";
            $output.= "<div class='subito-widget-img' style='background-image:url(".$thisad["images"][0]["scale"][3]["secureuri"].");'></div>";
            $output.= "<div class='subito-widget-title'>".$ad["subject"]."</div>";
            $output.= "<div class='subito-widget-price'>".$price."</div>";
            $output.= "</a>";
            $output.= "</div>"; //ad-box
            $counter++;
        }

        $output.= "</div>";
        return $output;
    }

    /**
     * Widget profile
     *
     * In the wp page insert the shortcode [render_subito_teaser]
     * Parameters:
     * q [mandatory] query. No whitespaces, use + instead. e.g.: audi+a4 
     * l number of ads rendered
     * c category, e.g.: 16 ( Abbigliamento ed Accessori )
     * u [mandatory]userid of the user you wanna extract the ads 
     * n [mandatory] the name of the profile
     * 
     * u and q parameters are self excluding. Limits applies to both.
     */
    public function render_subito_teaser( $attributes, $content = null ) {
        $query = isset($attributes['q']) ? $attributes['q'] : "";
        $cat = isset($attributes['c']) ? $attributes['c'] : "";
        $name = isset($attributes['n']) ? $attributes['n'] : "";
        $limit = isset($attributes['l']) ? $attributes['l'] : 5;
        //adding extract by user
        $user = isset($attributes['u']) ? $attributes['u'] : "";
        $caption = isset($attributes['caption']) ? $attributes['caption'] : "";

        //setting limit to 3 for the profile
        $limit = 5;

        //sanify n
        $name = str_replace("+"," ",$name);

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
        $output.= "<div class='subito-widget-wrapper' style='display:block; text-align:center'>";
        if($cat) {
            $endpoint.="&c=".$cat;
        }
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 
        $xml = file_get_contents($endpoint, false, stream_context_create($arrContextOptions));
        $json_a = json_decode($xml, true);
        $counter = 0;
        $classnomobile = "";

        $get_initial = substr($name, 0,1);
        $initial_circle = "<div class='initial'>".$get_initial."</div>";

        $output.= "<div class='profile-box'>";
            $output.= "<div class='initial-cont'>";
            $output.= $initial_circle;
            $output.= "</div>";
            $output.= "<div class='name-cont'>";
            $output.= $name;
            $output.= "<br>";
            $output.= "<a class='profile-follow' href='https://www.subito.it/utente/".$user."'>Segui</a>";
            $output.= "</div>";
        $output.= "</div>";
        
        


        foreach($json_a["ads"] as $ad) {
            //get the price
            foreach($ad["features"] as $feat) {             
            if($feat["uri"]=='/price') {
                $price  = $feat["values"][0]["value"];
            }
             }
            //add class to hide after the second ad on mobile
            if($counter>1) { $classnomobile=" subito-widget-nomobile"; }
            $thisad = $ad;
            $output.= "<div class='subito-box".$classnomobile."'>"; //ad-box
            $output.= "<a href='".$thisad["urls"]["default"]."?utm_source=subito-widget'>";
            $output.= "<div class='subito-widget-img' style='background-image:url(".$thisad["images"][0]["scale"][3]["secureuri"].");'></div>";
            $output.= "<div class='subito-widget-title'>".$ad["subject"]."</div>";
            $output.= "<div class='subito-widget-price'>".$price."</div>";
            $output.= "</a>";
            $output.= "</div>"; //ad-box
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
