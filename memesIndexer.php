<?php
/**
 * Plugin Name: Memes Indexer
 * Plugin URI: http://www.lamemerva.com
 * Description: Index large number of memes by keywords.
 * Version: 1.0
 * Author: Fernando Marquez
 * Author URI: http://www.lamemerva.com
 */


function memeIndexerInit() { 
	meinlog(plugin_dir_path( __FILE__ ) . 'class-wp-widget-search-memes.php');
}
add_action('init', 'memeIndexerInit', 1); 
require_once( plugin_dir_path( __FILE__ ) . 'class-wp-widget-search-memes.php' );

global $mein_db_version;
$mein_db_version = '1.0';

function mein_install() {
	global $wpdb;
	global $mein_db_version;

	$memesTableName = $wpdb->prefix . 'mein_memes';
    $keywordsTableName = $wpdb->prefix . 'mein_keywords';
    $memesKeywordsRelationTableName = $wpdb->prefix . 'mein_memes_keywords';

    $charsetCollate = $wpdb->get_charset_collate();
    $charset = explode("_", $wpdb->get_charset_collate());
	$charset = $charset[0];	
	$charset = str_replace("COLLATE utf8mb4", "COLLATE utf8mb4_general_ci", $charset);
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	createMemesTable($memesTableName, $charset);
	createKeywordsTable($keywordsTableName, $charset);
	createMemesKeywordsTable($memesTableName, $keywordsTableName, $memesKeywordsRelationTableName, $charset);
	add_option( 'mein_db_version', $mein_db_version );
}

function createMemesTable($memesTableName, $charset) {
	$charset = "DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$sqlMemesTable = "CREATE TABLE IF NOT EXISTS $memesTableName (
		id int NOT NULL AUTO_INCREMENT,
		creation_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		description tinytext NOT NULL,
		url tinytext NOT NULL,
        mime_type tinytext NOT NULL,
		guid varchar(255) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset;";
	dbDelta( $sqlMemesTable );

}
function createKeywordsTable($keywordsTableName, $charset) {
	$sqlKeywordsTable = "CREATE TABLE IF NOT EXISTS  $keywordsTableName (
		id int NOT NULL AUTO_INCREMENT,
		keyword varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset;";
	dbDelta( $sqlKeywordsTable );
}
function createMemesKeywordsTable($memesTableName, $keywordsTableName, $memesKeywordsRelationTableName, $charset) {
	$sqlRelationsTable = "CREATE TABLE IF NOT EXISTS $memesKeywordsRelationTableName (
		meme_id int NOT NULL,
		keyword_id int NOT NULL,
        CONSTRAINT pk_meme_keyword PRIMARY KEY (meme_id, keyword_id),
        CONSTRAINT fk_meme_keyword 
            FOREIGN KEY (meme_id) REFERENCES $memesTableName(id) ON DELETE CASCADE,
        CONSTRAINT fk_keyword_meme 
            FOREIGN KEY (keyword_id) REFERENCES $keywordsTableName(id) ON DELETE CASCADE
	) $charset;";
	dbDelta( $sqlRelationsTable );
}
function mein_install_data() {
	global $wpdb;
	
	// $welcome_name = 'Mr. WordPress';
	// $welcome_text = 'Congratulations, you just completed the installation!';
	
	// $memesTableName = $wpdb->prefix . 'mein';
	
	// $wpdb->insert( 
	// 	$memesTableName, 
	// 	array( 
	// 		'time' => current_time( 'mysql' ), 
	// 		'name' => $welcome_name, 
	// 		'text' => $welcome_text, 
	// 	) 
	// );
}

register_activation_hook( __FILE__, 'mein_install' );
register_activation_hook( __FILE__, 'mein_install_data' );



function addAdminPageMeme() {
	add_menu_page('Memes', 'Memes', 'manage_options', "memes", 'crudAdminPage', 'dashicons-smiley');
}

add_action('admin_menu', 'addAdminPageMeme');


require_once(__DIR__."/cruds/memesCrud.php");
require_once(__DIR__."/cruds/keywordsCrud.php");

function meinlog($msg) {
  $pluginlog = plugin_dir_path(__FILE__).'../../debug.log';
  $message = $msg."".PHP_EOL;
  error_log($message, 3, $pluginlog);
}

//create page to show the memes searcher
function addMemesIndexerPage()
{
   $post_details = array(
  'post_title'    => 'Buscador de Memes',
  'post_content'  => 'Busca el meme que quieras por palabra clave',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_type' => 'page'
   );
   wp_insert_post( $post_details );
}

register_activation_hook(__FILE__, 'addMemesIndexerPage');

