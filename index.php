<?php
	session_start();
	$db = new mysqli('hostname', 'user', 'password', 'database');
	if(mysqli_connect_errno())
	{
		die('Unable to connect to the database');
	}

	$data = array();
	$view = '';

	if(!empty($_GET['import']))
	{
		$url = 'https://api.github.com/search/repositories?q=language:php&sort=stars&order=desc&per_page=100';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'VICTR Candidate Assessment');
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response);

		if(empty($data->items))
		{
			$_SESSION['message'] = array('danger', 'There was an error importing the data from GitHub.');
			header('location: index.php');
			exit();
		}
		else
		{
			$query = $db->prepare('INSERT INTO popular_php_repos (id, name, url, created_at, pushed_at, description, stargazers_count) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), url = VALUES(url), pushed_at = VALUES(pushed_at), description = VALUES(description), stargazers_count = VALUES(stargazers_count)');
			foreach($data->items as $d)
			{
				$query->bind_param('isssssi', $d->id, $d->name, $d->html_url, date('Y-m-d H:i:s', strtotime($d->created_at)), date('Y-m-d H:i:s', strtotime($d->pushed_at)), $d->description, $d->stargazers_count);
				$query->execute();
			}

			$_SESSION['message'] = array('success', 'The import is complete!');
			header('location: index.php');
			exit();
		}
	}
	else if(!empty($_GET['id']))
	{
		$view = 'single';

		if(!preg_match('/^[0-9]+$/', $_GET['id']))
		{
			$_SESSION['message'] = array('danger', 'That‘s a bad id.');
			header('location: index.php');
			exit();
		}
		$query = $db->query("SELECT * FROM popular_php_repos WHERE id = " . $db->real_escape_string($_GET['id']));

		$data = $query->fetch_object();

		if(empty($data))
		{
			$_SESSION['message'] = array('danger', 'That‘s a bad id.');
			header('location: index.php');
			exit();
		}
	}

	else
	{
		$result = $db->query('SELECT * FROM popular_php_repos ORDER BY stargazers_count DESC');
		$data = array();
		while($row = $result->fetch_object())
		{
			$data[] = $row;
		}
	}
	$db->close();
?>

<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Programming Assessment</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <style>
			body {
				padding-top: 3em;
			}
        </style>
    </head>
    <body>

    	<div class="container">

		<?php if(!empty($_SESSION['message'])) : ?>
		<div class="alert alert-<?php echo $_SESSION['message'][0] ?>">
			<?php echo $_SESSION['message'][1] ?>
		</div>
		<?php unset($_SESSION['message']); ?>
		<?php endif; ?>

		<?php if($view == 'single') : ?>

		<ol class="breadcrumb">
			<li><a href="index.php">All Repositories</a></li>
			<li class="current"><?php echo $data->name ?></li>
		</ol>

		<h1 class="page-header"><?php echo $data->name ?></h1>

		<div class="row">
			<div class="col-sm-6">
				<div class="well">
					<?php if(!empty($data->description)) : ?>
						<?php echo $data->description ?>
					<?php else : ?>
						No description available
					<?php endif; ?>
				</div>
				<a href="<?php echo $data->url ?>" class="btn btn-primary">View on GitHub</a>
			</div>
			<div class="col-sm-6">
				<ul class="list-group">
					<li class="list-group-item">Repository ID: <?php echo $data->id ?></li>
					<li class="list-group-item">Stars: <?php echo $data->stargazers_count ?></li>
					<li class="list-group-item">Created: <?php echo date('F j, Y', strtotime($data->created_at)) ?></li>
					<li class="list-group-item">Last Push: <?php echo date('F j, Y', strtotime($data->pushed_at)) ?></li>
				</ul>
			</div>
		</div>

		<?php else : ?>


			<h1 class="page-header">The Most Starred PHP Repositories on GitHub</h1>

			<?php if(empty($data)) : ?>
				<p><em>There are currently no repositories.</em></p>
			<?php endif; ?>

			<p><a href="index.php?import=true" class="btn btn-primary">Import Repositories</a></p>

			<?php if(!empty($data)) : ?>
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Name</th>
						<th>Stars</th>
						<th>Last Push</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($data as $d) : ?>
					<tr>
						<td><a href="index.php?id=<?php echo $d->id ?>"><?php echo $d->name ?></a></td>
						<td><?php echo $d->stargazers_count ?></td>
						<td><?php echo date('F j, Y', strtotime($d->pushed_at)) ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

		<?php endif; ?>
		</div>
    </body>
</html>