<?php
/*
 |--------------------------------------------------------------------------
 | SkyCaiji (蓝天采集器)
 |--------------------------------------------------------------------------
 | Copyright (c) 2018 http://www.skycaiji.com All rights reserved.
 |--------------------------------------------------------------------------
 | 使用协议  http://www.skycaiji.com/licenses
 |--------------------------------------------------------------------------
 */

 define('IN_SKYCAIJI', 1);define('SKYCAIJI_VERSION', '1.0');if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } function bootstrap_pages($pageHtml){ if($pageHtml){ $pageHtml=str_replace('<div>','<nav><ul class="pagination">',$pageHtml); $pageHtml = str_replace('</div>','</ul></nav>',$pageHtml); $pageHtml = str_replace('<span class="current">','<li class="active"><a href="javascript:;">',$pageHtml); $pageHtml = str_replace('</span>','</a></li>',$pageHtml); $pageHtml=preg_replace('/<a\b([^\'\"]*?)class=[\'\"][^\'\"]*?[\'\"]\s*/i', "<li><a$1", $pageHtml); $pageHtml = str_replace('</a>','</a></li>',$pageHtml); } return $pageHtml; } function url_b64encode($string) { $data = base64_encode($string); $data = str_replace(array('+','/','='),array('-','_',''),$data); return $data; } function url_b64decode($string) { $data = str_replace(array('-','_'),array('+','/'),$string); $mod4 = strlen($data) % 4; if ($mod4) { $data .= substr('====', $mod4); } return base64_decode($data); } function breadcrumb($arr){ $return=''; foreach ($arr as $v){ if(is_string($v)){ $return.='<li>'.$v.'</li>'; }elseif(!empty($v['url'])){ $return.='<li><a href="'.$v['url'].'">'.$v['title'].'</a></li>'; } } return $return; } function array_array_map($callback, $arr1, array $_ = null){ if(is_array($arr1)){ foreach ($arr1 as $k=>$v){ if(!is_array($v)){ $arr[$k]=call_user_func($callback, $v); }else{ $arr[$k]=array_array_map($callback,$v,$_); } } } return $arr; } function array_implode($glue, $pieces){ $str=''; foreach ($pieces as $v){ if(is_array($v)){ $str.=array_implode($glue,$v); }else{ $str.=$glue.$v; } } return $str; } function auto_convert2utf8($str){ $encode = mb_detect_encoding($str, array('ASCII','UTF-8','GB2312','GBK','BIG5')); if(strcasecmp($encode, 'utf-8')!==0){ $str=iconv($encode,'utf-8//IGNORE',$str); } return $str; } function vendor_autoload(){ static $loaded=false; if(!$loaded){ require_once dirname(THINK_PATH).'/vendor/autoload.php'; $loaded=true; } } function send_mail($emailConfig,$to, $name, $subject = '', $body = '', $attachment = null){ set_time_limit(0); vendor('phpmailer/phpmailer/PHPMailerAutoload',C('ROOTPATH').'/vendor'); $mail = new PHPMailer(); $mail->isSMTP(); $mail->Host = $emailConfig['smtp']; $mail->SMTPAuth = true; $mail->Username = $emailConfig['email']; $mail->Password = $emailConfig['pwd']; $mail->SMTPSecure = empty($emailConfig['type'])?'tls':$emailConfig['type']; $mail->Port = $emailConfig['port']; $mail->setFrom($emailConfig['email'], $emailConfig['sender']); $mail->addAddress($to, $name); $mail->isHTML(true); $mail->Subject = $subject; $mail->Body = $body; $mail->AltBody = ''; if(is_array($attachment)){ foreach ($attachment as $file){ is_file($file) && $mail->AddAttachment($file); } } return $mail->Send() ? true : $mail->ErrorInfo; } function pwd_encrypt($pwd){ return md5(sha1($pwd)); } function clientinfo(){ $info=array( 'url'=>(is_ssl()?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/'.trim(__ROOT__,'\/\\'), 'v'=>constant('SKYCAIJI_VERSION'), ); return $info; } function get_html($url,$headers=null,$options=array(),$fromEncode='auto'){ $options=is_array($options)?$options:array(); if(!isset($options['useragent'])){ $options['useragent']=null; } if(!preg_match('/^\w+\:\/\//', $url)){ $url='http://'.$url; } try { vendor_autoload(); $request=\Requests::get($url,$headers,$options); } catch (\Exception $e) { $request=null; } if(!empty($request)){ if(200==$request->status_code){ $html=$request->body; if ($fromEncode == 'auto') { preg_match ( '/<meta[^<>]*?content=[\'\"]text\/html\;\s*charset=(?P<charset>[^\'\"\<\>]+?)[\'\"]/i', $html, $charset ) || preg_match ( '/<meta[^<>]*?charset=[\'\"](?P<charset>[^\'\"\<\>]+?)[\'\"]/i', $html, $charset ) || preg_match('/charset=(?P<charset>[\w\-]+)/i', $request->headers['content-type'],$charset); $fromEncode = $charset ['charset'] ? $charset ['charset'] : null; if(empty($fromEncode)){ $fromEncode = mb_detect_encoding($html, array('ASCII','UTF-8','GB2312','GBK','BIG5')); } $fromEncode=empty($fromEncode)?null:$fromEncode; } if (! empty ( $fromEncode ) && strcasecmp ( $fromEncode, 'utf-8' ) !== 0) { $html = iconv ( $fromEncode, 'utf-8//IGNORE', $html ); } } } return $html?$html:null; } function load_data_config(){ static $loaded=false; if(!$loaded){ if(file_exists(C('ROOTPATH').'/data/config.php')){ $dataDbConfig=include C('ROOTPATH').'/data/config.php'; if(!empty($dataDbConfig)&&is_array($dataDbConfig)){ C($dataDbConfig); $loaded=true; } } } } function clear_dir($path,$passFiles=null){ if(empty($path)){ return; } $path=realpath($path); if(empty($path)){ return; } $passFiles=array_map('realpath', $passFiles); $fileList=scandir($path); foreach( $fileList as $file ){ $fileName=realpath($path.'/'.$file); if(is_dir( $fileName ) && '.' != $file && '..' != $file ){ clear_dir($fileName,$passFiles); rmdir($fileName); }elseif(is_file($fileName)){ if($passFiles&&in_array($fileName, $passFiles)){ }else{ unlink($fileName); } } } clearstatcache(); } 