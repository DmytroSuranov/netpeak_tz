var monthNames = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];
function TwitterApi(){
	$.ajax({
		   url: "Controller.php",
		   type: 'post',
		   data: $('#form_twitter').serialize(),
		   success: function(data){
				$('.twitter_block .user_container .content').remove();
				$('.twitter_block .main_container .tweet').each(function(){
					$(this).remove();
				});
				$('.twitter_block .user_container .error').remove();
				if(data != 0){
					var data_array = $.parseJSON(data);
					console.log('Twitter');
					if(typeof data_array[0] === 'undefined'){
						$('.twitter_block .user_container').append('<div class="content error">No tweets.</div>');
						return false;
					}
					$('.twitter_block .user_container').append('<div class="content">\
						<a target="_blank" class="image" href="https://twitter.com/'+data_array[0]['user']['screen_name']+'">\
						<img src="'+data_array[0]['user']['profile_image_url']+'">\
						</a>\
						<a target="_blank" class="name" href="https://twitter.com/'+data_array[0]['user']['screen_name']+'">\
						'+data_array[0]['user']['name']+' <span>@'+data_array[0]['user']['screen_name']+'</span></a>\
						</div>');
					for(var i = 0; i < data_array.length; i++){
						var date_tweet = new Date(data_array[i]['created_at']);
						var month = monthNames[date_tweet.getMonth()];
						var day = date_tweet.getDate();
						if(typeof data_array[i]['retweet'] !== 'undefined'){
							$('.twitter_block .main_container').append('<div class="tweet tweet-'+i+' row"><div class="col-md-12">\
															<a class="image" href="https://twitter.com/'+data_array[i]['retweet']['user']['screen_name']+'"><img src="'+data_array[i]['retweet']['user']['profile_image_url']+'"></a>\
															<a class="name" href="https://twitter.com/'+data_array[i]['retweet']['user']['screen_name']+'">'+data_array[i]['retweet']['user']['name']+' <span>@'+data_array[i]['retweet']['user']['screen_name']+'</span></a>\
															<div class="body_tweet"><div class="text_tweet"><span class="retweeted">You Retweeted</span>'+data_array[i]['retweet']['text']+'</div>\
																<div class="media_tweet"></div>\
															</div>\
															</div></div>');
							
																										
						}else{
							$('.twitter_block .main_container').append('<div class="tweet tweet-'+i+' row"><div class="col-md-12">\
															<a class="image" href="https://twitter.com/'+data_array[i]['user']['screen_name']+'"><img src="'+data_array[i]['user']['profile_image_url']+'"></a>\
															<a class="name" href="https://twitter.com/'+data_array[i]['user']['screen_name']+'">'+data_array[i]['user']['name']+' <span>@'+data_array[0]['user']['screen_name']+'</span></a>\
															<div class="body_tweet"><div class="text_tweet">'+data_array[i]['text']+'</div>\
																<div class="media_tweet"></div>\
															</div>\
															</div></div>');
							
										
						}
					}
				}else{
					$('.twitter_block .user_container').append('<div class="content error">Didn\'t find anything.</div>');
				}
		   }
		 });
}

