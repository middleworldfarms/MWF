<?php
/**
 * The Template for update row in plugins list tables.
 *
 * @since 5.0.0
 * @var string $init The plugin init.
 * @var string $message The message to print.
 * @var WP_List_Table $wp_list_table The list table instance.
 * @package YITH/PluginUpgrade
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>

<tr class="plugin-update-tr <?php echo is_plugin_active( $init ) ? 'active' : ''; ?>">
	<td colspan="<?php echo esc_attr( $wp_list_table->get_column_count() ); ?>" class="plugin-update colspanchange">
		<div class="update-message notice inline notice-warning notice-alt">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
	</td>
</tr>
