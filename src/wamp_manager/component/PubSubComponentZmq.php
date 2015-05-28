<?php


namespace OilyBird\WampMgr\Component;


use OilyBird\WampMgr\Component\PubSubInterface;
use OilyBird\Conf\Config;

use React\ZMQ\Context;
use React\EventLoop\LoopInterface;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The publish-subscribe component that makes use of ZMQ
 * @author oliver.chong
 *
 */
class PubSubComponentZmq implements PubSubInterface
{
	private $m_cContext;

	//need more testing to check if a single instance of subscriber and publisher can handle multiple channels for pub/sub
	private $m_cSubscriber;
	private $m_cPublisher;

	//push and pull
	private $m_cPusher;
	private $m_cPuller;

	private $m_bHasEventLoop;


	//added by Oliver Chong - April 15, 2015
	/**
	 * The constructor for the PubSubZmq class
	 */
	public function __construct( LoopInterface $loop = null )
	{
		if ( $loop && $loop instanceof LoopInterface )
		{
			$this->m_cContext = new Context( $loop );
			$this->m_bHasEventLoop = true;
		}
		else
		{
			$this->m_cContext = new \ZMQContext();
			$this->m_bHasEventLoop = false;
		}

		$this->m_cSubscriber = null;
		$this->m_cPublisher = null;
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//PUB / SUB

	//added by Oliver Chong - April 16, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::onMessage()
	 */
	public function onMessage( callable $fCallback )
	{
		$this->m_cSubscriber->on( 'messages', function ( $msg ) use ( $fCallback ) {

			var_dump( "PubSubZmq onMessage", $msg, json_encode( $msg, JSON_NUMERIC_CHECK /*| JSON_FORCE_OBJECT*/ ) );

			call_user_func_array( $fCallback, array( json_encode( $msg, JSON_NUMERIC_CHECK /*| JSON_FORCE_OBJECT*/ ) ) );
		} );
	}


	//added by Oliver Chong - April 15, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::publish()
	 */
	public function publish( $sChannel, $sMsg )
	{
		//publish only works on the same place where the event loop runs
		if ( $this->m_cContext instanceof Context )
		{
			$this->initPublisher();

			return $this->m_cPublisher->sendmulti( array( $sChannel, $sMsg ) );
		}
		//if sending messages from anywhere else
		else if ( $this->m_cContext instanceof \ZMQContext )
		{
			return $this->push( array( $sChannel, $sMsg ) );
		}
	}


	//added by Oliver Chong - April 15, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::subscribe()
	 */
	public function subscribe( $sChannel )
	{
		//var_dump( "******************************************* PubSubZmq subscribe", $sChannel );

		$this->initSubscriber();

		if ( $this->m_bHasEventLoop )
		{
			$this->m_cSubscriber->subscribe( $sChannel );
		}
		else
		{
			$this->m_cSubscriber->setSockOpt( \ZMQ::SOCKOPT_SUBSCRIBE, $sChannel );
		}
	}


	//added by Oliver Chong - April 15, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::unsubscribe()
	 */
	public function unsubscribe( $sChannel )
	{
		$this->initSubscriber();

		if ( $this->m_bHasEventLoop )
		{
			$this->m_cSubscriber->unsubscribe( $sChannel );
		}
		else
		{
			$this->m_cSubscriber->setSockOpt( \ZMQ::SOCKOPT_UNSUBSCRIBE, $sChannel );
		}
	}


	//added by Oliver Chong - April 15, 2015
	/**
	 * Initializes the subscriber
	 */
	private function initSubscriber()
	{
		if ( !isset( $this->m_cSubscriber ) || !$this->m_cSubscriber )
		{
			if ( $this->m_bHasEventLoop )
			{
				$this->m_cSubscriber = $this->m_cContext->getSocket( \ZMQ::SOCKET_SUB );
			}
			else
			{
				$this->m_cSubscriber = new \ZMQSocket( $this->m_cContext, \ZMQ::SOCKET_SUB );
			}

			$this->m_cSubscriber->connect( "tcp://". Config::getWampZmqIP() .":". Config::getWampZmqPort() );
		}
	}


	//added by Oliver Chong - April 15, 2015
	/**
	 * Initializes the publisher
	 */
	private function initPublisher()
	{
		if ( !isset( $this->m_cPublisher ) || !$this->m_cPublisher )
		{
			//$this->m_cPublisher = new \ZMQSocket( $this->m_cContext, \ZMQ::SOCKET_PUB );
			$this->m_cPublisher = $this->m_cContext->getSocket( \ZMQ::SOCKET_PUB );
			// Binding to 127.0.0.1 means the only client that can connect is itself
			$this->m_cPublisher->bind( "tcp://". Config::getWampZmqIP() .":". Config::getWampZmqPort() );
		}
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//PUSH / PULL

	//added by Oliver Chong - April 16, 2015
	public function push( array $aData )
	{
		$this->initPusher();

		return $this->m_cPusher->send( json_encode( $aData ) );
	}


	//added by Oliver Chong - April 17, 2015
	public function pull( callable $fCallback )
	{
		$this->initPuller();

		$this->m_cPuller->on( 'message', function ( $msg ) use ( $fCallback ) {

			var_dump( "PubSubZmq PULL onMessage", $msg );

			call_user_func_array( $fCallback, array( $msg ) );
		} );
	}


	//added by Oliver Chong - April 17, 2015
	/**
	 * Initializes the pusher
	 */
	private function initPusher()
	{
		if ( !isset( $this->m_cPusher ) || !$this->m_cPusher )
		{
			$this->m_cPusher = $this->m_cContext->getSocket( \ZMQ::SOCKET_PUSH );
			$this->m_cPusher->connect( "tcp://". Config::getWampZmqIP() .":". Config::getWampZmqPort() );
		}
	}


	//added by Oliver Chong - April 17, 2015
	/**
	 * Initializes the puller
	 */
	private function initPuller()
	{
		if ( !isset( $this->m_cPuller ) || !$this->m_cPuller )
		{
			$this->m_cPuller = $this->m_cContext->getSocket( \ZMQ::SOCKET_PULL );
			// Binding to 127.0.0.1 means the only client that can connect is itself
			$this->m_cPuller->bind( "tcp://". Config::getWampZmqIP() .":". Config::getWampZmqPort() );
		}
	}

}//end class


?>