function FacebookApi(){
	FB.getLoginStatus(function(response) {
		if(response.status != 'connected'){
			FB.login(function(response) {
				if (response.authResponse) {
				$.ajax({
					   url: "Controller.php",
					   type: 'post',
					   data: 'facebook_access_token='+response.authResponse.accessToken+'&user_id='+response.authResponse.user_id,
					   success: function(data){
						   
						   $('.facebook_block .user_container .content').remove();
							$('.facebook_block .main_container .facebook_post').each(function(){
								$(this).remove();
							});
						   var data_array = $.parseJSON(data);
							console.log(data_array);
							$('.facebook_block .user_container .content').remove();
							$('.facebook_block .user_container').append('<div class="content">Posts by user <a target="_blank" href="'+data_array['user_info']['link']+'"><img src="'+data_array['picture']+'">'+data_array['user_info']['name']+'</a></div>');
							
							for(var i = 0; i < data_array['posts'].data.length; i ++){
								if (typeof data_array['posts'].data[i]['id'] !== 'undefined'){
									var date = new Date(data_array['posts'].data[i]['created_time']);
									if(data_array['posts'].data[i]['message']){
										var message = '<span class="name_in_message">'+data_array['user_info']['name']+' wrote : </span> '+ data_array['posts'].data[i]['message'];
									}else{
										var message = '';
									}
									if(data_array['posts'].data[i]['story']){
										var story = data_array['posts'].data[i]['story'];
									}else{
										var story = '';
									}
									if(data_array['posts'].data[i]['link']){
										var linka = '<a target="_blank" href="'+data_array['posts'].data[i]['link']+'">Link for post on Facebook</a>';
									}else{
										var linka = '';
									}
									$('.facebook_block .main_container').append('<div class="facebook_post"><span class="post_body">'+message +'</span> '+linka+' <span class="post_story">'+story+'</span> <span class="post_date">(Post from '+date.getDate() + ' ' + monthNames[date.getMonth()]+')</span></div>');
								}
							}
					   }
					 });

				} else {
				 console.log('User cancelled login or did not fully authorize.');
				}
			},{scope: 'user_location,user_posts,user_photos,user_about_me,ads_management,manage_pages,publish_pages,pages_show_list'});
		}else{
			$.ajax({
			   url: "Controller.php",
			   type: 'post',
			   data: 'facebook_access_token='+response.authResponse.accessToken+'&user_id='+response.authResponse.user_id,
			   success: function(data){
				  
				   $('.facebook_block .user_container .content').remove();
					$('.facebook_block .main_container .facebook_post').each(function(){
						$(this).remove();
					});
				   var data_array = $.parseJSON(data);
					console.log('Facebook');
					$('.facebook_block .user_container .content').remove();
					$('.facebook_block .user_container').append('<div class="content">Posts by user <a target="_blank" href="'+data_array['user_info']['link']+'"><img src="'+data_array['picture']+'">'+data_array['user_info']['name']+'</a></div>');
					
					for(var i = 0; i < data_array['posts'].data.length; i ++){
						if (typeof data_array['posts'].data[i]['id'] !== 'undefined'){
							var date = new Date(data_array['posts'].data[i]['created_time']);
							if(data_array['posts'].data[i]['message']){
								var message = '<span class="name_in_message">'+data_array['user_info']['name']+' wrote : </span> '+ data_array['posts'].data[i]['message'];
							}else{
								var message = '';
							}
							if(data_array['posts'].data[i]['story']){
								var story = data_array['posts'].data[i]['story'];
							}else{
								var story = '';
							}
							if(data_array['posts'].data[i]['link']){
								var linka = '<a target="_blank" href="'+data_array['posts'].data[i]['link']+'">Link for post on Facebook</a>';
							}else{
								var linka = '';
							}
							$('.facebook_block .main_container').append('<div class="facebook_post"><span class="post_body">'+message +'</span> '+linka+' <span class="post_story">'+story+'</span> <span class="post_date">(Post from '+date.getDate() + ' ' + monthNames[date.getMonth()]+')</span></div>');
						}
					}
			   }
			 });
		}
	});	
	
}

function resetAllApies(){
	console.log(window.intervalId);
	for (var i = 1; i <= window.intervalId; i++)
        window.clearInterval(i);
	$('#form_twitter').hide();
	$('.linkedin_block .user_container').remove();
	$('.linkedin_block .main_container').remove();
	$('.twitter_block .user_container .content').remove();
	$('.twitter_block .main_container .tweet').each(function(){
		$(this).remove();
	});
	$('.twitter_block .user_container .error').remove();
	$('#twitter_name').val('');
	$('#hidden_name').val('');
	if(IN.User.isAuthorized()){
		IN.User.logout();
	}
	
	$('.facebook_block .user_container .content').remove();
	$('.facebook_block .main_container .facebook_post').each(function(){
		$(this).remove();
	});
	$('.logout_from_facebook').hide();
}


$(document).ready(function(){
	  $.getScript('//connect.facebook.net/en_US/sdk.js', function(){
		FB.init({
		  appId: '1825936454383206',
		  version: 'v2.7' 
		});     
	  });
	  
	$('#facebook_link').click(function(e){
		e.preventDefault();
		resetAllApies();
		$('#form_twitter').hide();
		$('.logout_from_facebook').show();
		FacebookApi();
		window.clearInterval(window.intervalId);
		window.intervalId = setInterval(FacebookApi, 10000);
		
	});
	$('#linkedin_link').click(function(e){
		e.preventDefault();
		resetAllApies();
		location.href = '/Controller.php?linkedin=1';
	});
	$('#twitter_link').click(function(e){
		e.preventDefault();
		resetAllApies();
		$('#form_twitter').show();
	});
	$('.logout_from_facebook .logout').click(function(){
		resetAllApies();
		FB.logout();
		$('.facebook_block .user_container .content').remove();
		$('.facebook_block .main_container .facebook_post').each(function(){
			$(this).remove();
		});
		$('.logout_from_facebook').hide();
	});
	$('#twitter_button').click(function(e){
		e.preventDefault();
		if($('#twitter_name').val() != $('#hidden_name').val()){
			$('#hidden_name').val($('#twitter_name').val());
			TwitterApi();
			window.clearInterval(window.intervalId);
			window.intervalId = setInterval(TwitterApi, 10000);
		}		
	});
});
