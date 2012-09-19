<?php
 /*
 * @Title Wajox PhpMyConsole
 * @Copyright (c) 2009  
 * @Author: Ildar Usmanov aka wajox 
 * @Url https://github.com/ildarusmanov/phpmyconsole.git
 */

@session_start();
$end_code="<script type=\"text/javascript\">"
	."window.parent.document.getElementById('console_io').innerHTML+=document.getElementById('output').innerHTML;"
	."</script>";

function PhpMyConsole_Version()
	{
		return '1.0a';
	}
function PhpMyConsole_login_form($msg='')
	{
		if(isset($_POST['cmd'])) return FALSE;
		echo "<font color=\"red\">".$msg."</font><br/>"
		."<form action=\"phpmyconsole.php\" method=\"post\"/><br/>"
		."Host:<br/>"
    	."<input name=\"host\" type=\"text\" value=\"localhost\"/><br/>"
    	."Username:<br/>"
    	."<input name=\"user\" type=\"text\" value=\"root\"/><br/>"
    	."Password:<br/>"
    	."<input name=\"password\" type=\"text\" value=\"\"/><br/>"
    	."<input type=\"submit\" value=\"Go\">"
		."</form>\n";
	die('');
	}
function PhpMyConsole_login()
	{
		if(isset($_POST['host']) && isset($_POST['user']) && isset($_POST['password']))
		{
			if(!$r=mysql_connect($_POST['host'],$_POST['user'],$_POST['password']))
				PhpMyConsole_login_form();
			$_SESSION['host']=$_POST['host'];
			$_SESSION['user']=$_POST['user'];
			$_SESSION['password']=$_POST['password'];
			$_SESSION['rid']=$r;

		}elseif(isset($_SESSION['host']) && isset($_SESSION['user']) && isset($_SESSION['password']))
		{
			if(!$r=mysql_connect($_SESSION['host'],$_SESSION['user'],$_SESSION['password']))
				PhpMyConsole_login_form();
			$_SESSION['rid']=$r;
		}else
		{
			PhpMyConsole_login_form();
		}
	}
function PhpMyConsole_logout(){
		if(isset($_GET['logout'])){
				$_SESSION=null;
			}
	}
function PhpMyConsole_getCmdType($cmd){
		if(strtolower(substr($cmd,0,6))=='select')
			return 1;
		if(strtolower(substr($cmd,0,4))=='show')
			return 1;
		if(strtolower(substr($cmd,0,3))=='use')
			return 2;
		return 0;
	}
function PhpMyConsole_cmd()
	{
		global $end_code;
		$start=microtime();
		$output='';
		$result='';
		if(!isset($_POST['cmd']))
			return FALSE;
		$sql_cmd=$_POST['cmd'];
		if(get_magic_quotes_gpc())
  			$sql_cmd=stripslashes($sql_cmd);
		$sql_cmd=explode(";\r\n",$_POST['cmd']);
		foreach($sql_cmd as $key=>$sql)
		{
			if(get_magic_quotes_gpc())
  				$sql=stripslashes($sql);
		    if(empty($sql)) next;
    		if(!$r=mysql_query($sql,$_SESSION['rid']))
    		{
    			$output='mysql[<b>'.$_SESSION['host'].'</b>]&gt;<b><font color="red">'.mysql_error().'</font></b>';
    		}else
    		{
    			switch(PhpMyConsole_getCmdType($sql))
    			{
    				case 0:
    					if(mysql_affected_rows($_SESSION['rid'])==-1)
    						$output='mysql[<b>'.$_SESSION['host'].'</b>]&gt;ERROR';
    					else
    						$output='mysql[<b>'.$_SESSION['host'].'</b>]&gt;OK<br/>mysql[<b>'.$_SESSION['host'].'</b>]&gt;Affected rows:'.mysql_affected_rows($_SESSION['rid']);
    					$output.=mysql_info($_SESSION['rid']);
					break;
					case 1:
    				if(mysql_num_rows($r)>0)
    				{
    					$row=mysql_fetch_assoc($r);
    					$output='mysql[<b>'.$_SESSION['host'].'</b>]&gt;<table border="1"><tr style="background:white;color:black;">';
    					$line='<tr>';
    					foreach($row as $name=>$value)
    					{
    						$output.='<td>'.$name.'</td>';
    						$line.='<td>'.$value.'</td>';
    					}
    					$line.='</tr>';
    					$output.='</tr>';
    					$output.=$line;
    					while($row=mysql_fetch_assoc($r))
    					{
    						$output.='<tr>';
    						foreach($row as $name=>$value)
    							$output.='<td>'.$value.'</td>';
    						$output.='</tr>';
    					}
    					$output.='</table><br/>mysql[<b>'.$_SESSION['host'].'</b>]&gt;Rows count:'.mysql_num_rows($r);
    				}
    				break;
    				case 2:
    					$output='mysql[<b>'.$_SESSION['host'].'</b>]&gt;OK';
    				break;
    			}
    		}
    		$result.='user[<b>'.$_SESSION['user'].'</b>]&gt;<font color="yellow">'.$sql.'</font><br/>'.$output.'<br/>mysql[<b>'.$_SESSION['host'].'</b>]&gt;Time:'.(microtime()-$start).'<br>';
    	}
    	die('<div id="output">'.$result.'</div>'.$end_code);
	}
