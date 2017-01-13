<?php
/**
* php_broadlink 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 20:01:16 [Jan 11, 2017])
*/
//
//
class php_broadlink_test extends module {
/**
* php_broadlink_test
*
* Module class constructor
*
* @access private
*/
function php_broadlink_test() {
  $this->name="php_broadlink_test";
  $this->title="php_broadlink";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='dev_httpbrige_devices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_dev_httpbrige_devices') {
   $this->search_dev_httpbrige_devices($out);
  }
  if ($this->view_mode=='edit_dev_httpbrige_devices') {
   $this->edit_dev_httpbrige_devices($out, $this->id);
  }
  if ($this->view_mode=='delete_dev_httpbrige_devices') {
   $this->delete_dev_httpbrige_devices($this->id);
   $this->redirect("?data_source=dev_httpbrige_devices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='dev_broadlink_commands') {
  if ($this->view_mode=='' || $this->view_mode=='search_dev_broadlink_commands') {
   $this->search_dev_broadlink_commands($out);
  }
  if ($this->view_mode=='edit_dev_broadlink_commands') {
   $this->edit_dev_broadlink_commands($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* dev_httpbrige_devices search
*
* @access public
*/
 function search_dev_httpbrige_devices(&$out) {
  require(DIR_MODULES.$this->name.'/dev_httpbrige_devices_search.inc.php');
 }
/**
* dev_httpbrige_devices edit/add
*
* @access public
*/
 function edit_dev_httpbrige_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/dev_httpbrige_devices_edit.inc.php');
 }
/**
* dev_httpbrige_devices delete record
*
* @access public
*/
 function delete_dev_httpbrige_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM dev_httpbrige_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM dev_httpbrige_devices WHERE ID='".$rec['ID']."'");
 }
/**
* dev_broadlink_commands search
*
* @access public
*/
 function search_dev_broadlink_commands(&$out) {
  require(DIR_MODULES.$this->name.'/dev_broadlink_commands_search.inc.php');
 }
/**
* dev_broadlink_commands edit/add
*
* @access public
*/
 function edit_dev_broadlink_commands(&$out, $id) {
  require(DIR_MODULES.$this->name.'/dev_broadlink_commands_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
   $table='dev_broadlink_commands';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     if ($value==1) {
		 	require(DIR_MODULES.$this->name.'/broadlink.class.php');
			$id=$properties[$i]['DEVICE_ID'];
			//console.log($id);
			console.log('ntcn');
			//DebMes($id);
			$data=$properties[$i]['VALUE'];
			$rec=SQLSelectOne("SELECT * FROM dev_httpbrige_devices WHERE ID='$id'");
			$rm = Broadlink::CreateDevice($rec['TITLE'], $rec['MAC'], 80, $rec['TYPE']);
			$rm->Auth();
			//$rm->Enter_learning();
			$rm->Send_data($data);
			sg($object.".".$property, 0);
	 }
    }
   }
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS dev_httpbrige_devices');
  SQLExec('DROP TABLE IF EXISTS dev_broadlink_commands');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall() {
/*
dev_httpbrige_devices - 
dev_broadlink_commands - 
*/
  $data = <<<EOD
 dev_httpbrige_devices: ID int(10) unsigned NOT NULL auto_increment
 dev_httpbrige_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_httpbrige_devices: TYPE varchar(255) NOT NULL DEFAULT ''
 dev_httpbrige_devices: TITLE varchar(255) NOT NULL DEFAULT ''
 dev_httpbrige_devices: MAC varchar(255) NOT NULL DEFAULT ''
 dev_httpbrige_devices: UPDATED datetime
 dev_broadlink_commands: ID int(10) unsigned NOT NULL auto_increment
 dev_broadlink_commands: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_broadlink_commands: VALUE varchar(255) NOT NULL DEFAULT ''
 dev_broadlink_commands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 dev_broadlink_commands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 dev_broadlink_commands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDExLCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
