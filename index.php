<!DOCTYPE html>
<html class="no-js"
      lang="en"
      xmlns="http://www.w3.org/1999/html"
      xmlns="http://www.w3.org/1999/html">


<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 5/20/2015
 * Time: 9:45 AM
 */

require_once('header.php');

?>
<body onload="prettyPrint()">
<div class="row">
    <div class="small-12">
        <div class="top-bar"></div>
    </div>
    <div class="small-12">
        <h1>Tutorial on implementing Ratchet</h1>
    </div>

</div>
<div class="row">
    <div class="small-3 large-2 columns left hide-for-print">
        <img src="img/me.jpg" >
        <hr>
        <h5 class="subheader">Robert Jackson</h5><h5 class="small subheader">CS-290 5/24/2015 </h5>
        <hr>
        <ul class="no-bullet">
            <li><a class="webicon googleplus large" href="#">Google+</a></li>
            <li><a class="webicon facebook large" href="#">FaceBook</a></li>
            <li><a class="webicon github large" href="#">GitHub</a></li>
        </ul>
    </div>

    <div class="small-12 large-10 columns ">
        <!-- Intro -->
        <section>

            <h2 class="subheader">Introduction</h2>

            <p>Welcome to my tutorial on the implementation of the <a href="http://socketo.me">Ratchet library</a>. I wrote this as part of my class, CS-290 Web Development,
                I'm taking at Oregon State University. I found that,
                although the tutorial provided by the vendor was very helpful to get started, there are a few aspects upon
                which to improve, especially from the stand-point of the beginner. Furthermore,  I ran into issues implementing
                their tutorial for pushing information from the server to the client. Namely, I could not run both processes they employ in the tutorial (one for chatting and another for pushing to the clients from the server) at the same time. The great thing is, once we're done here, you'll be able to do just that. I have used many aspects of the original tutorial found <a href="http://socketo.me/docs/hello-world">here</a>, but manipulated and added to it to produce the desired result. This will allow us to do both the sending of data between clients, but also from the server to the clients.</p>

            <h3 >Required reading/downloading:</h3>

            <p>Here are a few links to get started.</p>
            <ul>
                <li><a href="https://getcomposer.org/doc/00-intro.md">Composer required download</a></li>
                <li><a href="https://pecl.php.net/package/zmq/1.1.2/">PHP ZMQ required download</a></li>
                <li><a href="https://www.engineyard.com/articles/websocket">Good explanation on websockets' utility</a></li>
            </ul>
            <p>If I lost you at the above requirements, don't fret, I'll be walking though some of it it. However, without the
                following basic understanding and apparatus, you may be quite lost.</p>

            <h3 >Additional Requirements (somewhat obvious):</h3>
            <ul>
                <li>You need a web server with php version >= 5.4 installed</li>
                <li>Functional understanding of Javascript, PHP, and HTML</li>
            </ul>

        </section>
        <!-- beginning files, loading ratchet, myApp -->
        <section>

        <h2 class="subheader">Humble Beginnings</h2>
        <h3>Websockets and Why Ratchet</h3>
        <p> Ratchet is a very well thought-out library for implementing websockets. In days past in order
            to do some of the really fun interactive stuff we do now on the web with live interactions between users and
            immediate updates from the server to a page, you had to fake it to an extent, or use things like
            those pesky java applets. Here is a good explanation on why websockets are so cool: <a href="https://www.engineyard.com/articles/websocket">Engine - on Websockets</a>
        </p>
        <p>Ratchet is by no means the only player in the field, However, it does a fine job of making the process just that much easier. Ratchet employs some other libraries that we'll be using including <a href="https://pecl.php.net/package/zmq/1.1.2/">ZMQ</a>, and <a href="http://reactphp.org/">React</a> by the same developer as Ratchet. Ratchet requires React to run. As I mentioned, there are alternatives and here are a few to look into if you are interested.
        </p>
        <ul>
            <li><a href="https://github.com/ghedipunk/PHP-WebSockets">PHPWebSockets</a></li>
            <li><a href="http://wrench.readthedocs.org/en/latest/index.html">Wrench</a></li>
            <li> Of course <a href="https://code.google.com/p/phpwebsocket/">Google has a library</a></li>
            <li><a href="https://github.com/Vinelab/minion">Minion</a> (specific to WAMP - Web Application Messaging Protocol)</li>
            <li><a href="http://phptrends.com/category/70">Phptrends.com</a> keeps a list</li>
        </ul>
        <h3 >Getting Ratchet Installed:</h3>
        <p> Ratchet has done a great job of getting you started with a <a href="http://socketo.me/docs/hello-world">tutorial</a>, which I too followed from scratch at first. The main difference between the two tutorials, is that I handle the server process differently for a specific reason, and  the goal is also a little different: Whereas Ratchet's will guide you though to sending message I've managed the marry two processes, one for sending from the server, and another for client communication. The first step is to download and unpack composer into your root directory. I found this to be the easiest method:
            <pre class="prettyprint">

                php -r "readfile('https://getcomposer.org/installer');" | php

            </pre>
        </p>

        <h4>Code this: composer.json </h4>
