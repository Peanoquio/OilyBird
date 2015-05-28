<?php

namespace OilyBird\WampMgr\Handler;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//this was based on the example from http://socketo.me/docs/hello-world
/**
 * The socket server component that handles websocket events
 *
 */
class SocketServerHandler implements MessageComponentInterface
{
	//the list of connected clients
	protected $m_aClients;


	/**
	 * The constructor for the SocketServerComponent class
	 */
	public function __construct()
	{
		$this->m_aClients = new \SplObjectStorage;
	}


	/**
	 * (non-PHPdoc)
	 * @see Ratchet.ComponentInterface::onOpen()
	 */
	public function onOpen( ConnectionInterface $conn )
	{
		// Store the new connection to send messages to later
		$this->m_aClients->attach( $conn );

		echo "New connection! ({$conn->resourceId})\n";
	}


	/**
	 * (non-PHPdoc)
	 * @see Ratchet.MessageInterface::onMessage()
	 */
	public function onMessage( ConnectionInterface $from, $msg )
	{
		$numRecv = count( $this->m_aClients ) - 1;
		echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
				, $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

		foreach ( $this->m_aClients as $client )
		{
			if ( $from !== $client )
			{
				// The sender is not the receiver, send to each client connected
				$client->send( $msg );
			}
		}//end loop
	}


	/**
	 * (non-PHPdoc)
	 * @see Ratchet.ComponentInterface::onClose()
	 */
	public function onClose( ConnectionInterface $conn )
	{
		// The connection is closed, remove it, as we can no longer send it messages
		$this->m_aClients->detach( $conn );

		echo "Connection {$conn->resourceId} has disconnected\n";
	}


	/**
	 * (non-PHPdoc)
	 * @see Ratchet.ComponentInterface::onError()
	 */
	public function onError( ConnectionInterface $conn, \Exception $e )
	{
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}

}//end class

?>