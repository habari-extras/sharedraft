<?php

class ShardraftPlugin extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Share Draft',
			'url' => 'http://habariproject.org/',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => '1.0',
			'description' => 'Allows an author to share a draft of a post using a special link',
			'license' => 'Apache License 2.0',
		);
	}

	function filter_template_where_filters($filters)
	{
		if(isset($_GET['showdraft']) && isset($filters['status']) && $filters['status'] == Post::status('published')) {
			$slug = $filters['slug'];
			$key = $_GET['showdraft'];
			if($key == md5($slug . Options::get('guid'))) {
				$filters['status'] = Post::status( 'any' );
			}
		}
		return $filters;
	}

	function action_form_publish($form, $post)
	{
		if($post->slug != '' && $post->status == Post::status('draft')) {
			$key = md5($post->slug . Options::get('guid'));
			$url = "{$post->permalink}?showdraft={$key}";
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