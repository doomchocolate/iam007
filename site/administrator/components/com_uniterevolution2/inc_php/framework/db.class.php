<?php
/**
 * @package Unite Revolution Slider for Joomla 1.7-2.5
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// No direct access.
defined('_JEXEC') or die;
	
	class UniteDBRev{
		
		private $jdb;
		private $lastRowID;
		
		/**
		 * 
		 * constructor - set database object
		 */
		public function __construct(){
			$this->jdb = JFactory::getDBO();
		}
		
		/**
		 * 
		 * throw error
		 */
		private function throwError($message,$code=-1){
			UniteFunctionsRev::throwError($message,$code);
		}
		
		//------------------------------------------------------------
		// validate for errors
		private function checkForErrors($prefix = ""){
			$errorNum = $this->jdb->getErrorNum();
			if($errorNum){
				$message = $this->jdb->getErrorMsg();
				if(!empty($prefix))
					$message = $prefix." ".$message;
					
				$this->throwError($message, $errorNum);
			}
		}
		
		
		/**
		 * 
		 * insert variables to some table
		 */
		public function insert($tableName,$arrItems){

			$strFields = "";
			$strValues = "";
			foreach($arrItems as $field=>$value){
				$value = "'".$this->escape($value)."'";
				if($field == "id") continue;
				if($strFields != "") $strFields .= ",";
				if($strValues != "") $strValues .= ",";
				$strFields .= $field;
				$strValues .= $value;
			}
			
			$insertQuery = "insert into $tableName($strFields) values($strValues)";									
			
			$this->runSql($insertQuery,"insert");
			$this->lastRowID = $this->jdb->insertid();
			
			return($this->lastRowID);
		}
		
		
		/**
		 * 
		 * get last insert id
		 */
		public function getLastInsertID(){
			$this->lastRowID = $this->jdb->insertid();
			return($this->lastRowID);			
		}
		
		
		/**
		 * 
		 * delete rows
		 */
		public function delete($table,$where){
			
			UniteFunctionsRev::validateNotEmpty($table,"table name");
			UniteFunctionsRev::validateNotEmpty($where,"where");
			
			if(is_array($where))
				$where = $this->getWhereString($where);
			
			$query = "delete from $table where $where";
			
			$success = $this->runSql($query, "delete error");
			return($success);
		}
		
		
		/**
		 * 
		 * get where string from where array
		 */
		private function getWhereString($where){
			$where_format = null;
			
			foreach ( $where as $key=>$value ) {
				if(is_numeric($value) == false)
					$value = "'$value'";
					
				$wheres[] = "$key = {$value}";
			}
			
			$strWhere = implode( ' AND ', $wheres );
			return($strWhere);
		}
		
		
		/**
		 * 
		 * insert variables to some table
		 */
		public function update($tableName,$arrData,$where){
			
			UniteFunctionsRev::validateNotEmpty($tableName,"table cannot be empty");
			UniteFunctionsRev::validateNotEmpty($where,"where cannot be empty");
			
			if(is_array($where))
				$where = $this->getWhereString($where);
			
			$strFields = "";
			foreach($arrData as $field=>$value){
				$value = "'".$this->escape($value)."'";
				if($strFields != "") $strFields .= ",";
				$strFields .= "$field=$value";
			}
			
			$updateQuery = "update $tableName set $strFields where $where";
			
			$numRows = $this->runSql($updateQuery, "update error");
			
			return($numRows);
		}
		
		
		/**
		 * 
		 * execute query, get number of rows affected 
		 */
		public function runSql($query,$errorMessage="Regular Guery"){
			
			$this->jdb->setQuery($query);
			$success = $this->jdb->query();
			$this->checkForErrors($errorMessage);
			if($success == false)
				$this->throwError("query execution failed");
			
			$numRows = $this->jdb->getAffectedRows();
			return($numRows);
		}
		
		
		/**
		 * 
		 * fetch rows from sql query
		 */
		public function fetchSql($query){
			
			$this->jdb->setQuery($query);
			//$response = $this->jdb->execute();			
			$this->checkForErrors("fetch");
			$rows = $this->jdb->loadObjectList();
			$rows = UniteFunctionsRev::convertStdClassToArray($rows);
			
			return($rows);
		}
		
		/**
		 * 
		 * get row wp emulator
		 */
		public function get_row($query = null){
			$this->jdb->setQuery($query);
			$this->checkForErrors("get_row");
			$rows = $this->jdb->loadObjectList();
			if(count($rows) == 1)
				$result = $rows[0];
			else
				$result = $rows;
			
			return($result);
		}
		
		/**
		 * 
		 * get data array from the database
		 * 
		 */
		public function fetch($tableName,$where="",$orderField="",$groupByField="",$sqlAddon=""){
			
			$query = "select * from $tableName";
			if($where) $query .= " where $where";
			if($orderField) $query .= " order by $orderField";
			if($groupByField) $query .= " group by $groupByField";
			if($sqlAddon) $query .= " ".$sqlAddon;
			
			$rows = $this->fetchSql($query);
			
			return($rows);
		}
		
		/**
		 * 
		 * fetch only one item. if not found - throw error
		 */
		public function fetchSingle($tableName,$where="",$orderField="",$groupByField="",$sqlAddon=""){
			$response = $this->fetch($tableName, $where, $orderField, $groupByField, $sqlAddon);
			if(empty($response))
				$this->throwError("Record not found");
			$record = $response[0];
			return($record);
		}
		
		/**
		 * 
		 * escape data to avoid sql errors and injections.
		 */
		public function escape($string){
			$newString = $this->jdb->escape($string);
			return($newString);
		}
		
		
		
	}
	
?>