<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

class CFiles
{
	public function getFileHTML($type = NULL, $id = NULL)
	{
		//CFactory::load( 'models' , 'files' );

		$model  = CFactory::getModel( 'files' );

		$data = $model->getFileList($type,$id);

		$my			= CFactory::getUser();


		if(!empty($data))
		{
			foreach($data as $key => $_data)
			{
				$data[$key] = $this->convertToMb($_data);
				$data[$key]->deleteable = $this->checkDeleteable($type,$_data,$my);
				$data[$key]->user = CFactory::getUser( $_data->creator );
			}
		}

		$permission = $my->authorise('community.add', 'files.' . $type,$id);

		$tmpl	= new CTemplate();

		$tmpl   ->set('type'    ,   $type)
				->set('id'      ,   $id)
				->set('data'    ,   $data)
				->set('permission', $permission);

		return $tmpl->fetch( 'files.list' );
	}

	public function downloadFile($file = null , $name = null)
	{
		if(!is_readable($file))
		{
			die('File not found or inaccessible!');
		}

		$size = filesize($file);

		$file_extension = strtolower(substr(strrchr($file,"."),1));

		$mime_type="application/force-download";

		$name = rawurldecode($name. '.' .$file_extension);

		@ob_end_clean();

		if(ini_get('zlib.output_compression'))
		{
			ini_set('zlib.output_compression', 'Off');
		}

		header('Content-Type: ' . $mime_type);
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header("Content-Transfer-Encoding: binary");
		header('Accept-Ranges: bytes');

		/* The three lines below basically make the
		download non-cacheable */
		header("Cache-control: private");
		header('Pragma: private');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Content-Length: ".$size);

		$chunksize = 1*(1024*1024); //you may want to change this

		$bytes_send = 0;
		if ($file = fopen($file, 'r'))
		{
			if(isset($_SERVER['HTTP_RANGE']))
			fseek($file, 0);

			while(!feof($file) && (!connection_aborted()) && ($bytes_send<$size))
			{
				$buffer = fread($file, $chunksize);
				print($buffer); //echo($buffer); // is also possible
				flush();
				$bytes_send += strlen($buffer);
			}
			fclose($file);
		}
		else die('Error - can not open file.');
	}

	public function getParentId($type,$obj)
	{
		switch($type)
		{
			case 'discussion':
			{
				return $obj->discussionid;
				break;
			}
			case 'bulletin':
			{
				return $obj->bulletinid;
				break;
			}
			case 'group':
			{
				return $obj->groupid;
				break;
			}
		}
	}

	public function convertToMb($obj)
	{
		$obj->filesize = round($obj->filesize/1048576,2) . 'MB';

		return $obj;
	}

	public function checkDeleteable($type,$obj,$my)
	{
		return $my->authorise('community.delete', 'files.' . $type,$obj);
	}

	public function checkType($fileName)
	{
		$extension = pathinfo($fileName);

		$params = JComponentHelper::getParams( 'com_media' );
		$fileType = $params->get( 'upload_extensions' );
		$fileType = explode(',',$fileType);

		if(in_array($extension['extension'],$fileType))
		{
			return true;
		}

		return false;
	}

	public function getParentType($obj)
	{
		if($obj->discussionid)
		{
			$obj->parentType = 'discussion';
		}
		elseif($obj->bulletinid)
		{
			$obj->parentType = 'bulletin';
		}
		elseif($obj->eventid)
		{
			$obj->parentType = 'event';
		}
		elseif($obj->profileid)
		{
			$obj->parentType = 'profile';
		}
		elseif($obj->groupid)
		{
			$obj->parentType = 'group';
		}

		return $obj;
	}

	public function getParentName($obj)
	{

		$cTable          = JTable::getInstance(ucfirst($obj->parentType), 'CTable');

		switch($obj->parentType)
		{
			case 'discussion':
			{
				$cTable->load($obj->discussionid);
				$obj->parentName = $cTable->title;
				break;
			}
			case 'bulletin':
			{
				$cTable->load($obj->bulletinid);
				$obj->parentName = $cTable->title;
				break;
			}
			case 'group':
			{
				$cTable->load($obj->groupid);
				$obj->parentName = $cTable->name;
				break;
			}

		}
		return $obj;
	}

	public function hasFile($id,$type)
	{
		$model  = CFactory::getModel( 'files' );

		$data = $model->getFileList($type,$id);

		if(count($data)>0)
			return true;

		return false;
	}
	public function S3DownloadFile($obj)
	{
		$storage		= CStorage::getStorage( $obj->storage );

		$file_extension = strtolower(substr(strrchr($obj->filepath,"."),1));
		$name = rawurldecode($obj->name. '.' .$file_extension);


		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename='.$name.';');


		readfile($storage->getURI($obj->filepath));
	}
}
class CFilesLibrary extends CFiles
{}
?>