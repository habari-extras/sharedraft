<?php

class ShareDraftPlugin extends Plugin
{
	/**
	 * Update the where filters for querying a post if the sharedraft key is set
	 *
	 * @param array $filters The array of pre-existing filters
	 * @return array The modified array, if the key is set
	 */
	function filter_template_where_filters($filters)
	{
		if(isset($_GET['sharedraft']) && isset($filters['status']) && $filters['status'] == Post::status('published')) {
			$slug = $filters['slug'];
			$key = $_GET['sharedraft'];
			if($key == md5($slug . Options::get('guid'))) {
				$filters['status'] = Post::status( 'any' );
			}
		}
		return $filters;
	}

	/**
	 * Update the publish form  to display the draft link
	 *
	 * @param FormUI $form The publishing form
	 * @param Post $post The post displayed in the form
	 */
	function action_form_publish($form, $post)
	{
		if($post->slug != '' && $post->status == Post::status('draft')) {
			$key = md5($post->slug . Options::get('guid'));
			$url = "{$post->permalink}?sharedraft={$key}";
			$notice = <<< NOTICE
<div class="container formcontrol transparent">
<p class="pct25"><label for="newslug">Share This Post:</label></p>
<p class="pct75"><a href="{$url}">{$url}</a></p>
</div>
NOTICE;
			$form->settings->append('static', 'show_draft', $notice);
		}
	}
}
?>