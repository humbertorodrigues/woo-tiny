<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
  * Plugin Name: Integração Tiny
 * Description: Integra o woocommerce ao Tiny
 * Version:     1.0.0
 * Author:      Humberto Rodrigues
 * Author URI:  http://humbertorodrigues.com 
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 **/
// Register new status

define("WOO_TINY_DIR", plugin_dir_path( __FILE__ ));
define("WOO_TINY_URL",plugin_dir_url(__FILE__));
include('classes/class.estoque.php');
include('classes/class.notas_fiscais.php');
include('classes/class.contas_pagar.php');
include('classes/class.pedidos.php');
include('classes/class.tiny.php');
include('classes/class.canais_venda.php');
include('classes/class.vendedores.php');


$tiny = new tiny();
$estoque = new estoque();
$pedidos = new pedidos();
$notasFiscais = new notasFiscais();
$contasPagar = new contasPagar();
$canaisVenda = new canaisVenda();
$vendedores = new vendedores();

function criar_status_pedidos() {
    
    register_post_status( 'wc-shipping', array(
        'label'                     => 'Preparando envio',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Preparando envio (%s)', 'Preparando envio (%s)' )
    ) );
}
add_action( 'init', 'criar_status_pedidos' );
function atualiza_status_woocommerce( $order_statuses ) {
 
    $new_order_statuses = array();    
    $order_statuses['wc-pending'] = 'Pedido realizado';
    $order_statuses['wc-on-hold'] = 'Pedido realizado';
    $order_statuses['wc-processing'] = 'Pagamento aprovado';
    $order_statuses['wc-shipping'] = 'Preparando envio';
    $order_statuses['wc-completed'] = 'Enviado';
    
    return $order_statuses;
}
add_filter( 'wc_order_statuses', 'atualiza_status_woocommerce' );


global $jal_db_version;
$jal_db_version = '1.4';
register_activation_hook( __FILE__, 'criar_tabelas' );
function criar_tabelas() {
	global $wpdb;
	global $jal_db_version;
	$installed_ver = get_option( "jal_db_version" );
	if ( $installed_ver != $jal_db_version ) {

		$table_name = $wpdb->prefix . 'acoes_tiny';
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			acao varchar(100) DEFAULT '' NOT NULL,
			empresa varchar(100) NULL DEFAULT NULL,
			id_pedido bigint(11) NOT NULL,
			id_produto bigint(11) NULL DEFAULT NULL,
			id_tiny varchar(100) DEFAULT '' NOT NULL,
			data timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			data_execucao datetime NULL DEFAULT NULL,
			ultima_execucao datetime NULL DEFAULT NULL,
			status varchar(100) DEFAULT 'pending' NOT NULL,
			PRIMARY KEY  (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$r = dbDelta( $sql );
        
		update_option( 'jal_db_version', $jal_db_version );
	}
}
function myplugin_update_db_check() {
    global $jal_db_version;
    if ( get_site_option( 'jal_db_version' ) != $jal_db_version ) {
        criar_tabelas();
    }
}
add_action( 'plugins_loaded', 'myplugin_update_db_check' );

add_filter( 'manage_edit-shop_order_columns', 'set_coluna_codigo_tiny' );
function set_coluna_codigo_tiny($columns) {
    
    $columns['codigo_tiny'] = 'Código tiny';

    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_shop_order_posts_custom_column' , 'mostra_valor_coluna_codigo_tiny', 0, 2 );
function mostra_valor_coluna_codigo_tiny( $column, $post_id ) {
    switch ( $column ) {

        case 'codigo_tiny' :
            $codigo_tiny = get_post_meta( $post_id , 'codigo_tiny' , true );
            if(!empty($codigo_tiny)){

				echo "<a target=\"_blank\" href=\"https://erp.tiny.com.br/vendas#edit/".$codigo_tiny."\">Ver pedido ($codigo_tiny) </a>";
			}

			
			break;

        

    }
}