<pre class="prettyprint linenums"><code>
{
    "autoload": {
        "psr-0": {
            "MyApp": "src"
        }
    },
    "require": {
        "cboden/ratchet": "0.3.*"
        "react/zmq": "0.2.*|0.3.*"
    }
}
</code></pre>
        <p>This goes is your composer.json file located in your root directory where composer.phar is now also sitting. You can change the "MyApp" to whatever you want, just make sure that you change the appropriate lines in the files we will generate. MyApp is essentially the location, a folder, under which we're placing our application. It will interact with the classes we're about to download via composer for handling the websockets. Ratchet requires Symfony2, and this will also allow us to do what we do with Line 4. It is going to help us auto load everything we need on the pages we make. Line 4 is the standard they are using (psr-0 is outdated, but messing with this now is outside of the scope of this article ) This file is establishing that location with line 5.  Lines 8-10 are going to load the package containing all classes we need for the websockets.  Now every time we call </p>
<pre class="prettyprint">

    require dirname(__DIR__) . '/vendor/autoload.php';


</pre>
       <p>we have access to all the classes from Ratchet and React/ZMQ, but also to our own application. To understand more on how this is all fitting together you can go to <a href="http://symfony.com/">Symfony.com</a>.</p>
        <h4><a class="tip">Extra Tip: Errors with ZMQ</a></h4>
            <div class="extraTip">
            <p>The biggest trouble I had before firing up Ratchet was with the ZMQ extension.  The Ratchet tutorial doesn't get into this, so, I will. In fact, I didn't see it mentioned in the beginning tutorial. This is probably because it's not required by Ratchet specifically. The ZMQ extension is very specific to your version of php. If you go to ZeroMQ.org (it's what you'll find if you google the extension), you'll get directed to use http://snapshot.zero.mq/ for Windows installations. Don't go there; it's dead anyway. I didn't check the Linux versions. They might be solid, but your best option is to go here, <a href="https://pecl.php.net/package/zmq/1.1.2/windows"">PECL</a>, and download it.  You'll have to determine if you are using a threaded or non-threaded version, and if you're on a 64 bit or 32 bit version of php. Almost for certain, you're running a 32 bit, so, that narrows things down, but the rest could mean trial and error with the right dll. I ended up systematically trying the threaded and non-threaded.</p>
                    <p>You might get this if you're using the wrong one</p>
                    <div class ="code">
<pre><code>
Loading composer repositories with package information
Updating dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.

Problem 1
- react/zmq v0.3.0 requires ext-zmq * -> the requested PHP extension zmq is missing from your system.
- react/zmq v0.2.0 requires ext-zmq * -> the requested PHP extension zmq is missing from your system.
- Installation request for react/zmq 0.2.*|0.3.* -> satisfiable by react/zmq[v0.2.0, v0.3.0].
</code></pre>
                    </div>
            </div>
