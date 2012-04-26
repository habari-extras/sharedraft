<?php

class ShareDraftPlugin extends Plugin
{
	
	/**
	 * Gets the secret key for a post 
	 **/
	public function get_secret_key( $post )
	{
		$key = md5($post->slug . Options::get('guid'));

		return $key;
	}
	
	/**
	 * Gets the sharing URL for a post 
	 **/
	public function get_share_url( $post )
	{
		$key = $this->get_secret_key( $post );
		
		$url = $post->permalink . '?sharedraft=' . $key;
		
		return $url;
	}
	
	/**
	 * Checks if the proper credential has been supplied to access the current post
	 **/
	private function is_authorized( $post = null, $deny = false )
	{
		$auth = Controller::get_var( 'sharedraft' );
				
		// if there's no auth key, deny authorization automatically
		if( $auth == null )
		{
			return false;
		}
		
		ACL::clear_caches(); // sadly, caching can't be used with Hisa
		
		// if someone has an auth token but should be denied, mess them up
		if( $deny == true )
		{
			// Utils::redirect( Site::get_url() );
			exit;
			return false;
		}
		
		// we assume the authorization is fine until actually testing the post
		if( $post != null )
		{
			if( $auth != $this->get_secret_key( $post ) )
			{
				return false;
			}
		}
				
		return true;
	}
	
	/**
	 * A helper function to prevent access with Hisa
	 **/
	public function deny_access()
	{
		$this->is_authorized( null, true );
	}
	
	/**
	 * Update the where filters for querying a post if the sharedraft key is set
	 *
	 * @param array $filters The array of pre-existing filters
	 * @return array The modified array, if the key is set
	 */
	public function filter_template_where_filters( $filters)
	{
		if( $this->is_authorized() )
		{
			unset( $filters['status'] );
			// Utils::debug( $filters, $filters['status'] );
		}
		
		return $filters;
	}

	/**
	 * Give users access to the token if they passed along the proper key 
	 **/
	public function filter_user_token_access( $accesses, $user_id, $token_id )
	{
		// Utils::debug( $accesses, $user_id, $token_id );
		
		if( $this->is_authorized() )
		{
			$bitmask = ACL::get_bitmask( 0 );
			$bitmask->read = true;
			
			$accesses[0] = $bitmask->value;
		}
		
		
		return $accesses;
	}
	
	/**
	 * Run the actual check of the post authorization here, in the template header 
	 **/
	public function action_template_header( $theme )
	{
		
		if( $theme->posts instanceof Posts )
		{
			// if someone is trying to sneak into multiple posts, kill their attempt
			$this->deny_access();
			return;
		}
		elseif( $theme->post instanceof Post )
		{
			if( !$this->is_authorized( $theme->post ) )
			{
				$this->deny_access();
			}
			return;
		}
		else
		{
			return;
		}
		
		// if( !$this->is_authorized( $theme->post ) )
	}

	/**
	 * Update the publish form  to display the draft link
	 *
	 * @param FormUI $form The publishing form
	 * @param Post $post The post displayed in the form
	 */
	function action_form_publish($form, $post)
	{
		if( $post->slug != '' ) {
			$url = $this->get_share_url( $post );

			$share_url = $form->settings->append( 'text', 'share_url', 'null:null', _t( 'Share URL', 'hisa' ), 'tabcontrol_text' );
			$share_url->value = $url;
			
		}
	}
	
	
}
?>