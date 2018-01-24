# OilyBird
A wrapper module that supports caching, websocket and database functionalities

## Requirements
* PHP >= 5.4
* MySQL >= 4.1
* Redis >= 2.8
* ZMQ >= 4.0

## Overview
* [Technical Overview](#technical-overview)
* [Installation](#installation)
* [Usage](#usage)
* [Contributing](#contributing)
* [License](#license)

## Technical Overview
#### *What components does the OilyBird wrapper utilize?*
(take note that all the PHP dependencies can be found in the composer.json file)

1. **React (PHP)**  
Event-driven, non-blocking I/O with PHP (similar to the event-driven, non-blocking I/O of Node.js)  
http://reactphp.org/  

2. **Ratchet (PHP)**  
Ratchet is a loosely coupled PHP library providing developers with tools to create real time, bi-directional applications between clients and servers over WebSockets.  
http://socketo.me/  
In order to increase the performance (optional), you can use this extension:  
Libevent (an event notification library)  
  * http://libevent.org/  
  * http://windows.php.net/downloads/pecl/releases/libevent/0.0.5/  

3. **Predis Async (PHP)**  
Asynchronous (non-blocking) version of Predis, the full-featured PHP client library for Redis, built on top of React to handle evented I/O.  
https://github.com/nrk/predis-async  
In order to increase the performance (optional), you can use this extension:    
PHP bindings for Hiredis (Redis client)  
https://github.com/nrk/phpiredis

5. **ZMQ**  
ØMQ (also spelled ZeroMQ, 0MQ or ZMQ) is a high-performance asynchronous messaging library, aimed at use in scalable distributed or concurrent applications.  
  * https://en.wikipedia.org/wiki/%C3%98MQ  
  * http://zeromq.org/community  

6. **Redis**  
Redis is an open source, BSD licensed, advanced key-value cache and store.  
http://redis.io/  

7. **Autobahn JS**  
Autobahn|JS is a subproject of Autobahn and provides an open-source implementation of The Web Application Messaging Protocol (WAMP).  
WAMP runs on top of WebSocket and adds asynchronous Remote Procedure Calls and Publish & Subscribe.  
http://autobahn.ws/js/

## Installation
#### *How to setup OilyBird?*

1. **Download and install XAMPP**  
XAMPP is an easy to install Apache distribution containing MySQL, PHP, and Perl.  
It also includes the Eclipse IDE.  
https://www.apachefriends.org/index.html

2. **Install Redis**  
This is needed for server-side caching  
refer to section:  
[Setting Up the Cache](#setting-up-the-cache)

3. **Install ZMQ**  
Although optional, this is used for server-side message queueing, push/pull, publish/subscribe  
refer to section:  
[Setting Up Websockets](#setting-up-websockets)

4. **Install Composer**  
Composer is a tool for dependency management in PHP. It allows you to declare the dependent libraries your project needs and it will install them in your project for you.  
https://getcomposer.org/download/  
After you install composer, go to the directory of the `composer.json` and type on the command line:  
`composer install`  
or alternatively--on Windows--you can right-click on the composer.json and select `Composer Install` from the context menu.  

#### *Setting Up the Cache*

For the cache, we will be needing Redis.  
The installers can be found here:
* Linux version  
http://redis.io/download  
* Windows version  
https://github.com/MSOpenTech/redis/releases  

Composer (this should already be done once you install through Composer)  
Include this in the require section of your `composer.json`:  
```
"predis/predis-async": "dev-master"
```
For more information about this binding, you can check this link:  
https://github.com/reactphp/zmq

#### *Setting Up Websockets*

Websockets are supported by Ratchet PHP from the server-side.   
As for the WAMP sub-protocol having the publish/subscribe functionality, it can be supported by either Predis Pub/Sub or ZMQ.  
The client-side WAMP is supported by Autobahn JS.

If you are to use ZMQ, you need to get the installer here:  
Windows installer  
* http://zeromq.org/intro:get-the-software  
* http://zeromq.org/distro:microsoft-windows  

You would also be needing to setup the PHP binding for ZMQ.  
To download the PHP binding for ZMQ, you can find it in the following sites:  
* http://zeromq.org/bindings:php  
* https://github.com/Polycademy/php_zmq_binaries  
* http://windows.php.net/downloads/pecl/releases/zmq/1.1.2/ 

To make life easier, you can just go to this link since this repository is the most active and recent one:  
http://windows.php.net/downloads/pecl/releases/zmq/1.1.2/  
  1. Download this file and extract it:  
  `php_zmq-1.1.2-5.6-ts-vc11-x64.zip`  
  2. Copy `libzmq.dll` into your php directory (e.g. C:\wamp\bin\php\php5.3.8\)  
  3. Copy the appropriate version of `php_zmq.dll` to your php extension directory (e.g. C:\wamp\bin\php\php5.3.8\ext)
  4. Add the following line to your php.ini:  
  ```
  extension=php_zmq.dll
  ```
  5. Restart your web server to pickup the ini changes

Composer (this should already be done once you install through Composer)  
Include this in the require section of your `composer.json`:  
```
"cboden/ratchet": "0.3.*",
"ext-zmq": "*",
"react/zmq": "0.4.*@dev"
```
For more information about binding React PHP to ZMQ, you can check this link:  
https://github.com/reactphp/zmq

## Usage
You can refer to the files found in the `examples` directory to get an idea on how to use this.

## Contributing
Your contribution to this project is most certainly welcomed.
Feel free to fork this project and make it awesome.

## License
This is an open source project under the MIT license.  For more information, please refer to [LICENSE.md](LICENSE.md)