</section>

        <!-- Application -->
        <section>

        <h2 class="subheading">Next up: Writing the Application</h2>
            <p>Provided you didn't get any further errors, we can move on to the next file. Here we're going to write the implementation file for a class that is used in Ratchet to trigger further actions on events such as on connect, on close, on message, etc. The great part about about this file is that it is extensible. We can add more functions as needed, and they are available to us when we later run the process that checks for these events.  For any veterans the way that all works may or may not have been entirely obvious from the beginning, but for me, this is a step deeper into OOP than I've been before. I find the levels of abstraction to be somewhat confusing at first. Since this is a beginners' approach, I'll do my best to break it all down. </p>
        <h3> Code this: actions.php</h3>
        <ul class="tabs" data-tab role="tablist">
            <li class="tab-title active" role="presentational" ><a href="#panel2-1" role="tab" tabindex="0" aria-selected="true" controls="panel2-1">Code</a></li>
            <li class="tab-title" role="presentational" ><a href="#panel2-2" role="tab" tabindex="0"aria-selected="false" controls="panel2-2">Plain text</a></li>
        </ul>
        <div class="tabs-content">
            <section role="tabpanel" aria-hidden="false" class="content active" id="panel2-1">
        <pre class="prettyprint linenums"><code>
        namespace MyApp;
        use Ratchet\MessageComponentInterface;
        use Ratchet\ConnectionInterface;

        class Chat implements MessageComponentInterface {
        protected $clients;

        public function __construct() {
            $this->clients = new \SplObjectStorage;
        }

        public function onOpen(ConnectionInterface $conn) {
            // Store the new connection to send messages to later
            $this->clients->attach($conn);

            echo "New connection! ({$conn->resourceId})\n";

        }

        public function onMessage(ConnectionInterface $from, $msg) {
            $numRecv = count($this->clients) - 1;
            echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

            foreach ($this->clients as $client) {
                if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
                }
            }
        }

        public function onClose(ConnectionInterface $conn) {
            // The connection is closed, remove it, as we can no longer send it messages
            $this->clients->detach($conn);

            echo "Connection {$conn->resourceId} has disconnected\n";
        }

        public function onError(ConnectionInterface $conn, \Exception $e) {
            echo "An error has occurred: {$e->getMessage()}\n";

            $conn->close();
        }

        public function onNewObject( $msg) {
            echo $msg;

            foreach ($this->clients as $client) {

            $client->send($msg);
        }

    }
}

        </code></pre>
            </section>
            <section role="tabpanel" aria-hidden="true" class="content" id="panel2-2">
                <pre><code>
        namespace MyApp;
        use Ratchet\MessageComponentInterface;
        use Ratchet\ConnectionInterface;

        class Chat implements MessageComponentInterface {
        protected $clients;

        public function __construct() {
            $this->clients = new \SplObjectStorage;
        }

        public function onOpen(ConnectionInterface $conn) {
            // Store the new connection to send messages to later
            $this->clients->attach($conn);

            echo "New connection! ({$conn->resourceId})\n";

        }

        public function onMessage(ConnectionInterface $from, $msg) {
            $numRecv = count($this->clients) - 1;
            echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

            foreach ($this->clients as $client) {
                if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
                }
            }
        }

        public function onClose(ConnectionInterface $conn) {
            // The connection is closed, remove it, as we can no longer send it messages
            $this->clients->detach($conn);

            echo "Connection {$conn->resourceId} has disconnected\n";
        }

        public function onError(ConnectionInterface $conn, \Exception $e) {
            echo "An error has occurred: {$e->getMessage()}\n";

            $conn->close();
        }

        public function onNewObject( $msg) {
            echo $msg;

            foreach ($this->clients as $client) {

            $client->send($msg);
        }

    }
}

                </code></pre>
            </section>
        </div>
        <h3>Break down:</h3>
       <p> <strong>Lines 2 - 5</strong> are <a href="http://symfony.com/">Symfony</a> constructs, wherein we're first declaring to what "namepace" this file belongs. Since it's part of MyApp, this file should also be located under /src/MyApp The title of the document is unimportant, because Symfony is going load everything we put there. Additionally, the "uses" statements are adding the MessageComponentInterface and ConnectionInterface classes from Ratchet we're going to use to the file.</p>
        <p><strong>Line 6.</strong> If you were to look in vendor/cboden/ratchet/src, and look at MessageComponentInterface.php, you'll see it contains all of the functions in the file we just wrote excepting the last function, which is my addition and about which I'll say more later.  It has only the declarations, and none of the functionality we added in the file, hence, our class chat implements MessageComponentInterface. </p>
        <p><strong>Lines 9-10</strong> are the constructor function for the class. It instantiates a new SplObjectStorage. This object holds all the connections / users' information and this is stored in the variable clients.</p>
        <p><strong>onOpen</strong> This is what is going to fire when the user connects to the service. We could trigger other events to happen at this point, for example, save the users information in a database, start a log, etc. At the moment however, it only echos in the console that a connection has been established together with the associated id obtained though $conn->resourceId </p>
        <p><strong>onMessage</strong> Here we get to a key function of the class, the onMessage function. It takes a connectionInterface object (the sender) and a message as arguments. It makes sure that it won't resend this message to the origin, in the foreach statement. The message is pushed it out in real-time to all attached clients via the send($msg) function. </p>
        <p><strong>onClose</strong> This is self-explanatory. If anything else needs to be accomplished upon disconnect of a client, this is where it should go. It calls the detach function on the connection object. </p>
        <p><strong>onError</strong> This is self-explanatory</p>
        <p><strong>onNewObject</strong> This is the function that I have added to the system. It looks redundant in it's form, i.e. it is in essence the same as the onMessage function above. There is one key difference in the function, and that is, I'm pushing the message to all the clients. Later when we add the ability to push events to all the users directly from the server, not from user to user, we'll utilize this function instead of just the onMessage function. I wanted to show that the same class can be extended with custom functions to achieve more functionality.</p>
            </section>
        <!-- The server File-->
        <section>

            <h2 class="subheader">A Server push:</h2>

            <p>The next task is to write the server side code that I want to use in order to push something to all clients who are connected. Because I want to be able to push various types of content to them, I'm going to make it send things in JSON. Specifically, I wanted to send my users a Youtube video. The following file does just that. Ideally we should be keeping it in a new class, and adding this file to MyApp/src. We are not making use of any of the classes in the autoloader here. Therefore, this file can reside anywhere you choose.</p>
            <h3>Code this: videoPush.php</h3>
            <ul class="tabs" data-tab role="tablist">
                <li class="tab-title active" role="presentational" ><a href="#vp1" role="tab" tabindex="0" aria-selected="true" controls="vp1">Code</a></li>
                <li class="tab-title" role="presentational" ><a href="#vp2" role="tab" tabindex="0"aria-selected="false" controls="vp2">Plain text</a></li>
            </ul>
            <div class="tabs-content">
                <section role="tabpanel" aria-hidden="false" class="content active" id="vp1">
                <pre class="prettyprint linenums"><code>$entryData = array(
                        'category' => 'room1'
                        , 'src'  => 'https://www.youtube.com/embed/4WbxYzAG2I4'
                        , 'when'     => time()
                        ,'requestType' => "videoUpdate"
                        ,'message' => "now playing:  ");



                        $context = new ZMQContext();
                        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my vid');
                        $socket->connect("tcp://localhost:5555");

                        $socket->send(json_encode($entryData));
                    </code></pre>
                </section>
                <section role="tabpanel" aria-hidden="false" class="content" id="vp2">
                <pre><code>$entryData = array(
                        'category' => 'room1'
                        , 'src'  => 'https://www.youtube.com/embed/4WbxYzAG2I4'
                        , 'when'     => time()
                        ,'requestType' => "videoUpdate"
                        ,'message' => "now playing:  ");



                        $context = new ZMQContext();
                        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my vid');
                        $socket->connect("tcp://localhost:5555");

                        $socket->send(json_encode($entryData));
                    </code></pre>
                </section>
            </div>
            <p>In this file I placed a simple associative array just to hold some information that we can send.  It is also identifying of the type being sent. I'll use that aspect to differentiate it on the client side's javascript. Lines 10-14 are going to send my array to the port 5555 where we will have the service already listening. ZMQ and subsequently the onmessage function of the server.php is expecting to get a string, so, we need to encode the array before it gets sent. We'll subsequently decode it in javascript later. I could imagine expanding this to keep track of the time the video is playing, and send more content when it's over, or any number of things are possible, for example live scores, stock prices, etc. The instant this script runs, it's immediately picked up by all clients attached. This is the reason we call ZMQ::SOCKET_PUSH, as opposed to SOCKET_PULL. Notice here we are not going though React to start ZMQ as we will later. This will only change the call to ZMQ minimally.</p>
