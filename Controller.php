<?php
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php'; 

$api_object = new Controller();
switch ($_GET['api']){
	case 'twitter' :
		$api_object->twitterFunction();
		break;
	case 'facebook' :
		$api_object->facebookApi();
		break;
	case 'linkedin' :
		$api_object->linkedinApi();
		break;
}

class Controller{
	
	public $twitter_token = TWITTER_TOKEN;
	public $twitter_token_secret = TWITTER_TOKEN_SECRET;
	public $twitter_consumer_key = TWITTER_CONSUMER_KEY;
	public $twitter_consumer_secret = TWITTER_CONSUMER_SECRET;

	public $facebook_api_id = FACEBOOK_APP_ID;
	public $facebook_app_secret = FACEBOOK_APP_SECRET;
	
	public $linkedin_client_id = LINKEDIN_CLIENT_ID;
	public $linkedin_client_secret = LINKEDIN_CLIENT_SECRET;
	
	public function twitterFunction(){
		$host = 'api.twitter.com';
		$method = 'GET';
		$path = '/1.1/statuses/user_timeline.json'; 

		if ($_POST['hidden_name']){
			$screen_name = $_POST['hidden_name'];
		}else{
			$screen_name = 'twitterdev';
		}

		// Подготовка данных для запроса
		$query = array( 
			'screen_name' => $screen_name,
			'count' => '25'
		);

		$oauth = array(
			'oauth_consumer_key' => $this->twitter_consumer_key,
			'oauth_token' => $this->twitter_token,
			'oauth_nonce' => (string)mt_rand(),
			'oauth_timestamp' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_version' => '1.0'
		);
		
		// Преобразование данных в нужный нам вид
		$oauth = array_map("rawurlencode", $oauth); 
		$query = array_map("rawurlencode", $query);

		$arr = array_merge($oauth, $query); 

		asort($arr); 
		ksort($arr); 

		$querystring = urldecode(http_build_query($arr, '', '&'));

		$url = "https://$host$path";

		$base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

		$key = rawurlencode($this->twitter_consumer_secret)."&".rawurlencode($this->twitter_token_secret);

		$signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

		$url .= "?".http_build_query($query);
		$url=str_replace("&amp;","&",$url); 

		$oauth['oauth_signature'] = $signature; 
		ksort($oauth); 


		$oauth = array_map("Controller::add_quotes", $oauth);

		
		// Отправка запроса
		$auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));


		$options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
						  CURLOPT_HEADER => false,
						  CURLOPT_URL => $url,
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_SSL_VERIFYPEER => false);


		$feed = curl_init();

		curl_setopt_array($feed, $options);
		$json = curl_exec($feed);
		curl_close($feed);

		$twitter_data = json_decode($json);

		// Обработка данных с ответа
		$finally_array = array();

		$ssl_is_on = $_SERVER['HTTPS'];

		foreach($twitter_data as $key=>$tweet){
			$finally_array[$key]['created_at'] = $tweet->created_at;
			$finally_array[$key]['text'] = $tweet->text;
			if(!empty($tweet->entities->hashtags))
				foreach($tweet->entities->hashtags as $hashtag)
					$finally_array[$key]['text'] = str_replace('#'.$hashtag->text, '<a target="_blank" href="https://twitter.com/hashtag/'.$hashtag->text.'?src=hash">#'.$hashtag->text.'</a>', $finally_array[$key]['text']);

			if(!empty($tweet->entities->user_mentions))
				foreach($tweet->entities->user_mentions as $user_mention)
					$finally_array[$key]['text'] = str_replace('@'.$user_mention->screen_name, '<a target="_blank" href="https://twitter.com/'.$user_mention->screen_name.'">@'.$user_mention->screen_name.'</a>', $finally_array[$key]['text']);
	
			if(!empty($tweet->entities->urls))
				foreach($tweet->entities->urls as $url)
					$finally_array[$key]['text'] = str_replace($url->url, '<a target="_blank" href="'.$url->expanded_url.'">'.$url->url.'</a>', $finally_array[$key]['text']);
				
			$finally_array[$key]['user']['name'] = $tweet->user->name;
			$finally_array[$key]['user']['screen_name'] = $tweet->user->screen_name;
			$finally_array[$key]['user']['profile_image_url'] = ($ssl_is_on == null) ? $tweet->user->profile_image_url : $tweet->user->profile_image_url_https;
			
			if(!empty($tweet->entities->media))
				foreach($tweet->entities->media as $media_key=>$media)
					$finally_array[$key]['text'] = ($ssl_is_on == null) ? str_replace($media->url, '<div class="media_tweet"><a target="_blank" href="'.$media->expanded_url.'"><img src="'.$media->media_url.'"></a></div>', $finally_array[$key]['text']) :  str_replace($media->url, '<div class="media_tweet"><a target="_blank" href="'.$media->expanded_url.'"><img src="'.$media->media_url.'"></a></div>', $finally_array[$key]['text']);
			
			if($tweet->retweeted_status){
				$finally_array[$key]['retweet']['created_at'] = $tweet->retweeted_status->created_at;
				$finally_array[$key]['retweet']['text'] = $tweet->retweeted_status->text;
				if(!empty($tweet->retweeted_status->entities->hashtags))
					foreach($tweet->retweeted_status->entities->hashtags as $hashtag)
						$finally_array[$key]['retweet']['text'] = str_replace('#'.$hashtag->text, '<a target="_blank" href="https://twitter.com/hashtag/'.$hashtag->text.'?src=hash">#'.$hashtag->text.'</a>', $finally_array[$key]['retweet']['text']);

				if(!empty($tweet->retweeted_status->entities->user_mentions))
					foreach($tweet->retweeted_status->entities->user_mentions as $user_mention)
						$finally_array[$key]['retweet']['text'] = str_replace('@'.$user_mention->screen_name, '<a target="_blank" href="https://twitter.com/'.$user_mention->screen_name.'">@'.$user_mention->screen_name.'</a>', $finally_array[$key]['retweet']['text']);
				
				if(!empty($tweet->retweeted_status->entities->urls))
					foreach($tweet->retweeted_status->entities->urls as $url)
						$finally_array[$key]['retweet']['text'] = str_replace($url->url, '<a target="_blank" href="'.$url->expanded_url.'">'.$url->url.'</a>', $finally_array[$key]['retweet']['text']);

				$finally_array[$key]['retweet']['user']['name'] = $tweet->retweeted_status->user->name;
				$finally_array[$key]['retweet']['user']['screen_name'] = $tweet->retweeted_status->user->screen_name;
				$finally_array[$key]['retweet']['user']['profile_image_url'] = ($ssl_is_on == null) ? $tweet->retweeted_status->user->profile_image_url : $tweet->retweeted_status->user->profile_image_url_https;
				
				if(!empty($tweet->retweeted_status->entities->media))
					foreach($tweet->retweeted_status->entities->media as $media_key=>$media)
						$finally_array[$key]['retweet']['text'] = ($ssl_is_on == null) ? str_replace($media->url, '<div class="media_tweet"><a target="_blank" href="'.$media->expanded_url.'"><img src="'.$media->media_url.'"></a></div>', $finally_array[$key]['retweet']['text']) : str_replace($media->url, '<div class="media_tweet"><a target="_blank" href="'.$media->expanded_url.'"><img src="'.$media->media_url.'"></a></div>', $finally_array[$key]['retweet']['text']);

			}
		}

		// Передача данный на фронт часть
		if(!empty($finally_array)){
			echo json_encode($finally_array);
		}else{
			echo 0;
		}
	}
	
	public function facebookApi(){

		$fb = new \Facebook\Facebook([
		  'app_id' => $this->facebook_api_id,
		  'app_secret' => $this->facebook_app_secret,
		  'default_graph_version' => 'v2.10',
		  'default_access_token' => $_POST['facebook_access_token'], 
		]);

		// Получение данных
		try {
		  $response = $fb->get('/me/posts?fields=link,message,story,created_time&limit=25');
		  $response_data['posts'] = json_decode($response->getBody());
		  $response_user = $fb->get('/me?fields=link,name');
		  $response_data['user_info'] = json_decode($response_user->getBody());
		  $response_picture = $fb->get('/me/picture?fields=url');
		  $response_data['picture'] = '//graph.facebook.com/'.$response_data['user_info']->id.'/picture?type=large';
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
		  // Когда FB Graph верунл ошибку
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
		  // Локальная ошибка с SDK
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
		
		echo json_encode($response_data);
	}
	
	
	public function linkedinApi(){
		
		// Подготовка данных
		$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].'/Controller.php?linkedin=1&api=linkedin';
		
		$array_data = array(
			'client_id' => $this->linkedin_client_id,
			'redirect_uri' => $redirect_uri,
			'response_type' => 'code',
			'scope' => 'r_basicprofile,rw_company_admin,r_emailaddress,w_share',
			'state' => (string)mt_rand()
		);
		$url_data = http_build_query($array_data);

		// Работа с Linked Api
		if(isset($_GET['code'])){
			$url = 'https://www.linkedin.com/oauth/v2/accessToken';
			$data = array(
				'grant_type' => 'authorization_code', 
				'code' => $_GET['code'],
				'redirect_uri' => $redirect_uri,
				'client_id' => $this->linkedin_client_id,
				'client_secret' => $this->linkedin_client_secret,
				);
			$options = array(
				'http' => array(
					'method'  => 'POST',
					'protocol_version'  => '1.1',
					'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
					'content' => http_build_query($data)
				)
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			$resultat = json_decode($result);
			$accessToken = $resultat->access_token;
			
			$curl_res = curl_init();
			// Для просмотра публикаций нужно было бы добавить с ледующую строку через запятую слово 'publications' где идет перечисление полей в адресе запроса
			curl_setopt($curl_res, CURLOPT_URL,"https://api.linkedin.com/v1/people/~:(id,first-name,last-name,headline,public-profile-url,current-share,picture-urls::(original))?oauth2_access_token=".$accessToken."&format=json");
			curl_setopt($curl_res, CURLOPT_POST, 0);
			curl_setopt($curl_res, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec ($curl_res);
			curl_close ($curl_res);
			
			$response_data = array();
			$user_data = json_decode($server_output);
			
			setcookie("data", json_encode($user_data));
			header('Location: http://'.$_SERVER['HTTP_HOST']);
		}else{
			header('Location: https://www.linkedin.com/oauth/v2/authorization?'.$url_data);
		}
	}
	
	static function add_quotes($str) { 
		return '"'.$str.'"'; 
	}
}
?>