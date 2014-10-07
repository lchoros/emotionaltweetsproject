<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<title>Twitter Sentiment Analysis</title>
</head>
<body>
<h1>Twitter Sentiment Analysis</h1>
<p>Type keyword:</p>
<form method="GET">
    <label>Keyword: </label> <input type="text" name="q" /> 
    <input type="submit" />
</form>

<?php

if(isset($_GET['q']) && $_GET['q']!='') {
    require_once(dirname(__FILE__).'/config.php');
    require_once(dirname(__FILE__).'/libraries/Tweet.php');

    $Tweet = new Tweet(TWITTER_CONSUMER_KEY,TWITTER_CONSUMER_SECRET,TWITTER_ACCESS_KEY,TWITTER_ACCESS_SECRET);

    //Search Tweets parameters as described at https://dev.twitter.com/docs/api/1.1/get/search/tweets
    $tweetSearchParams=array(
        'q'=>$_GET['q'],
        'lang'=>'en',
        'count'=>10,
    );
    $results=$Tweet->fetchTweets($tweetSearchParams);


    ?>
    <h2>Search result "<?php echo $_GET['q']; ?>"</h2>
    <table border="1">
        <tr>
            <td>User</td>
            <td>Text</td>
        </tr>
        <?php
        foreach($results as $tweet) {
            
            ?>
            <tr>
                <td><?php echo $tweet['user']; ?></td>
                <td><?php echo $tweet['text']; ?></td>
                
            </tr>
            <?php
        }
        ?>    
    </table>
    <?php
}

?>
  
</body>
</html>