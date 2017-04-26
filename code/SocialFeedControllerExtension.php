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

	public function SocialFeed($customHandleInstagram = null,$customHandleFacebook = null,$customHandleTwitter = null)
	{
		$combinedData = array();
		if ($customHandleInstagram) {
			$combinedData = $this->getProviderFeed(SocialFeedProviderInstagram::get()->filter('Enabled', 1), $combinedData, $customHandleInstagram);
		}
		if ($customHandleFacebook) {
			$combinedData = $this->getProviderFeed(SocialFeedProviderFacebook::get()->filter('Enabled', 1), $combinedData, $customHandleFacebook);
		}
		if ($customHandleTwitter) {
			$combinedData = $this->getProviderFeed(SocialFeedProviderTwitter::get()->filter('Enabled', 1), $combinedData, $customHandleTwitter);
		}
		$result = new ArrayList($combinedData);
		$result = $result->sort('Created', 'DESC');
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
