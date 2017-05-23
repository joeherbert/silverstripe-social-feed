<?php

class SocialFeedControllerExtension extends DataExtension
{
	public function onBeforeInit()
	{
		// Allow easy clearing of the cache in dev mode
		if (Director::isDev() && isset($_GET['socialfeedclearcache']) && $_GET['socialfeedclearcache'] == 1) {
			foreach (SocialFeedProvider::get() as $prov) {
				$prov->clearFeedCache();
			}
		}
	}

	public function SocialFeed($customHandleInstagram = null,$customHandleFacebook = null,$customHandleTwitter = null, $chunk = false)
	{
		
		$combinedData = array();
		if ($customHandleInstagram) {
			$link_array = explode('/',$customHandleInstagram);
    		$handle = end($link_array);
    		$query_array = explode('?',$handle);
    		$handle = $query_array[0];
			$combinedData = $this->getProviderFeed(SocialFeedProviderInstagram::get()->filter('Enabled', 1), $combinedData, $handle);
		}
		if ($customHandleFacebook) {
			$link_array = explode('/',$customHandleFacebook);
    		$handle = end($link_array);
    		$query_array = explode('?',$handle);
    		$handle = $query_array[0];
			$combinedData = $this->getProviderFeed(SocialFeedProviderFacebook::get()->filter('Enabled', 1), $combinedData, $handle);
		}
		if ($customHandleTwitter) {
			$link_array = explode('/',$customHandleTwitter);
    		$handle = end($link_array);
    		$query_array = explode('?',$handle);
    		$handle = $query_array[0];
			$combinedData = $this->getProviderFeed(SocialFeedProviderTwitter::get()->filter('Enabled', 1), $combinedData, $handle);
		}
		
		$result = new ArrayList($combinedData);
		$result = $result->sort('Created', 'DESC');
		if ($chunk) {
			$chunkedData = array_chunk($result->toArray(),$chunk);
			foreach ($chunkedData AS $k => $v) {
				$chunkedData[$k] = array();
				$chunkedData[$k]['Items'] = new ArrayList($v);
			}
			/*echo "<pre>";
			print_r($chunkedData);
			die();*/
			$result = new ArrayList($chunkedData);
		}
		
		return $result;
	}

	private function getProviderFeed($providers, $data = array(), $customHandle = null)
	{
		foreach ($providers as $prov) {
			if (is_subclass_of($prov, 'SocialFeedProvider')) {
				if ($feed = $prov->getFeed($customHandle)) {
					foreach ($feed->toArray() as $post) {
						$data[] = $post;
					}
				}
			}
		}
		return $data;
	}
}
