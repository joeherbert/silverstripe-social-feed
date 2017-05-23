<?php

use \Abraham\TwitterOAuth\TwitterOAuth;

class SocialFeedProviderTwitter extends SocialFeedProvider implements SocialFeedProviderInterface
{
	private static $db = array (
		'ConsumerKey' => 'Varchar(400)',
		'ConsumerSecret' => 'Varchar(400)',
		'AccessToken' => 'Varchar(400)',
		'AccessTokenSecret' => 'Varchar(400)'
	);

	private static $singular_name = 'Twitter Provider';
	private static $plural_name = 'Twitter Provider\'s';

	private $type = 'twitter';

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', new LiteralField('sf_html_1', '<h4>To get the necessary Twitter API credentials you\'ll need to create a <a href="https://apps.twitter.com" target="_blank">Twitter App.</a></h4>'), 'Label');
		$fields->addFieldsToTab('Root.Main', new LiteralField('sf_html_2', '<p>You can manually grant permissions to the Twitter App, this will give you an Access Token and Access Token Secret.</h5><p>&nbsp;</p>'), 'Label');
		return $fields;
	}

	public function getCMSValidator()
	{
		return new RequiredFields(array('ConsumerKey', 'ConsumerSecret'));
	}

	/**
	 * Return the type of provider
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	public function getFeedUncached($customHandle = null)
	{
		// NOTE: Twitter doesn't implement OAuth 2 so we can't use https://github.com/thephpleague/oauth2-client
		$connection = new TwitterOAuth($this->ConsumerKey, $this->ConsumerSecret, $this->AccessToken, $this->AccessTokenSecret);
		if ($customHandle) {
			$result = $connection->get('statuses/user_timeline', ['screen_name' => $customHandle, 'count' => 25, 'exclude_replies' => true,'include_rts' => false,'tweet_mode' => 'extended']);
			
		}
		else
		{
			$result = $connection->get('statuses/user_timeline', ['count' => 25, 'exclude_replies' => true,'include_rts' => false,'tweet_mode' => 'extended']);
		}

		if (isset($result->error)) {
			user_error($result->error, E_USER_WARNING);
		}
		return $result;
	}

	/** 
	 * @return HTMLText
	 */
	public function getPostContent($post) {
		if (isset($post->text)) {
			$text = isset($post->text) ? $post->text : '';
		} elseif ((isset($post->truncated)) AND ($post->truncated)) {
			$text = isset($post->truncated) ? $post->truncated : '';
		} else  {
			$text = isset($post->full_text) ? $post->full_text : '';
		}
		$text = preg_replace('/(https?:\/\/[a-z0-9\.\/]+)/i', '<a href="$1" target="_blank">$1</a>', $text); 

		$result = DBField::create_field('HTMLText', $text);
		return $result;
	}

	/**
	 * Get the creation time from a post
	 *
	 * @param $post
	 * @return mixed
	 */
	public function getPostCreated($post)
	{
		return $post->created_at;
	}

	/**
	 * Get the post URL from a post
	 *
	 * @param $post
	 * @return mixed
	 */
	public function getPostUrl($post)
	{
		return 'https://twitter.com/' . (string) $post->user->id .'/status/' . (string) $post->id;
	}

	/**
	 * The user's name who tweeted
	 *
	 * @param $post
	 * @return mixed
	 */
	public function getUserName($post)
	{
		return $post->user->name;
	}

	/**
	 * The first image for a Tweet
	 *
	 * @param $post
	 * @return mixed
	 */
	public function getImage($post)
	{
		if(property_exists($post->entities, 'media') && $post->entities->media[0]->media_url_https) {
			return $post->entities->media[0]->media_url_https;
		}
	}
}
