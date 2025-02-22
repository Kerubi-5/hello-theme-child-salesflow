<?php

/**
 * Ticket Details Template.
 *
 * This is a built-in template file. If you need to customize it, please,
 * DO NOT modify this file directly. Instead, copy it to your theme's directory
 * and then modify the code. If you modify this file directly, your changes
 * will be overwritten during next update of the plugin.
 */

/* Exit if accessed directly */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @var $post WP_Post
 */
global $post;

/* Get author meta */
$author = get_user_by('id', $post->post_author);
?>
<div class="wpas wpas-ticket-details">

	<!-- Remove ticket navigation -->
	<!-- <?php wpas_get_template('partials/ticket-navigation'); ?> -->

	<!-- <?php
				/**
				 * Display the table header containing the tickets details.
				 * By default, the header will contain ticket status, ID, priority, type and tags (if any).
				 */
				wpas_ticket_header(array(
					'container' => 'div',
					'container_class' => 'wpas-table-responsive'
				));
				?> -->

	<?php

	do_action('wpas_ticket_details_replies_after', $post);

	/**
	 * Prepare to show the reply form.
	 */
	if (apply_filters('wpas_show_reply_form_front_end', true, $post)) {
	?>

		<h3><?php esc_html_e('Write a reply', 'awesome-support'); ?></h3>

	<?php
		/**
		 * Display the reply form.
		 *
		 * @since 3.0.0
		 */

		wpas_get_reply_form();
	} ?>


	<table class="wpas-table wpas-ticket-replies">
		<col class="col1" />
		<col class="col2" />
		<tbody>
			<tr class="wpas-reply-single" valign="top">
				<td style="width: 64px;">
					<div class="wpas-user-profile">
						<?php echo apply_filters('wpas_fe_template_detail_author_avatar', get_avatar($post->post_author, '64', get_option('avatar_default')), $post); ?>
					</div>
				</td>

				<td style="width: calc(100% - 64px);">
					<div class="wpas-reply-meta">
						<div class="wpas-reply-user">
							<strong class="wpas-profilename"><?php echo apply_filters('wpas_fe_template_detail_author_display_name', $author->data->display_name, $post); ?></strong>
						</div>
						<div class="wpas-reply-time">
							<time class="wpas-timestamp" datetime="<?php echo get_the_date('Y-m-d\TH:i:s') . wpas_get_offset_html5(); ?>">
								<span class="wpas-human-date"><?php echo get_the_date(get_option('date_format') . ' ' . get_option('time_format'), $post->ID); ?></span>
								<span class="wpas-date-ago"><?php printf(esc_html__('%s ago', 'awesome-support'), human_time_diff(get_the_time('U', $post->ID), current_time('timestamp'))); ?></span>
							</time>
						</div>
					</div>

					<?php
					/**
					 * wpas_frontend_ticket_content_before hook
					 *
					 * @since  3.0.0
					 */
					do_action('wpas_frontend_ticket_content_before', $post->ID, $post);

					/* Process missing html tag when pull content from email for ticket and ticket reply 11-5447420 */
					$post->post_content = force_balance_tags($post->post_content);

					/**
					 * Display the original ticket's content
					 */
					echo '<div class="wpas-reply-content wpas-break-words">' .  make_clickable(apply_filters('the_content', $post->post_content)) . '</div>';

					/**
					 * wpas_frontend_ticket_content_after hook
					 *
					 * @since  3.0.0
					 */
					do_action('wpas_frontend_ticket_content_after', $post->ID, $post);
					?>

				</td>

			</tr>

			<?php
			// Set the number of replies
			$replies_per_page  = wpas_get_option('replies_per_page', 10);
			$force_all_replies = WPAS()->session->get('force_all_replies');

			// Check if we need to force displaying all the replies (direct link to a specific reply for instance)
			if (true === $force_all_replies) {
				$replies_per_page = -1;
				WPAS()->session->clean('force_all_replies'); // Clean the session
			}

			$args = array(
				'posts_per_page' => $replies_per_page,
				'no_found_rows'  => false,
				'order' => "DESC"
			);

			$replies = wpas_get_replies($post->ID, array('read', 'unread'), $args, 'wp_query');

			if ($replies->have_posts()) :

				while ($replies->have_posts()) :

					$replies->the_post();
					$user      = get_userdata($post->post_author);
					if ($user && !empty($user)) {
						$time_ago  = human_time_diff(get_the_time('U', $post->ID), current_time('timestamp'));
						wpas_get_template('partials/ticket-reply', array('time_ago' => $time_ago, 'user' => $user, 'post' => $post));
					}
				endwhile;

			endif;

			wp_reset_query(); ?>
		</tbody>
	</table>

	<?php
	if ($replies_per_page !== -1 && (int) $replies->found_posts > $replies_per_page) :

		$current = $replies->post_count;
		$total   = (int) $replies->found_posts;
	?>

		<div class="wpas-alert wpas-alert-info wpas-pagi">
			<div class="wpas-pagi-loader"><?php esc_html_e('Loading...', 'awesome-support'); ?></div>
			<p class="wpas-pagi-text"><?php echo wp_kses_post(sprintf(_x('Showing %s replies of %s.', 'Showing X replies out of a total of X replies', 'awesome-support'), "<span class='wpas-replies-current'>$current</span>", "<span class='wpas-replies-total'>$total</span>")); ?>
				<?php
				if ('ASC' == wpas_get_option('replies_order', 'ASC')) {
					$load_more_msg = __('Load newer replies', 'awesome-support');
				} else {
					$load_more_msg = __('Load older replies', 'awesome-support');
				} ?>
				<?php if (-1 !== $replies_per_page) : ?><a href="#" class="wpas-pagi-loadmore"><?php echo esc_html($load_more_msg); ?></a><?php endif; ?>
			</p>
		</div>

	<?php endif; ?>



</div>

<style>
	.wpas-table.wpas-ticket-replies tbody tr:first-child {
		display: none;
	}

	#wpas_files_wrapper {
		display: grid;
		grid-template-columns: auto minmax(0, 1fr);
		/* First column adjusts to content, second column's width is dynamic with a minimum of 0 */
		grid-template-rows: auto auto;
		gap: 0.5rem 1rem;
		align-items: center;
		margin: 0.75rem 0;
	}

	p.wpas-help-block  {
		display: none !importantA;
	}


	.checkbox {
		display: none;
	}

	[data-elementor-type="header"] {
		display: none;
	}

	#content {
		width: 100% !important;
		max-width: 100% !important;
		padding: 0 24px;
	}

	.page-header {
		display: none !important;
	}

	#wpas-new-reply {
		margin-bottom: 1rem;
	}

	.wpas-btn {
		background-color: #405AAC;
		color: white;
		border: none;
	}

	.wpas-btn:hover {
		background-color: #3D8FEF;
	}
</style>

<script>
	jQuery(document).ready(function($) {
		// Reduce the number of rows to 5 for all textareas on page load
		$('textarea').attr('rows', 3);
	});
</script>