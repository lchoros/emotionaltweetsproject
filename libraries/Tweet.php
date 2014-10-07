<?php
require_once(dirname(__FILE__).'/twitter-client.php');

class Tweet {
        
    protected $consumer_key; //Twitter Consumer Key.
    protected $consumer_secret; //Twitter Consumer Secret.
    protected $access_key; //Twitter Access Key. 
    protected $access_secret; //Twitter Access Secret.
    
    /**
    * The constructor of the class
    * 
    * @param string $consumer_key
    * @param string $consumer_secret
    * @param string $access_key
    * @param string $access_secret
    * 
    * @return Tweet  
    */
    public function __construct($consumer_key, $consumer_secret, $access_key, $access_secret){
        
        $this->consumer_key=$consumer_key;
        $this->consumer_secret=$consumer_secret;
        $this->access_key=$access_key;
        $this->access_secret=$access_secret;
    }
    
    /**
    * This function fetches the twitter list
    * 
    * @param array $tweetSearchParams The Twitter Search Parameters that are passed to Twitter API.
    * 
    * @return array
    */
    public function fetchTweets($tweetSearchParams) {
        $tweets=$this->getTweets($tweetSearchParams);
        
        return $this->processTweet($tweets);
    }
    
    /**
    * Calls the Search/tweets method of the Twitter API for particular Twitter Search Parameters and returns the list of tweets that match the search criteria.
    * 
    * @param mixed $tweetSearchParams The Twitter Search Parameters that are passed to Twitter API.
    * 
    * @return array $tweets
    */
    protected function getTweets($tweetSearchParams) {
        $Client = new TwitterApiClient(); //Use the TwitterAPIClient
        $Client->set_oauth ($this->consumer_key, $this->consumer_secret, $this->access_key, $this->access_secret);

        $tweets = $Client->call('search/tweets', $tweetSearchParams, 'GET' ); //call the service and get the list of tweets
        
        unset($Client);
        
        return $tweets;
    }
    
    protected function processTweet($tweets) {
        
        $results=array();
        foreach($tweets['statuses'] as $tweet) { //foreach of the tweets that we received
                    $results[]=array( //add the tweet message in the results
                        
                        'user'=>$tweet['user']['name'],
                        'text'=>$tweet['text'],
                        'url'=>'https://twitter.com/'.$tweet['user']['name'].'/status/'.$tweet['id_str'],
                    );
                }
        
        unset($tweets);        
        return $results;
    }
}

  
