<?php
/**
 * Plugin Name: Advanced Slug Translate
 * Description: Advanced Slug Translate 1.0
 * Version: 1.0
 * Author: TuTM
 * License: GPLv2 or later
 * Text Domain: astrans
**/
?>
<?php
/* Don't load directly */
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}
global $wpdb;
if ( is_admin() ) {
    /* ============== change slug after save post ================ */
    if(!function_exists('setup_astrans')){
        function setup_astrans(){
            /* value default */
            define('astrans_plugin_url', plugin_dir_url( __FILE__ )); 
            define('astrans_plugin_dir', plugin_dir_path( __FILE__ )); 
            $text_domain = 'astrans';
            require_once dirname( __FILE__ ) . '/inc/SlugTrans.php';

            $active_plugin = add_site_option( 'astrans_plugin', 'active' );
            if($active_plugin == true){
                $meta_box = array(
                        $text_domain . '_status'    =>  '0',
                        $text_domain . '_language'    =>  'en',
                        $text_domain . '_trans_for'    =>  '',
                    );
                foreach ($meta_box as $key => $value) {
                    add_site_option( $key, $value );
                }
            }

            /* load file json map  */
            global $language_suport;
            $dir    = astrans_plugin_dir . 'inc/maps/';
            $list_file = scandir($dir);
            $list_lang['en'] = 'English';
            for ($i=2; $i < count($list_file); $i++) { 
                $string = file_get_contents(astrans_plugin_url . 'inc/maps/' . $list_file[$i]);
                $map =  json_decode($string, true);     
                $short = $map['short'];
                $list_lang[$short] = $map["lang"];
                SlugTransAstrans::$maps[$short] = $map[$short];
            }
            $language_suport = $list_lang;

            global $astrans_pages;
            $astrans_pages = 10;

            /* ================== */
            /* Add column to MSQL */
            /* ================== */
            global $wpdb;
            /* add column astrans to table wp_posts. Value default 0 ( 0 : false, 1 :  true) */
            $row = $wpdb->get_results(  "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$wpdb->prefix."posts' AND column_name = 'astrans'"  );
            if(empty($row)){
                $wpdb->query("ALTER TABLE ".$wpdb->prefix."posts ADD astrans INT(1) NOT NULL DEFAULT 0");
            }
            /* add column astrans to table wp_terms. Value default 0 ( 0 : false, 1 :  true) */
            $row = $wpdb->get_results(  "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$wpdb->prefix."terms' AND column_name = 'astrans'"  );
            if(empty($row)){
                $wpdb->query("ALTER TABLE ".$wpdb->prefix."terms ADD astrans INT(1) NOT NULL DEFAULT 0");
            }
        }
        add_action( 'init', 'setup_astrans' );
    }   

    /* register menu */
    if(!function_exists('wpdocs_register_astrans_menu_page')){
        function wpdocs_register_astrans_menu_page(){
            add_menu_page( 
                __( 'Advanced Slug Translate', 'AST' ), /* title plugin */
                'ASTranslate', /* name menu plugin */
                'manage_options',
                'astrans', /* slug plugin */
                null, /* call function view if plugin */
                astrans_plugin_url . '/assets/images/ast.png', /* icon plugin */
                81 /* position plugin after setting */
            );
            add_submenu_page( 'astrans', 'Setting', 'Setting', 'manage_options', 'astrans', 'index_astrans_plugin' );
            add_submenu_page( 'astrans', 'Sync Slug', 'Sync Slug', 'manage_options', 'astrans_sync', 'astrans_sync' );
        }
        add_action( 'admin_menu', 'wpdocs_register_astrans_menu_page' );
    }

    /**
     * Display a custom menu page
     */
    function index_astrans_plugin(){
        require_once  dirname( __FILE__ ) . '/inc/index.php';
    }
    function astrans_sync(){
        require_once  dirname( __FILE__ ) . '/inc/sync.php';
    }

    /* call sidebar */
    function hook_astrans_sidebar(){
        require_once  dirname( __FILE__ ) . '/inc/astrans_sidebar.php';
    }
    add_action( 'astrans_sidebar', 'hook_astrans_sidebar', 1 );

    /* add js css admin */
    function hook_astrans_admin($hook) {
        wp_enqueue_media();
        wp_enqueue_style( 'astrans_css', astrans_plugin_url . '/assets/css/astrans-admin.css' );
        wp_enqueue_script( 'astrans_js', astrans_plugin_url . '/assets/js/astrans-admin.js' );
    }
    add_action( 'admin_enqueue_scripts', 'hook_astrans_admin', 10 );

    /* (^ _ ^) code (@ _ @) */
    /* Automation Translate */
    if(get_site_option('astrans_status') == 1 ){
        /* hook slug page & post */
        add_filter( 'wp_unique_post_slug', 'custom_unique_post_slug_astrans', 10, 4 );
        function custom_unique_post_slug_astrans( $slug, $post_ID, $post_status, $post_type ) {
            $post = get_post($post_ID);
            if($post->post_status != 'publish' && $post->post_status != 'private' && $post->post_name == ''){            
                $p_type = json_decode(str_replace('\"', '"', get_site_option( 'astrans_trans_for' )), true);
                if($p_type['astrans_pages'] == '1' && $post_type == 'page'){
                    $slug = trans_slug_astrans($post_ID, $slug, $post_type);
                }else if( $p_type['astrans_posts'] == '1' && $post_type == 'post'){
                    $slug = trans_slug_astrans($post_ID, $slug, $post_type);
                }
                save_trans_astrans($post_ID, $post_type);
            }        
            return $slug;
        }
        /* hook slug category (not taxonomy) */
        add_action('wp_unique_term_slug', 'custom_created_term_astrans', 10, 4);
        function custom_created_term_astrans( $slug, $term )
        {
            $p_type = json_decode(str_replace('\"', '"', get_site_option( 'astrans_trans_for' )), true);
            if($p_type['astrans_cate'] == '1' && $term->taxonomy == 'category'){
                $lang_site = get_site_option('astrans_language');
                $s = SlugTransAstrans::downcode($term->name ,$lang_site );
                if($s != $term->name){
                    $s = str_replace(' ', '-', $s);
                    $s = strtolower($s);
                    $i = 0;
                    $check = $s;
                    while (slug_cate_exit_astrans($check) == false) {
                        $i++;
                        $check = $s . '-' . $i;
                    }
                    return $check; 
                    save_trans_astrans($term->term_id ,$term->taxonomy);  
                }
            }
            return $slug;
        }
    }
    function trans_slug_astrans($post_ID, $slug_default, $post_type){
        $lang_site = get_site_option('astrans_language');
        $post = get_post($post_ID);
        $slug = SlugTransAstrans::downcode($post->post_title ,$lang_site );
        if($slug != $post->post_title){
            $slug = str_replace(' ', '-', $slug);
            $slug = strtolower($slug);  
            $check = $slug;
            $i = 0;
            while (intval(slug_post_exit_astrans($check, $post_type)) != 0) {
                $i++;
                $check = $slug . '-' . $i;
                if($i >= 100){
                    break;
                }
            }
            $slug = $check;       
        }else{
            $slug = $slug_default;
        }
        return $slug;
    }
    function slug_cate_exit_astrans($slug, $name = 'category', $id = 0){
        if( (term_exists($slug, $name) != '') && ( intval(term_exists($slug, $name)["term_id"]) != intval($id)) ){
            return false;
        }else if( (term_exists($slug, $name) != '') && ( intval(term_exists($slug, $name)["term_id"]) != intval($id)) ){
            return true;
        }
        return true;
    }
    function slug_post_exit_astrans($slug, $post_type = 'post', $id = 0){
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts WHERE post_name = '".$slug."' && post_type = '".$post_type."'";
        $a = $wpdb->get_results($query);
        if( (count($a) != 0) && intval($a[0]->ID) != intval($id)){
            return $a[0]->ID;
        }else if(count($a) != 0 && intval($a[0]->ID) == intval($id) ){
            return 0;
        }
        return 0;
    }

    /* Ajax save option astrans */
    add_action( 'wp_ajax_save_astrans_astrans', 'save_astrans_astrans');
    function save_astrans_astrans() {
        $status = true;
        $mess = 'Successfully Saved';
        if(isset($_GET['astrans_status']) && $_GET['astrans_status'] != "" ){
            $status = strval($_GET['astrans_status']);
            $language = strval($_GET['astrans_language']);
            $for = strval($_GET['astrans_trans_for']);

            update_option( 'astrans_language', $language );
            update_option( 'astrans_status', $status );
            if($status == '1'){            
                update_option( 'astrans_trans_for', $for);
            }            
            $status = true;
            $mess = 'Successfully Saved';
        }else{
            $status = false;
            $mess = 'Errors - Process SAVE can not be completed, please try again or contact support';
        }
        $data = array(
            'status'        =>  $status,
            'messenger'     =>  $mess
        );
        wp_send_json_success( $data );
    }
    /* Ajax sync load */
    add_action( 'wp_ajax_table_sync_astrans', 'table_sync_astrans');
    function table_sync_astrans() {
        global $astrans_pages;
        if(isset($_GET['pre_page']) && $_GET['pre_page'] != ''){
            $astrans_pages = intval($_GET['pre_page']);
        }else{
            $astrans_pages = 10;
        }

        if(isset($_GET['post_type']) && $_GET['post_type'] != ''){
            $type = strval($_GET['post_type']);
        }else{
            $type = 'post';
        }

        if(!isset($_GET['filter']) || $_GET['filter'] == ''){
            $filter = 0;
        }else{
            $filter = intval($_GET['filter']);   
        }
        if(isset($_GET['keyworks'])){
            $keyworks = strval($_GET['keyworks']);
        }else{
            $keyworks = "";
        }

        $paged = $_GET['paged'] != '' ? intval($_GET['paged']) : 1;

        $args = array(
            'type'      =>  $type,
            'filter'    =>  $filter,
            'keyworks'  =>  $keyworks,
            'offset'    =>  0,
        ); 
        $posts = get_astrans($args);
        if($posts != ''){
            if($paged == 1 ){
                $to = 0;
                $from = $astrans_pages - 1;
                if($from >= count($posts) || $astrans_pages == 0){
                    $from = count($posts) - 1;
                }
            }else{
                $to = ($paged - 1) * $astrans_pages;
                $from = $paged * $astrans_pages - 1;
                if($from >= count($posts)){
                    $from = count($posts) - 1;
                }
            }

            for ($i= $to; $i <= $from ; $i++) { 
                $post = $posts[$i];
        ?>        
                <tr id='astrans_<?php echo $type; ?>_<?php echo $post['id']; ?>'>
                    <td><input type="checkbox" class="astrans_check_all"></td>
                    <td><?php echo $post['title']; ?></td>
                    <td><?php echo $post['slug_old']; ?></td>
                    <td><input type="text" name="" value="<?php echo $post['slug_new'] ; ?>"></td>
                    <td class="icon_tran"><i class="dashicons dashicons-<?php echo check_astrans($post['id'], $type) === true ? 'yes' : 'no'; ?>"></i></td>
                    <td><button class="btn-astrans">save</button></td>
                </tr>   
    <?php
            }
        }                
    }/* end function table_sync_astrans */

    /* Ajax save sync */
    add_action( 'wp_ajax_save_sync_astrans', 'save_sync_astrans');
    function save_sync_astrans() {
        if( isset($_GET['type']) && $_GET['type'] != ""){
           $type   = strval($_GET['type']); 
        }

        if( isset($_GET['id']) && $_GET['id'] != ""){
           $id     = intval($_GET['id']); 
        }
        if( isset($_GET['slug']) && $_GET['slug'] != ""){
           $slug   = strval($_GET['slug']); 
        }        
         
        if($type != 'category'){
            $my_post = array(
                'ID'            => $id,
                'post_name'     => $slug,
            );
            wp_update_post( $my_post );
            save_trans_astrans($id, $type);
        }else{
            $my_post = array(
                'slug' => $slug,
            );
            wp_update_term($id, 'category', $my_post);
            save_trans_astrans($id, 'category');
        }
    }
    /**
      * 
      * Function name : check_astrans
      * $id : id of post, page or category
      * $type : is post , page or category
      * return : true or false
      *
      **/
    function check_astrans($id, $type){
        switch ($type) {
            case 'post':
                $p = get_post($id);
                if(intval($p->astrans) == 1){
                    return true;
                }
                break;
            case 'page':
                $p = get_post($id);
                if(intval($p->astrans) == 1){
                    return true;
                }
                break;
            case 'category':
                $c = get_term( $id , 'category');
                if(intval($c->astrans) == 1){
                    return true;
                }      
                break;
            default:
                return false;
                break;
        }
        return false;
    }
    function save_trans_astrans($id, $type){
        global $wpdb;
        if($type != 'category'){
            /* save table wp_posts of post & page */
            $wpdb->get_results(  "UPDATE ".$wpdb->prefix."posts SET astrans = '1' WHERE ID = " . $id );
        }else{
            /* save table wp_terms of category */
            $wpdb->get_results(  "UPDATE ".$wpdb->prefix."terms SET astrans = '1' WHERE term_id = " . $id );
        }
    }

    /****
      * 
      * Function name : get_astrans
      * $args : array
      * return : array
      *
    ****/
    function get_astrans($args){
        $type = $args['type'] != '' ? $args['type'] : 'post';
        $filter = $args['filter'] != '' ? intval($args['filter']) : 0;
        $keyworks = $args['keyworks'] != '' ? $args['keyworks'] : '';
        $offset = $args['offset'] != '' ? $args['offset'] : 0;
        $all_astrans = array();
        $data = array("id"=>"", "title"=>"", "slug_old"=>"", "slug_new"=>"");
        if($type == 'page' || $type == 'post'){
            $args = array(
                'post_pre_page' => -1,
                'numberposts'=> -1,                                          
                'offset' => 0,
                'post_type' => $type,
                'orderby' => 'date',
                'order'   => 'DESC',
            ); 
            if($type == 'page'){
                $my_post = get_pages($args);
            }else{
                $my_post = get_posts($args);
            }    
            foreach ($my_post as $post) {
                $data = '';
                if($keyworks == ''){
                    $key = $post->post_title;
                }else{
                    $key = $keyworks;
                }
                $key = strtolower($key);            
                if( intval($post->astrans) == $filter || $filter == 2 ){ /* $filter */
                    if( strpos(strtolower($post->post_title), $key) !== false && strpos(strtolower($post->post_title), $key) >= 0){ /* search */
                        $slug = $check =  SlugTransAstrans::downcode(urldecode($post->post_name), get_site_option('astrans_language'));
                        $i = 0;
                        while (intval(slug_post_exit_astrans($check, $type, $post->ID)) != 0) {
                            $i++;
                            $check = $slug . '-' . $i;
                            if($i >= 100){
                                break;
                            }
                        }
                        $slug = $check;
                        $data = array("id"=>$post->ID, "title"=>$post->post_title, "slug_old"=>urldecode($post->post_name), "slug_new"=>$slug);
                        $all_astrans[] = $data;
                    }
                }            
            }
        }else if($type == 'category'){
            $args = array(
                'post_pre_page' => -1,                                          
                'offset' => $offset,
                'post_type' => $type,
                'orderby' => 'date',
                'order'   => 'DESC',
                'hide_empty'=> 0,
            );
            $cates = get_categories($args);
            foreach ( $cates as $cate ) {    
                $data = '';        
                if($keyworks == ''){
                    $key = $cate->name;
                }else{
                    $key = $keyworks;
                }
                $key = strtolower($key);            
                if(intval($cate->astrans) == $filter || $filter == 2){               
                    if( strpos(strtolower($cate->name), $key) !== false && strpos(strtolower($cate->name), $key) >= 0){ /* search */                     
                        $slug = $check=  SlugTransAstrans::downcode(urldecode($cate->slug), get_site_option('astrans_language'));
                        $i = 0;
                        while (slug_cate_exit_astrans($check, 'category', $cate->term_id) == false) {
                            $i++;
                            $check = $slug . '-' . $i;
                            if($i >= 100){
                                break;
                            }
                        }
                        $slug = $check;  
                        $data = array("id"=>$cate->term_id, "title"=>$cate->name, "slug_old"=>urldecode($cate->slug), "slug_new"=>$slug);
                        $all_astrans[] = $data;
                    }
                }            
            }
        }else{
            do_action('all_taxonomy');
        }
        return $all_astrans;
    }
    /* page navi */
    add_action( 'wp_ajax_astrans_navi', 'astrans_navi');
    function astrans_navi() {
        global $astrans_pages;
        if(isset($_GET['pre_page'])){
            $astrans_pages = intval($_GET['pre_page']);
        }else{
            $astrans_pages = 10;
        }
        if(isset($_GET['post_type']) && $_GET['post_type'] != ''){
            $type = strval($_GET['post_type']);
        }else{
            $type = 'post';
        }
        if(!isset($_GET['filter']) || $_GET['filter'] == null){
            $filter = 0;
        }else{
            $filter = intval($_GET['filter']);   
        }
        if (isset($_GET['keyworks'])) {
            $keyworks = strval($_GET['keyworks']);
        }       

        $offset = 0;
        $args = array(
            'type'      =>  $type,
            'filter'    =>  $filter,
            'keyworks'  =>  $keyworks,
            'offset'    =>  0,
            'page'      =>  1,
        ); 
        $posts = get_astrans($args);
        
        if(count($posts) <= $astrans_pages){

        }else{
            $page = ( count($posts) - (count($posts)%$astrans_pages) ) / $astrans_pages;
            if($astrans_pages > 1){
                $page++;  
            }        
            if($astrans_pages == 0){
                $page = 1;
            }
            if($page > 1){
                for ($i=1; $i <= $page ; $i++) {
        ?>
                    <a href="javascript:void(0)" data-page="<?php echo $i; ?>" <?php if($_GET['paged'] == $i) echo 'class="active"'; ?>><?php echo $i; ?></a>
        <?php
                }    
            }
        }

    }
}
?>