</Section>
        <!-- Sockets and starting the server -->
        <section>

        <h2 class="subheader">Making the Sockets - Starting the Listener</h2>
        <h3> Code this: server.php</h3>
        <ul class="tabs" data-tab role="tablist">
            <li class="tab-title active" role="presentational" ><a href="#actions1" role="tab" tabindex="0" aria-selected="true" controls="actions1">Code</a></li>
            <li class="tab-title" role="presentational" ><a href="#actions2" role="tab" tabindex="0"aria-selected="false" controls="actions2">Plain text</a></li>
        </ul>
        <div class="tabs-content">
            <section role="tabpanel" aria-hidden="false" class="content active" id="actions1">
                <pre class="prettyprint linenums">
                    <code>
require dirname(__DIR__) . '/vendor/autoload.php';

$loop   = React\EventLoop\Factory::create();
$vid = new MyApp\Chat;

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($vid, 'onNewObject'));



// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(

                $vid

        )
    ),
    $webSock
);

$loop->run();
                    </code>
                </pre>
            </section>
            <section role="tabpanel" aria-hidden="false" class="content" id="actions2">
                <pre>
                    <code>
require dirname(__DIR__) . '/vendor/autoload.php';

$loop   = React\EventLoop\Factory::create();
$vid = new MyApp\Chat;

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($vid, 'onNewObject'));

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(

                $vid

        )
    ),
    $webSock
);

