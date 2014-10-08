<?php

require_once('twitter-client.php');
require_once('semantria/session.php');

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

    public function __construct($consumer_key, $consumer_secret, $access_key, $access_secret) {

        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->access_key = $access_key;
        $this->access_secret = $access_secret;
    }

    /**
     * This function fetches the twitter list
     * 
     * @param array $tweetSearchParams The Twitter Search Parameters that are passed to Twitter API.
     * 
     * @return array
     */
    public function fetchTweets($tweetSearchParams) {
        $tweets = $this->getTweets($tweetSearchParams);

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
        $Client->set_oauth($this->consumer_key, $this->consumer_secret, $this->access_key, $this->access_secret);

        $tweets = $Client->call('search/tweets', $tweetSearchParams, 'GET'); //call the service and get the list of tweets

        unset($Client);

        return $tweets;
    }

    /**
     * @return \Semantria\Session
     */
    protected function processTweet($tweets) {
        $session = $this->initilizeSemantriaSession();
        $results = array();
        foreach ($tweets['statuses'] as $tweet) { //foreach of the tweets that we received
            $results[] = array(//add the tweet message in the results
                'user' => $tweet['user']['name'],
                'text' => $tweet['text'],
                'url' => 'https://twitter.com/' . $tweet['user']['name'] . '/status/' . $tweet['id_str'],
            );

            list($doc, $status) = $this->addTwetsToQue($tweet, $session);
        }
        $scores = $this->processQueuedTweets($doc, $session);

        $this->checkSentimentScore($scores);
        echo "\r\n";
        unset($tweets);
        return $results;
    }

    /**
     * @return \Semantria\Session
     */
    protected function initilizeSemantriaSession() {
// Initializes new session with the serializer object and the keys.
        $session = new \Semantria\Session(SEMANTRIA_CONSUMER_KEY, SEMANTRIA_CONSUMER_SECRET, NULL, NULL, TRUE);

        // Initialize session callback handler
        $callback = new SessionCallbackHandler();
        $session->setCallbackHandler($callback);
        return $session;
    }

    /**
     * @param $tweet
     * @param $session
     * @return array
     */
    protected function addTwetsToQue($tweet, $session) {
        $doc = array("id" => uniqid(''), "text" => $tweet['text']);

        // Queues document for processing on Semantria service
        $status = $session->queueDocument($doc);
        // Check status from Semantria service
        if ($status == 202) {
            echo "Document ", $doc["id"], " queued successfully.", "\r\n";
            return array($doc, $status);
        }
        return array($doc, $status);
    }

    /**
     * @param $doc
     * @param $session
     * @return array
     */
    protected function processQueuedTweets($doc, $session) {
// Count of the sample documents which need to be processed on Semantria
        $length = count($doc["id"]);
        $scores = array();

        while (count($scores) < $length) {
            echo "Please wait 10 sec for documents ...", "\r\n";
            // As Semantria isn't real-time solution you need to wait some time before getting of the processed results
            // In real application here can be implemented two separate jobs, one for queuing of source data another one for retreiving
            // Wait ten seconds while Semantria process queued document
            sleep(10);

            // Requests processed results from Semantria service
            $status = $session->getProcessedDocuments();
            // Check status from Semantria service
            if (is_array($status)) {
                $scores = array_merge($scores, $status);
            }
            echo count($status), " documents received successfully.", "\r\n";
        }
        return $scores;
    }

    /**
     * @param $scores
     */
    protected function checkSentimentScore($scores) {
        foreach ($scores as $data) {
            // Printing of document sentiment score
            $data["id"];
            if ($data["sentiment_score"] < 0) {
                echo "negative";
                echo "\n";
            }
            if ($data["sentiment_score"] == 0) {
                echo "neutral";
                echo "\n";
            }
            if ($data["sentiment_score"] > 0) {
                echo "positive";
                echo "\n";
            }
        }
    }

}

class SessionCallbackHandler extends \Semantria\CallbackHandler {

    function onRequest($sender, $args) {
        //$s = json_encode($args);
        //echo "REQUEST: ", htmlspecialchars($s), "\r\n";
    }

    function onResponse($sender, $args) {
        //$s = json_encode($args);
        //echo "RESPONSE: ", htmlspecialchars($s), "\r\n";
    }

    function onError($sender, $args) {
        $s = json_encode($args);
        echo "ERROR: ", htmlspecialchars($s), "\r\n";
    }

    function onDocsAutoResponse($sender, $args) {
        //$s = json_encode($args);
        //echo "DOCS AUTORESPONSE: ", htmlspecialchars($s), "\r\n";
    }

    function onCollsAutoResponse($sender, $args) {
        //$s = json_encode($args);
        //echo "COLLS AUTORESPONSE: ", htmlspecialchars($s), "\r\n";
    }

}
