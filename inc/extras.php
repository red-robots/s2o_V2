<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package bellaworks
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function bellaworks_body_classes( $classes ) {
    // Adds a class of group-blog to blogs with more than 1 published author.
    if ( is_multi_author() ) {
        $classes[] = 'group-blog';
    }

    // Adds a class of hfeed to non-singular pages.
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }

    if ( is_front_page() || is_home() ) {
        $classes[] = 'homepage';
    } else {
        $classes[] = 'subpage';
    }

    $browsers = ['is_iphone', 'is_chrome', 'is_safari', 'is_NS4', 'is_opera', 'is_macIE', 'is_winIE', 'is_gecko', 'is_lynx', 'is_IE', 'is_edge'];
    $classes[] = join(' ', array_filter($browsers, function ($browser) {
        return $GLOBALS[$browser];
    }));

    return $classes;
}
add_filter( 'body_class', 'bellaworks_body_classes' );

if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();
}

add_action('acf/init', 'bella_acf_op_init');
function bella_acf_op_init() {

    // Check function exists.
    if( function_exists('acf_add_options_page') ) {

        // Register options page.
        $option_page = acf_add_options_page(array(
            'page_title'    => __('Theme Options'),
            'menu_title'    => __('Theme Options'),
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}


function add_query_vars_filter( $vars ) {
  $vars[] = "pg";
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );


function shortenText($string, $limit, $break=".", $pad="...") {
  // return with no change if string is shorter than $limit
  if(strlen($string) <= $limit) return $string;

  // is $break present between $limit and the end of the string?
  if(false !== ($breakpoint = strpos($string, $break, $limit))) {
    if($breakpoint < strlen($string) - 1) {
      $string = substr($string, 0, $breakpoint) . $pad;
    }
  }

  return $string;
}

/* Fixed Gravity Form Conflict Js */
add_filter("gform_init_scripts_footer", "init_scripts");
function init_scripts() {
    return true;
}

function get_page_id_by_template($fileName) {
    global $wpdb;
    $page_id = 0;
    if($fileName) {
        $fileName = $fileName . ".php";
        $result = $wpdb->get_row( "SELECT * FROM $wpdb->postmeta WHERE meta_key='_wp_page_template' AND meta_value = '".$fileName."'" );
        $page_id = ($result) ? $result->post_id : 0;
    }
    return $page_id;
}

function string_cleaner($str) {
    if($str) {
        $str = str_replace(' ', '', $str); 
        $str = preg_replace('/\s+/', '', $str);
        $str = preg_replace('/[^A-Za-z0-9\-]/', '', $str);
        $str = strtolower($str);
        $str = trim($str);
        return $str;
    }
}

function format_phone_number($string) {
    if(empty($string)) return '';
    $append = '';
    if (strpos($string, '+') !== false) {
        $append = '+';
    }
    $string = preg_replace("/[^0-9]/", "", $string );
    $string = preg_replace('/\s+/', '', $string);
    return $append.$string;
}

function get_instagram_setup() {
    global $wpdb;
    $result = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name = 'sb_instagram_settings'" );
    if($result) {
        $option = ($result->option_value) ? @unserialize($result->option_value) : false;
    } else {
        $option = '';
    }
    return $option;
}

function extract_emails_from($string){
  preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);
  return $matches[0];
}

function email_obfuscator($string) {
    $output = '';
    if($string) {
        $emails_matched = ($string) ? extract_emails_from($string) : '';
        $stripped = preg_replace('/\s+/','',$string);

        if($emails_matched) {
            foreach($emails_matched as $em) {
                $encrypted = antispambot($em,1);
                $replace = 'mailto:'.$em;
                
                if (strpos($stripped, $replace) !== false) {
                    $new_mailto = 'mailto:'.$encrypted;
                    $string = str_replace($replace, $new_mailto, $string);
                    $rep2 = $em.'</a>';
                    $new2 = antispambot($em).'</a>';
                    $string = str_replace($rep2, $new2, $string);
                } else {
                    $emailStr = '<a href="mailto:'.$encrypted.'">'.antispambot($em).'</a>';
                    $string = str_replace($em, $emailStr, $string);
                }
            }
        }
        $output = apply_filters('the_content',$string);
    }
    return $output;
}

function get_social_links() {
    // $social_types = array(
    //     'facebook_link'  => 'fab fa-facebook-square',
    //     'instagram_link' => 'fab fa-instagram',
    //     'twitter_link'   => 'fab fa-twitter-square',
    //     'linkedin_link'  => 'fab fa-linkedin-square',
    //     'youtube_link'   => 'fab fa-youtube'
    // );

    $social_types = array(
        'facebook'  => 'fab fa-facebook-square',
        'twitter'   => 'fab fa-twitter-square',
        'linkedin'  => 'fab fa-linkedin-square',
        'instagram' => 'fab fa-instagram',
        'youtube'   => 'fab fa-youtube'
    );

    $social = array();
    foreach($social_types as $k=>$icon) {
        $value = get_field($k,'option');
        if($value) {
            $field = str_replace("_link","",$k);
            $social[$field] = array('link'=>$value,'icon'=>$icon);
        }
    }
    return $social;
}

function get_subpage_banner() {
    $banner = get_field('banner');
    $parentId = '';
    $parent_templates = array(
                'projects'=>'page-projects',
                'staff'=>'page-staff',
                'post'=>'page-news'
            );
    foreach($parent_templates as $post_type => $temp_filename) {
        if( is_singular( array($post_type) ) ) {
            $parentId = get_page_id_by_template($temp_filename);
        }
    }

    if($parentId) {
        $banner = get_field('banner',$parentId);
    }
    return $banner;
}


/* This is to fix the Theme Options redirection issue */
function myCustomAdminStyles() { ?>
    <style>
        #adminmenu li#toplevel_page_acf-options {
            display: none;
        }
    </style>
<?php }
add_action("admin_head","myCustomAdminStyles");

function custom_admin_js() {
    $currentPage = ( isset($_GET['page']) && $_GET['page'] ) ? $_GET['page'] : '';
    $customNonce = wp_create_nonce("myCustomOptionsPage");
    $url = get_bloginfo('template_directory') . '/assets/js/admin.js';
    echo '"<script type="text/javascript" src="'. $url . '"></script>"'; 
    if($currentPage=='theme-general-settings') { ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            
            /* Remove Leave Site Pop-up Message */
            acf.unload.active = false;

            var messageContainer = '<div id="msgDiv"></div>';
            $(messageContainer).insertBefore("body.toplevel_page_theme-general-settings form#post");
            $("body.toplevel_page_theme-general-settings form#post").prepend('<input type="hidden" name="optnonce" value="<?php echo $customNonce?>">');
            var customSubmitBtn = '<a class="button button-primary button-large" accesskey="p" href="#" id="acfCustomBtn">Update</a>';
            $("body.toplevel_page_theme-general-settings form#post #publishing-action input.button").hide();
            $("body.toplevel_page_theme-general-settings form#post #publishing-action").append(customSubmitBtn);
        });
    </script>
    <?php 
    }  
}
add_action('admin_footer', 'custom_admin_js');