$loop->run();
                    </code>
                </pre>
            </section>
        </div>
        <h3>Break down: It's alright</h3>
        <p>This file belongs in the bin directory. This time we autoload to get React and Ratchet. We could instead call "use Ratchet\Server..etc", but this is why Symfony is nice; it's all there now.
            <p>I will try to unpack what is going on in this section in simpler terms. The variable, $loop, is going to hold a new instance of the create function in the factory class. This starts a loop that will be aware of events we send to it. Next, we store a new instance of our application for later use.

       <p></p>
        <dl>
            <dt>Line 8-10</dt>
            <dd>This initializes the interface in react to use ZMQ. This usage of ZMQ is how it's typically started. There are a bunch of examples <a href="http://zguide.zeromq.org/php:all">here</a>. The only difference is that we're going through the React library, a dependency of Ratchet. Next, we are binding this service to a specific port, and saying that the only thing it should be listening to, is itself, hence 127.0.0.1. We don't want everything sent to 5555 to cause a function to run; just what came from the server itself.<dd>
            <dt>Line 11</dt>
            <dd>This is where we are going to tell ZMQ what function to run when it picks up an event on this port. This is where I've designated the onNewObject, as seen in actions.php to run. In the Ratchet tutorials this is similar to the piece that is used to hook up WAMP methods for server interactions. We don't need WAMP for this application, but we do need that piece. The reason for this is that we don't want our server push to act like a client; it needs it's own way to communicate with the attached clients. However, since only one event loop can be running at any one time in ratchet, we have to pack more into it.  The application class we wrote, stored in the variable on line 22, is wrapping or wrapped by the following: </dd>
            <dt>WsServer</dt>
            <dd>This is going to allow us to do things inside the browser. It utilizes the <a rel="external" href="http://dev.w3.org/html5/websockets/">W3C WebSocket API</a> The wsServer class  set to listen from any source on port 8080, using the same loop as before. The wrapping leads to more functionality. If we needed session management with Ratchet we could wrap it in the Session class as well.</dd>
            <dt>httpServer</dt>
            <dd>This step acts like a buffer to get a complete http request before moving on. Without it, the I/O layer doesn't really know what the WebSocket layer is talking about.</dd>
            <dt>I/O server</dt>
            <dd>This stores all the connections, handles the data being transferred, and errors as well. We could communicate between clients using only this piece but then not through the web browser.</dd>
        </dl>

       <p> I've essentially combined two processes into one file. It is, however, so very confusing to figure out what each piece is returning, and what really happens when its result is packed into a newly instantiated object. This took a fair amount of trial and error. Once I realized that Ratchet only likes one loop running, it started to come together. The good news is that we're done with the hard part. The last step here is to open up your console in the directory where your /bin/server.php resides and type php server.php. It should start running. If there is a problem, make sure that your php ini is set to report all errors and then it's usually easy to find out what went wrong. Now all that is left is to make the page where the user gets tapped in to all this goodness. </p>
        </section>
       <!-- Making the Client Connection -->
        <section>
            <h2>Making the Client Connection:</h2>

            <p>You can make the following file how you'd prefer. To make sure that the javascript events also show up in the page, you should note the naming conventions I've used. I established two places for content to appear in separate divs on lines 9 and 10, and an input space for sending a message from the client.</p>
            <h3>Code this: index.html</h3>
            <ul class="tabs" data-tab role="tablist">
                <li class="tab-title active" role="presentational" ><a href="#sp1" role="tab" tabindex="0" aria-selected="true" controls="sp1">Code</a></li>
                <li class="tab-title" role="presentational" ><a href="#sp2" role="tab" tabindex="0"aria-selected="false" controls="sp2">Plain text</a></li>
            </ul>
            <div class="tabs-content">
                <section role="tabpanel" aria-hidden="false" class="content active" id="sp1">


