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

namespace Install\Event; use Think\Controller; use Admin\Model\ConfigModel; if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } class UpgradeDbEvent extends Controller{ public function success($message='',$jumpUrl='',$ajax=false){ parent::success($message,$jumpUrl,$ajax); exit(); } public function run(){ load_data_config(); $result=$this->run_upgrade(); if($result['success']){ $mconfig=new ConfigModel(); $mconfig->setVersion(constant('SKYCAIJI_VERSION')); } return $result; } public function run_upgrade(){ $mconfig=new ConfigModel(); $dbVersion=$mconfig->where("`cname`='version'")->find(); if(!empty($dbVersion)){ $dbVersion=$mconfig->convertData($dbVersion); $dbVersion=$dbVersion['data']; } $fileVersion=constant('SKYCAIJI_VERSION'); if(empty($dbVersion)){ return array('success'=>false,'msg'=>'未获取到数据库中的版本号'); } if(empty($fileVersion)){ return array('success'=>false,'msg'=>'未获取到项目文件的版本号'); } if(version_compare($dbVersion,$fileVersion)>=0){ return array('success'=>true,'msg'=>'数据库已是最新版本，无需更新'); } $methods=get_class_methods($this); $upgradeDbMethods=array(); foreach ($methods as $method){ if(preg_match('/^upgrade_db_to(?P<ver>(\_\d+)+)$/',$method,$toVer)){ $toVer=str_replace('_', '.', trim($toVer['ver'],'_')); if(version_compare($toVer,$dbVersion)>=1){ if(version_compare($toVer,$fileVersion)<=0){ $upgradeDbMethods[$toVer]=$method; } } } } if(empty($upgradeDbMethods)){ return array('success'=>true,'msg'=>'暂无更新'); } ksort($upgradeDbMethods); foreach ($upgradeDbMethods as $newVer=>$upMethod){ try { $this->$upMethod(); $mconfig->setVersion($newVer); }catch (\Exception $ex){ return array('success'=>false,'msg'=>$ex->getMessage()); } } return array('success'=>true,'msg'=>'升级完毕'); } } ?>