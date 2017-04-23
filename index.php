<?php
header('Content-Type: text/html; charset=utf-8', true);



class BlockService {

function getDbh()
    {
    $dbh = new PDO('mysql:host=localhost;dbname=gpr', 'login', 'password');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
    }
    
function getStatus($blockItem) {
	//throw new SoapFault("Server changeStatus",print_r($blockItem,true));
	$guid=$blockItem->checkguid;
	return new SoapVar($this->_getStatus($guid),SOAP_ENC_OBJECT, null,null,"ClientStatus", "http://example.com/example/soap/index.php")   ;
}
  
function changeStatus($blockItem) {
    $itemList=array();
    $dbh=$this->getDbh();
	{
	$guid=$blockItem->guid;
	$user=$blockItem->user;
	$block=0;
	if($blockItem->isBlock)
	    {
	    $block=1;
	    }
	$query = sprintf("INSERT INTO deny_deny (guid, user,block) VALUES ('%s', '%s', %s) ON DUPLICATE KEY UPDATE user='%s',block='%s',mkdate=now() ",
    		$guid,
		$user,
		$block,
		$user,
		$block
		);
	$sth=$dbh->exec($query);
	return new SoapVar($this->_getStatus($guid),SOAP_ENC_OBJECT, null,null,"ClientStatus", "http://example.com/example/soap/index.php")   ;
	}
}  
  

function changeStatusList($in) {
    //throw new SoapFault("Server changeStatusList",print_r($in,true));
    $ret=(object) array('out'=>array());
    $itemList=array();
    $dbh=$this->getDbh();
    array_push($itemList,$in->in);
    if(is_array($in->in))
            {$itemList=$in->in;}
      foreach($itemList as $blockItem)
	{
	$guid=$blockItem->guid;
	$user=$blockItem->user;
	$block=$blockItem->isBlock;
	if("".$blockItem->isBlock == "true")
	    {
	    $block=1;
	    }
	
	$query = sprintf("INSERT INTO deny_deny (guid, user,block) VALUES ('%s', '%s', %s) ON DUPLICATE KEY UPDATE user='%s',block='%s',mkdate=now() ",
    		$guid,
		$user,
		$block,
		$user,
		$block
		);
	$sth=$dbh->exec($query);
	array_push($ret->out,new SoapVar($this->_getStatus($guid),SOAP_ENC_OBJECT, null,null,"ClientStatus", "http://example.com/example/soap/index.php")   );
	}
    return (object) $ret;
}  


function getStatusList($in) {
    $ret=(object) array('out'=>array());
    $itemList=array();
    $dbh=$this->getDbh();
    array_push($itemList,$in->in);
    if(is_array($in->in))
            {$itemList=$in->in;}
      foreach($itemList as $blockItem)
	{
	$guid=$blockItem;
	array_push($ret->out,new SoapVar($this->_getStatus($guid),SOAP_ENC_OBJECT, null,null,"ClientStatus", "http://example.com/example/soap/index.php")   );
	}
    return (object) $ret;
}  


function _getStatus($guid){
	$dbh=$this->getDbh();
	$query = sprintf("select guid,user,block,mkdate from deny_deny where guid='%s'",$guid);
	$sth=$dbh->query($query);
	$ret=array();

foreach($sth as $row)
	    {
	    $ret[]=new SoapVar($row['guid'],XSD_STRING, null,null,"guid", "http://example.com/example/soap/index.php");
	    $ret[]=new SoapVar($row['user'],XSD_STRING, null,null,"user", "http://example.com/example/soap/index.php");
	    $ret[]=new SoapVar($row['block'],XSD_BOOLEAN, null,null,"isBlock", "http://example.com/example/soap/index.php");
	    return $ret;
	    }
	$ret[]=new SoapVar($guid,XSD_STRING, null,null,"guid", "http://example.com/example/soap/index.php");
	$ret[]=new SoapVar('У нас тут НЛО пролетало',XSD_STRING, null,null,"user", "http://example.com/example/soap/index.php");
	$ret[]=new SoapVar(false,XSD_BOOLEAN, null,null,"isBlock", "http://example.com/example/soap/index.php");


	return $ret;

}


} 



ini_set("soap.wsdl_cache_enabled", "0"); // отключаем кэширование WSDL 
$server = new SoapServer("BlockSoap.wsdl",array('soap_version' => SOAP_1_2));
$server->setClass("BlockService");

$data = file_get_contents('php://input');
$server->handle($data); 
?>
