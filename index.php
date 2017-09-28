<?php
if($_COOKIE['data'] != false){
	$data_object = json_decode($_COOKIE['data']);
	setcookie("data", false);
}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Тестовое задание Netpeak</title>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script type="text/javascript" src="script.js"></script>
		<script type="text/javascript" src="//platform.linkedin.com/in.js">
			api_key:   77gnk2mb5viqfu
		</script>
	</head>
	<body>
		<div class="bootstrap">
			<div class="container">
				<div class="row header">
					<h1 class="h1">Сервис вывода новостных лент.</h1>
				</div>
				<div class="row">
					<div class="col-md-4 col-md-offset-4 main">
						<a class="links" id="twitter_link" href="#twitter"><img src="/img/Twitter-64.png" alt="twitter"></a>
						<a class="links" id="facebook_link" href="#facebook"><img src="/img/facebook_64.png" alt="facebook"></a>
						<a class="links" id="linkedin_link" href="#linkedin"><img src="/img/Linkedin-64.png" alt="linkedin"></a>
						<form method="post" action="#" id="form_twitter">
							<input placeholder="Enter name" class="form-control" type="text" name="twitter_name" id="twitter_name">
							<input type="hidden" name="hidden_name" id="hidden_name">
							<button id="twitter_button" class="btn btn-primary">Загрузить ленту</button>
						</form>
						<!--<button id="facebook_button" class="btn btn-primary">Facebook</button>-->
					</div>
				</div>
			
				<div class="row twitter_block">
					<div class="user_container"></div>
					<div class="main_container"></div>
				</div>
				<div class="row facebook_block">
					<div class="logout_from_facebook"><button class="logout btn btn-primary">Logout from Facebook account</button></div>
					<div class="user_container"></div>
					<div class="main_container"></div>
				</div>
				<?php

					if($data_object != false){
						
						$html .= '<div class="row linkedin_block">
							<div class="user_container">
								<div class="content">
								<a target="_blank" href="'.$data_object->publicProfileUrl.'">';
							if($data_object->pictureUrls->_total != 0){
								$html .= '<img src="'.$data_object->pictureUrls->values[0].'">';
							}		
						$html .= $data_object->firstName.' '.$data_object->lastName.'</a><span>'.$data_object->headline.'</span></div></div>
							<div class="main_container">
								<div class="linked_description">'.$data_object->currentShare->content->description.'</div>
								<a target="_blank" href="'.$data_object->currentShare->content->resolvedUrl.'"><img src="'.$data_object->currentShare->content->submittedImageUrl.'"></a>
								<div class="linked_title">'.$data_object->currentShare->content->title.'</div>
							</div>
						</div>';
						echo $html;
						
					}
				?>
				
					
				
			</div>
		</div>
	</body>
</html>
