<?php
/*
Plugin Name: WP Geofence
Plugin URI: https://github.com/acashjos/wp-geofence
Description: A plugin to geofence your blog posts
Version: 1.0
Author: acash
Author URI: https://github.com/acashjos/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( !defined( 'ABSPATH' ) ) exit;
?>
<?php
/**
 * Initialize wprLoc on the post edit screen.
 */

function init_plug() {
    new wprLoc();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'init_plug' );
    add_action( 'load-post-new.php', 'init_plug' );
}
else 
{
	init_plug();
}
class wprLoc {

private $TERRITORY="PAK";
private $mIP=null;
private $countries;
	/**
	 * Hook into the appropriate actions when the wprLoc is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_func' ));
		add_filter( 'get_previous_post_where', array( $this, 'adj_link_mod') );
		add_filter( 'get_next_post_where', array( $this, 'adj_link_mod') );
		$this->LoadVisitorData();
        add_shortcode( 'local', array(&$this, 'ShortcodeDoer') );
        
	}

    public function ShortcodeDoer($atts,$content="")
    {
        if($atts['code']==$this->TERRITORY || $this->TERRITORY=='*')
return $content;
return "";
    }
	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
    if ( post_type_supports( $post_type, 'thumbnail' )) {
      add_meta_box(
        'friendly_territories'
        ,'Friendly Territories'
        ,array( $this, 'render_meta_box_content' )
        ,$post_type
        ,'normal'
        ,'default'
      );
    }
	}

	/**
	 * Save the meta when the post is saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['wprLoc_nonce'] ) )
			return $post_id;

		$nonce = $_POST['wprLoc_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wprLoc' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
    // so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
		
    /* All good, its safe for us to save the data now. */

		// Sanitize the user input.
		$locs = explode("; ",$_POST['territories']);//sanitize_text_field(  );
	 delete_post_meta( $post_id, '_post_territory' );
  foreach ($locs as $key => $value) {
   	$value=trim($value) ;
   	if(!empty($value))
   	 add_post_meta( $post_id, '_post_territory', $value ,false);
  
   }
    
