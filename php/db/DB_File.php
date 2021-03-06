<?php
/**
 * Wikidot - free wiki collaboration software
 * Copyright (c) 2008, Wikidot Inc.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * For more information about licensing visit:
 * http://www.wikidot.org/license
 * 
 * @category Wikidot
 * @package Wikidot_Db
 * @version $Id$
 * @copyright Copyright (c) 2008, Wikidot Inc.
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * Object Model class.
 *
 */
class DB_File extends DB_FileBase {

	private $cachedSite;

	public function getSizeString(){
		return FileHelper::formatSize($this->getSize());	
	}
	
	public function getFilePath(){
		$page = DB_PagePeer::instance()->selectByPrimaryKey($this->getPageId());
		$site = DB_SitePeer::instance()->selectByPrimaryKey($this->getSiteId());
		return WIKIDOT_ROOT."/web/files--sites/".
			$site->getUnixName()."/files/".$page->getUnixName().'/'.$this->getFilename();
	}
	
	public function getResizedDir(){
		$page = DB_PagePeer::instance()->selectByPrimaryKey($this->getPageId());
		$site = DB_SitePeer::instance()->selectByPrimaryKey($this->getSiteId());
		return WIKIDOT_ROOT."/web/files--sites/".
						$site->getUnixName()."/resized-images/".$page->getUnixName().
						'/'.$this->getFilename();	
	}
	
	public function getResizedURI($size = null){
		
		$page = DB_PagePeer::instance()->selectByPrimaryKey($this->getPageId());
		$site = DB_SitePeer::instance()->selectByPrimaryKey($this->getSiteId());
		$out =  'http://'.$site->getDomain()."/local--resized-images/".
			$page->getUnixName().'/'.$this->getFilename();
		if($size){
			$out .= '/'.strtolower($size).'.jpg';	
		}
		return $out;
	}
	
	public function getFileURI(){
		$page = DB_PagePeer::instance()->selectByPrimaryKey($this->getPageId());
		$site = DB_SitePeer::instance()->selectByPrimaryKey($this->getSiteId());
	
		return 	'http://'.$site->getDomain()."/local--files/".
			$page->getUnixName()."/".$this->getFilename();	
	}
	
	public function getUser(){
		if($this->getUserId() == 0){return null;}
		if(is_array($this->prefetched)){
			if(in_array('ozone_user', $this->prefetched)){
				if(in_array('ozone_user', $this->prefetchedObjects)){
					return $this->prefetchedObjects['ozone_user'];
				} else {
					$obj = new DB_OzoneUser($this->sourceRow);
					$obj->setNew(false);
					$this->prefetchedObjects['ozone_user'] = $obj;
					return $obj;
				}
			}
		}
		return DB_OzoneUserPeer::instance()->selectByPrimaryKey($this->getUserId());
		
	}
	
	public function getUserOrString(){
		$user = $this->getUser();
		if($user == null){
			return $this->getUserString();	
		}else{
			return $user;
		}
		
	}
	
}
