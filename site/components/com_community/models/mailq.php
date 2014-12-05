<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .'/components/com_community/models/models.php' );

class CommunityModelMailq extends JCCModel
{
        protected $_receipts = array ();

	/**
	 * take an object with the send data
	 * $recipient, $body, $subject,
	 */
	public function add($recipient, $subject, $body , $templateFile = '' , $params = '' , $status = 0, $email_type = '' )
	{
		$my  = CFactory::getUser();

		// A user should not be getting a notification email of his own action
		$bookmarkStr = explode('.',$templateFile);
		if ($my->id == $recipient && $bookmarkStr[1] != 'bookmarks' && $my->id != 0)
		{
			return $this;
		}

		$db	 = $this->getDBO();


		$date = JFactory::getDate();
		$obj  = new stdClass();

		$obj->recipient = $recipient;

		// This part does a url search in the email body for URL and automatically makes it a linked URL
		// pattern search must starts with www or protocal such as http or https
		$body = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $body);
        $body = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $body);

		$obj->body 		= $body;
		$obj->subject 	= $subject;
		$obj->template	= $templateFile;
		$obj->params	= ( is_object( $params ) && method_exists( $params , 'toString' ) ) ? $params->toString() : '';
		$obj->created	= $date->toSql();
		$obj->status	= $status;
		$obj->email_type = $email_type;

		$db->insertObject( '#__community_mailq', $obj );

		return $this;
	}

    public function addMultiple ($recipient, $subject, $body , $templateFile = '' , $params = '' , $status = 0, $email_type = '' ) {

        $my  = CFactory::getUser();

        // A user should not be getting a notification email of his own action
        $bookmarkStr = explode('.',$templateFile);
        if ($my->id == $recipient && $bookmarkStr[1] != 'bookmarks' )
        {
                return $this;
        }




        $date = JFactory::getDate();
        $obj  = new stdClass();

        $obj->recipient = $recipient;

        // This part does a url search in the email body for URL and automatically makes it a linked URL
        // pattern search must starts with www or protocal such as http or https
        $body = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $body);
        $body = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $body);

        $obj->body 		= $body;
        $obj->subject 	= $subject;
        $obj->template	= $templateFile;
        $obj->params	= ( is_object( $params ) && method_exists( $params , 'toString' ) ) ? $params->toString() : '';
        $obj->created	= $date->toSql();
        $obj->status	= $status;
        $obj->email_type = $email_type;

        $this->_receipts[] = $obj;
    }

    public function send () {
        if (!empty ($this->_receipts)) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->insert('#__community_mailq');
            $query->columns(
                    array (
                        $db->quoteName('recipient'),
                        $db->quoteName('subject'),
                        $db->quoteName('body'),
                        $db->quoteName('status'),
                        $db->quoteName('created'),
                        $db->quoteName('template'),
                        $db->quoteName('email_type'),
                        $db->quoteName('params'),
                    ));
            foreach ( $this->_receipts as $receipt ) {
                $fields = get_object_vars ($receipt);
                $values =   $db->quote($fields['recipient']) . ',' .
                            $db->quote($fields['subject']) . ',' .
                            $db->quote($fields['body']) . ',' .
                            $db->quote($fields['status']) . ',' .
                            $db->quote($fields['created']) . ',' .
                            $db->quote($fields['template']) . ',' .
                            $db->quote($fields['email_type']) . ',' .
                            $db->quote($fields['params']);
                $query->values($values);
            }
            $db->setQuery($query);
            $db->query();
            /* reset list */
            $this->_receipts = array ();
        }
    }
	/**
	 * Restrive some emails from the q and delete it
	 */
	public function get($limit = 100, $markAsSent = false )
	{
		$db	 = $this->getDBO();

		$sql = 'SELECT * FROM '.$db->quoteName('#__community_mailq').' WHERE '.$db->quoteName('status').'='.$db->Quote('0').' LIMIT 0,' . $limit;

		$db->setQuery( $sql );
		$result = $db->loadObjectList();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr() );
		}

		if( $markAsSent )
		{
			// lets immediately mark all as sent for now to minimise
			// multiple email being sent at the same time
			$ids = array();
			foreach ($result as $row){
				$ids[] = $row->id;
			}

			if( !empty($ids)) {
				$ids = implode(',', $ids);
				$sql  = 'UPDATE '.$db->quoteName('#__community_mailq').' SET '.$db->quoteName('status').'='.$db->Quote('1').' WHERE '.$db->quoteName('id').' IN ('. $ids.'); ';
				$db->setQuery( $sql );
				$db->query();
			}
		}

		return $result;
	}

	/**
	* Set the email status (0 = pending, 1 = sent/succesful, 2 = blocked)
	*/
	public function markEmailStatus($id, $statuscode = 1){
		$db	 = $this->getDBO();

		$sql = 'SELECT * FROM '.$db->quoteName('#__community_mailq').' WHERE '.$db->quoteName('id').'=' . $db->Quote($id);
		$db->setQuery( $sql );
		$obj = $db->loadObject();

		$obj->status = $statuscode;
		$db->updateObject( '#__community_mailq', $obj, 'id' );
	}

	/**
	 * Change the status of a message
	 */
	public function markSent($id)
	{
		return $this->markEmailStatus($id, 1);
	}

	public function purge(){
	}

	public function remove(){
	}

	public function getMailCount()
	{
		$db = $this->getDBO();

		$sql = 'SELECT COUNT(*) FROM '. $db->quoteName('#__community_mailq');
		$db->setQuery($sql);
		$result = $db->loadResult();
		return $result;
	}
}
