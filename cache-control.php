<div class="wrap">
	<?php include 'includes/header.php' ?>
	<h1><?php _e('wao.io Cache Control', 'wao-io-cache-control'); ?></h1>
	<?php _e('By using this plugin, you can control your site´s cache at wao.io.', 'wao-io-cache-control'); ?>

	<div class="clear"></div>
	<br>

	<div>
		<div class="wao-io-card">
		<b><?php _e( 'Invalidate Origin Content', 'wao-io-cache-control' ); ?></b>
		<br>
		<br>

		<?php
			if (
				!empty(get_option('wao_io_apikey'))
				&& !empty(get_option('wao_io_siteid'))
			) {
		?>

			<?php _e('Click on the button to invalidate your site´s cache at wao.io now', 'wao-io-cache-control'); ?>
				<br>
				<br>

				<form
					action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
					name="cache-control"
					method="post"
				>
					<input type="hidden" name="action" value="clear_cache">
					<button
						type="submit"
						class="button-primary"
					>
						<?php _e('Invalidate Origin Content', 'wao-io-cache-control'); ?>
					</button>
				</form>
				<br>
			<?php
				}
			?>


			<?php _e('Cache invalidation treats all content in the cache as if it was outdated.', 'wao-io-cache-control'); ?>
			<br>
		</div>

		<div class="wao-io-card">
			<?php _e('Cache invalidation will automatically be triggered after publishing or updating a post or a page, and after switching a theme.', 'wao-io-cache-control'); ?>
			<br>
		</div>

		<div class="wao-io-card">
			<?php
				if (
					!empty(get_option('wao_io_apikey'))
					&& !empty(get_option('wao_io_siteid'))
				) {
			?>
				<b><?php _e( 'Site Credentials', 'wao-io-cache-control' ); ?></b>
				<ul>
					<li>
						<?php _e( 'API Key', 'wao-io-cache-control' ); ?>:
						<?php echo get_option('wao_io_apikey') ?>
					</li>
					<li>
						<?php _e( 'Site ID', 'wao-io-cache-control' ); ?>:
						<?php echo get_option('wao_io_siteid') ?>
					</li>
				</ul>
			<?php
				} else {
			?>
				<strong><?php _e( 'You did not enter your API key and site ID yet.', 'wao-io-cache-control' ); ?></strong>
				<br>
				<br>
			<?php
				}
			?>

			<?php _e('To use this plugin, please enter an API key and a site ID in the wao.io section of your', 'wao-io-cache-control'); ?>
			<a href="options-general.php#wao-io-settings">
				<?php _e('settings', 'wao-io-cache-control'); ?>
			</a>
			<br>
			<br>
			<?php _e( 'If you do not have an API key yet, please contact', 'wao-io-cache-control' ); ?>
			<a href="https://wao.io/#wordpress-plugin-cachecontrol">
				wao.io
			</a>
			<?php _e( 'If you do not have an API key yet, please contact', 'wao-io-cache-control' ); ?>

		</div>
	</div>

	<?php
		if ( $wao_io_cc_message = get_transient( 'wao_io_cc_message' ) ) {
	?>
		<div class="notice notice-<?php echo $wao_io_cc_message['notice_class'] ?>">
			<h2><?php echo $wao_io_cc_message['title']; ?></h2>
			<?php
				if (!empty($wao_io_cc_message['content'])) {
					echo '<p>' . $wao_io_cc_message['content'] . '</p>';
				}
				if (!empty($wao_io_cc_message['status'])) {
					echo '<p>' . __( 'status', 'wao-io-cache-control' ) . ': ' . $wao_io_cc_message['status'] . '</p>';
				}
				if (!empty($wao_io_cc_message['site_id'])) {
					echo '<p>' . __( 'Site ID', 'wao-io-cache-control' ) . ': ' . $wao_io_cc_message['site_id'] . '</p>';
				}
			?>
			<?php echo '<p>' . __( 'Server Time', 'wao-io-cache-control' ) . ': ' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']); ?>
			<br>
		</div>
	<?php
		delete_transient( 'wao_io_cc_message' );
		}
	?>

	<div class="clear"></div>
</div>