<xmp class="prettyprint linenums lang-html">
    <head lang="en">
        <meta charset="UTF-8">
        <title></title>

    </head>
    <body>

    <div id="messages"></div>
    <div id ="videoFrame">

    </div>
    <div>
        <form>
            <input type="text" id="msgText" >
            <input type="button" id="msgSend" value=">">
        </form>
    </div>
    <script>
        function Transfer(url, usr, msg, req) {
            this.src = url;
            this.usr = usr;
            this.message = msg;
            this.requestType = req;

        }


        var conn = new WebSocket('ws://localhost:8080');
        conn.onopen = function(e) {
            console.log("Connection established!");

            //give user an ID?
        };

        conn.onmessage = function(e) {
            console.log(e.data);

            var reply = JSON.parse(e.data);

            // determine what type it is
            //handle empty case
            switch(reply['requestType']){

                case "message":

                    var chat = document.createElement("div");
                    var cwind = document.getElementById("messages");
                    cwind.appendChild(chat);
                    cwind.lastElementChild.innerHTML = reply["message"];
                    break;
                case "videoUpdate":
                    var frame = document.getElementById('videoFrame');
                    var vid = document.createElement("iframe");
                    vid.setAttribute("src",  encodeURI(reply["src"]));

                    frame.appendChild(vid);

                    break;
                default:
                    console.log("nothing gaind; nothing earned")
            }

        };

        var button = document.getElementById("msgSend");

        button.onclick = function() {

            var val = document.getElementById("msgText").value;
            console.log( ' button sending this '+ val);
            sendMsg(val);
        };



        function sendMsg(val){
            // build a JSON string that has user info, etc
            msgObj = new Transfer("", "", val, "message");

            conn.send(JSON.stringify(msgObj));

        }

    </script>