    //Set as featured image.
    //delete_post_meta( $post_id, '_thumbnail_id' );
    //add_post_meta( $post_id , '_thumbnail_id' , $attach_id, true);
	}

	/**
	 * Render Meta Box content.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wprLoc', 'wprLoc_nonce' );
?>
		<label for="territories"> Enter 3 leter country codes </label> 
		<div id="ter_list" ondblclick="loc_edit(this);"></div>
		<input type="hidden" id="hidden_loc_list" name="territories" size="25"  value="<?php
		$terits=get_post_meta($post->ID,"_post_territory");
		echo implode("; ",$terits).'; ';
		?>" />
		<input type="text" id="territories" size="25" onkeypress="check_typed_country(this,event)" />
		<div id="ter_hint" >
		</div>
		<script type="text/javascript">
		var countries={<?php
			$i=0;
			foreach ($this->countries as $key => $value) {
				echo "'$key': '".addslashes($value)."'".(++$i>=count($this->countries)?'':",\n");
			}
			?>}
		document.getElementById('ter_list').innerHTML=document.getElementById('hidden_loc_list').value;
		function check_typed_country(elem,evnt)
		{
			var x=String.fromCharCode(evnt.which|evnt.keyCode);
			var hint=document.getElementById('ter_hint');
			hint.innerHTML="";
			var limi=0;
			var regx=new RegExp('\\b'+elem.value+x, "i")
			for(var b in countries)
					if( b.match( regx) && limi++<20)
						hint.innerHTML+=b.replace(regx,'<b>$&</b>')+": "+countries[b]+"<br>";
			for(var b in countries)
					if(  countries[b].match(regx) &&limi++<20)
						hint.innerHTML+=b+": "+countries[b].replace(regx,'<b>$&</b>')+"<br>";
			if(x==';')
			{
			
				var flag=false;
				for(var b in countries)
					if(b==elem.value.toUpperCase())flag=true;
				if(flag==true)
				{
					document.getElementById('hidden_loc_list').value=document.getElementById('hidden_loc_list').value+elem.value+"; ";
					document.getElementById('ter_list').innerHTML=document.getElementById('hidden_loc_list').value;
				elem.value="";}
			}
			return false;
		}
		function loc_edit (elem) {
			var string="";
if (window.getSelection) {  // all browsers, except IE before version 9
    var range = window.getSelection ();
    string= (range.toString ());
} 
else {
    if (document.selection.createRange) { // Internet Explorer
        var range = document.selection.createRange ();
        string= (range.text);
        
    }
}

var vals=elem.innerHTML.split("; ");
			
        var pos=0;
        for(x in vals)
        	if(vals[x]==string){
        vals.splice(x,1);
        elem.innerHTML=vals.join("; ");
        document.getElementById('hidden_loc_list').value=vals.join("; ");
        document.getElementById('territories').value=string; break;
    }
}
		</script
<?php
}

function filter_func( $query ) {
    // Make sure this only runs on the main query on the main query
  if (  !is_admin() && $this->TERRITORY!='*') {//$query->is_main_query() &&

        // Exclude posts that have been explicitly set to hidden
        $query->set('meta_query', array(
            'relation' => 'OR',
            // Include posts where the meta key isn't set
            array(
                'key'     => '_post_territory',
                'value'   => 'asdf', // A value must exist due to https://core.trac.wordpress.org/ticket/23268
                'compare' => 'NOT EXISTS',
            ),
            // Include posts where the meta key isn't explicitly true
            array(
                'key'     => '_post_territory',
                'value'   => $this->TERRITORY,
                'compare' => '==',
            ),
        ) );
    }

}
function adj_link_mod( $where ) {
    global $wpdb;
    if($this->TERRITORY!='*')
     $where .= " AND  ( EXISTS  ( 
    	SELECT 1 FROM $wpdb->postmeta WHERE ($wpdb->postmeta.post_id = p.ID ) AND $wpdb->postmeta.meta_key = '_post_territory' and  
    	$wpdb->postmeta.meta_value='$this->TERRITORY')
    	OR
    	  NOT EXISTS  ( 
    	SELECT 1 FROM $wpdb->postmeta WHERE ($wpdb->postmeta.post_id = p.ID ) AND $wpdb->postmeta.meta_key = '_post_territory' ))";
	
return $where;
}

 function LoadVisitorData()
    {
        // Get the path of the plugin
        $mPluginDir = plugin_dir_path( __FILE__ );
        $mPluginUrl = plugin_dir_url ( __FILE__ );
                
        // Include MaxMind's API
        include( $mPluginDir . 'geoip.inc');
        
        //check for bots (basic check)
        if(preg_match("/(bot|bing|google|yahoo|fb|baidu|spider|crawl|slurp)/i", $_SERVER['HTTP_USER_AGENT']))
           { $this->TERRITORY ='*';
            return;}
        // Get the visitor IP
        $iIp = $_SERVER[ 'REMOTE_ADDR' ];
        if ( !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) 
        {               
            // check ip from share internet
            $iIp = $_SERVER[ 'HTTP_CLIENT_IP' ];
        } elseif ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR'] ) )
        {
            // to check ip is pass from proxy
            $iIp = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        }
        $iIpList = explode(",", $iIp);
        
        // Go through the list of IPs
        foreach( $iIpList as $ip )
        {
            // Ignore LAN IPs and pick the first one representing WAN IPs
            if( substr( $ip,0,8 ) !== '192.168.' )
            {
                $this->mIP=$ip;
                break;
            }
        }
    
        // Connect to MaxMind's GeoIP
        $iGeoIP = geoip_open( $mPluginDir . 'GeoIP.dat', GEOIP_STANDARD);
        
        //copy country list and codes
        $this->countries=array();
        foreach ($iGeoIP->GEOIP_COUNTRY_CODES3 as $key => $value) {
        	$this->countries[$value]=$iGeoIP->GEOIP_COUNTRY_NAMES[$key];
        }
        
        // Get the country id
        $iCountryID = geoip_country_id_by_addr( $iGeoIP, $this->mIP );
        
        if ( $iCountryID !== false ) {
            // Lookup country code and name
            $this->TERRITORY = $iGeoIP->GEOIP_COUNTRY_CODES3[$iCountryID];  
        }
                
        // Close MaxMind's connection
        geoip_close($iGeoIP);
    }
 
}

