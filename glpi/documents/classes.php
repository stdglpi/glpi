<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2005 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/
 
// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

include ("_relpos.php");


class Document {

	var $fields	= array();
	var $updates	= array();
	
	function getfromDB ($ID) {

		$db = new DB;
		$query = "SELECT * FROM glpi_docs WHERE (ID = '$ID')";
		
		if ($result = $db->query($query)) {
		if ($db->numrows($result)==1){
			$data = $db->fetch_array($result);
		
			foreach ($data as $key => $val) {
				$this->fields[$key] = $val;
			}
			return true;
		} else return false;
		} else {
			return false;
		}
	}
	
	function getEmpty () {
	$db = new DB;
	$fields = $db->list_fields("glpi_docs");
	$columns = $db->num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
			$name = $db->field_name($fields, $i);
			$this->fields[$name] = "";
		}
	return true;
	}

	function restoreInDB($ID) {
		$db = new DB;
		$query = "UPDATE glpi_docs SET deleted='N' WHERE (ID = '$ID')";
		if ($result = $db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	function updateInDB($updates)  {

		$db = new DB;

		for ($i=0; $i < count($updates); $i++) {
			$query  = "UPDATE glpi_docs SET ";
			$query .= $updates[$i];
			$query .= "='";
			$query .= $this->fields[$updates[$i]];
			$query .= "' WHERE ID='";
			$query .= $this->fields["ID"];	
			$query .= "'";
			$result=$db->query($query);
		}
		
	}
	
	function addToDB() {
		
		$db = new DB;

		// Build query
		$query = "INSERT INTO glpi_docs (";
		$i=0;
		foreach ($this->fields as $key => $val) {
			$fields[$i] = $key;
			$values[$i] = $val;
			$i++;
		}		
		for ($i=0; $i < count($fields); $i++) {
			$query .= $fields[$i];
			if ($i!=count($fields)-1) {
				$query .= ",";
			}
		}
		$query .= ") VALUES (";
		for ($i=0; $i < count($values); $i++) {
			$query .= "'".$values[$i]."'";
			if ($i!=count($values)-1) {
				$query .= ",";
			}
		}
		$query .= ")";

		$result=$db->query($query);
		return $db->insert_id();

	}

	function isUsed($ID){
	return true;
	$db = new DB;		
	$query="SELECT * from glpi_doc_device where FK_doc = '$ID'";
	$result = $db->query($query);
	return ($db->numrows($result)>0);
	}
	
	function deleteFromDB($ID,$force=0) {
	global $cfg_install,$phproot,$lang;
	
		$db = new DB;
		$this->getFromDB($ID);	
		if ($force==1||!$this->isUsed($ID)){
			$query = "DELETE from glpi_docs WHERE ID = '$ID'";
			if ($result = $db->query($query)) {
				
				$query3 = "DELETE FROM glpi_doc_device WHERE (FK_doc = '$ID')";
				$result3 = $db->query($query3);
				
				// UNLINK DU FICHIER
				if (!empty($this->fields["filename"]))
				if(is_file($phproot.$cfg_install["doc_dir"]."/".$this->fields["filename"])&& !is_dir($phproot.$cfg_install["doc_dir"]."/".$this->fields["filename"])) {
						if (unlink($phproot.$cfg_install["doc_dir"]."/".$this->fields["filename"]))
						$_SESSION["MESSAGE_AFTER_REDIRECT"]= $lang["document"][24].$phproot.$cfg_install["doc_dir"]."/".$this->fields["filename"]."<br>";
						else $_SESSION["MESSAGE_AFTER_REDIRECT"]= $lang["document"][25].$phproot.$cfg_install["doc_dir"]."/".$this->fields["filename"]."<br>";
						}

				
					return true;
			} else {
				return false;
			}
		} else {
		$query = "UPDATE glpi_docs SET deleted='Y' WHERE ID = '$ID'";		
		return ($result = $db->query($query));
		}
	}
	
}

?>