PhpMyConsole_logout();
PhpMyConsole_login();
PhpMyConsole_cmd();
?>
<html>
<head>
<title>Wajox PhpMyConsole <?php echo PhpMyConsole_Version();?> by Wajox</title>
<style type="text/css">
body{
	background:#FFFFFF;
	color:#000000;
	}
#console{
	color:#000000;
	background:#EEEEEE;
	border-color:#000000;
	border-style:solid;
	border-width:1px;
	padding:10px;
	margin:0 auto;
	width:800px;
	}
#console_io{
	color:#FFFFFF;
	background:#000000;
	font-family:Terminal;
	font-size:14px;
	border-width:0px;
	width:800px;
	height:250px;
	overflow:auto;
	}
#console_io table{color:#FFFFFF;}
#cmd_line{
	width:800px;
	height:100px;
	overflow:auto;
	color:yellow;
	background:#000000;
	font-family:Terminal;
	font-size:14px;
	width:100%;
	padding:10px -10px 10px 10px;
	}
</style>
</head>
<body>
<div id="console">
Font:
<select size="1" id="console_font" onChange="document.getElementById('cmd_line').style.fontFamily=document.getElementById('console_io').style.fontFamily=document.getElementById('console_font').value;">
  <option value="Terminal">Terminal</option>
  <option value="Scan Serif">Scan Serif</option>
  <option value="Arial">Arial</option>
</select>
Size:
<select size="1" id="console_fontsize" onChange="document.getElementById('cmd_line').style.fontSize=document.getElementById('console_io').style.fontSize=document.getElementById('console_fontsize').value+'px';">
  <option value="10">10</option>
  <option value="12">12</option>
  <option value="14" selected="true">14</option>
  <option value="16">16</option>
  <option value="18">18</option>
  <option value="20">20</option>
  <option value="22">22</option>
  <option value="24">24</option>
</select>
<br/>
<div id="console_io">
<b>Wajox PhpMyConsole <?=PhpMyConsole_Version();?> (c)2009 Wajox (http://wajox.myglonet.com)</b><br/>
MySQL INFO:<br/>
-&gt;Host: <?=mysql_get_host_info($_SESSION['rid']);?><br/>
-&gt;Server: <?=mysql_get_server_info($_SESSION['rid']);?><br/>
-&gt;Client: <?=mysql_get_client_info();?><br/>
-&gt;Protocol: <?=mysql_get_proto_info($_SESSION['rid']);?><br/>
</div><br/>
<form action="phpmyconsole.php" method="post" target="frame">
<textarea name="cmd" id="cmd_line" OnFocus="if(this.value=='Insert SQL query...')this.value='';">Insert SQL query...</textarea>
<br/><input type="submit" value="Go!"/>
<input type="button" value="Clear screen" onClick="document.getElementById('console_io').innerHTML='';"/>
<input type="button" value="Logout" onClick="location.href='PhpMyConsole.php?logout';"/>
</form>
<iframe style="display:none;" name="frame"></frame>
</div>
</body>
</html>