add_action( 'admin_enqueue_scripts', 'my_script_enqueuer' );
function my_script_enqueuer() {
    wp_register_script( 'ajax-js', get_template_directory_uri() . '/assets/js/admin.js', array( 'jquery' ), '', true );
    wp_localize_script( 'ajax-js', 'ajaxParams', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
    wp_enqueue_script( 'ajax-js' );
}

/* Ajax Save */
add_action("wp_ajax_my_ajax_save_option", "my_ajax_save_option");
add_action('wp_ajax_nopriv_my_ajax_save_option', 'youMustLogin');
function my_ajax_save_option() {
    if ( !wp_verify_nonce( $_REQUEST['optnonce'], "myCustomOptionsPage")) {
        exit("");
    }  

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

        $option_id = $_POST['_acf_post_id'];
        $optionData = $_POST['acf'];
        $updatesArr = array();
        $success = false;

        if($optionData) {
            foreach($optionData as $fieldKey=>$value) {
                $isUpdate = update_field( $fieldKey, $value, 'option' );
                $updatesArr[] = $fieldKey;
            }
            $success = true;
        }


      $result = json_encode( array('success'=>$success,'fieldkeys'=>$updatesArr) );
      echo $result;

    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }
    die();
}

function youMustLogin() {
   echo "You must log in to edit.";
   die();
}