</xmp>

                </section>
                <section role="tabpanel" aria-hidden="false" class="content" id="sp2">
                    <xmp>


                        <div id="messages"></div>
                        <div id ="videoFrame">

                        </div>
                        <div>
                            <form>
                                <input type="text" id="msgText" >
                                <input type="button" id="msgSend" value=">">
                            </form>
                        </div>
                        <script>
                            function Transfer(url, usr, msg, req) {
                                this.src = url;
                                this.usr = usr;
                                this.message = msg;
                                this.requestType = req;

                            }


                            var conn = new WebSocket('ws://localhost:8080');
                            conn.onopen = function(e) {
                                console.log("Connection established!");

                                //give user an ID?
                            };

                            conn.onmessage = function(e) {
                                console.log(e.data);

                                var reply = JSON.parse(e.data);

                                // determine what type it is
                                //handle empty case
                                switch(reply['requestType']){

                                    case "message":

                                        var chat = document.createElement("div");
                                        var cwind = document.getElementById("messages");
                                        cwind.appendChild(chat);
                                        cwind.lastElementChild.innerHTML = reply["message"];
                                        break;
                                    case "videoUpdate":
                                        var frame = document.getElementById('videoFrame');
                                        var vid = document.createElement("iframe");
                                        vid.setAttribute("src",  encodeURI(reply["src"]));

                                        frame.appendChild(vid);

                                        break;
                                    default:
                                        console.log("nothing gaind; nothing earned")
                                }

                            };

                            var button = document.getElementById("msgSend");

                            button.onclick = function() {

                                var val = document.getElementById("msgText").value;
                                console.log( ' button sending this '+ val);
                                sendMsg(val);
                            };



                            function sendMsg(val){
                                // build a JSON string that has user info, etc
                                msgObj = new Transfer("", "", val, "message");

                                conn.send(JSON.stringify(msgObj));

                            }

                        </script>
                    </xmp>


                </section>
            </div>
            <p>The javascript here is fairly simplistic. Once initialized, it listens to the websocket that we left running. Then the function <strong>onmessage</strong> will pick up anything coming its way. Depending on the json object sent, we can now make our page do different things with that information. The function <strong>sendMsg</strong> does just what you'd expect. It hits the websocket with a message, that message is instantly broadcast to all. At this point it's smart to recognize that I have given no thought to security. This probably has some big holes, but filling them is outside of the scope of this tutorial. The fact that we designed the application to deal in JSON objects, allows us to give users the ability to immediately broadcast different events to all attached clients instantly.</p>

        </section>
        <!-- Tying it all together -->
        <section>

            <h2>The fun begins!:</h2>
        <p>At this point you can open two new tabs, and point them to the index.html. Next, check to make sure your service is still running in the console. It should tell you if something failed. Clients can now send messages back and forth. If you have a third tab open, navigate to the videoPush.php file we made. That should cause both tabs with client connections to load the video. </p>
        <p>Please feel free to contact me with questions, and by all means contact me if my understanding is wrong. I want to use this opportunity to learn, and thus both questions and criticisms / corrections will help me on my way.</p>
        </section>
    </div>


</div>

<script src="js/vendor/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script src="js/foundation/foundation.tab.js"></script>
<script>
    $(".tip").click(function() {
        $(".extraTip").slideToggle("fast")
    });

</script>
<script>
    $(document).foundation();
</script>

</body>
</html>