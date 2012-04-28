<?php

/**************************************************************************
*  Copyright notice
*
*  Copyright 2011-2012 Logic Works GmbH
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*  
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*  
***************************************************************************/

class lw_sitemap extends lw_plugin
{
    function __construct($pid)
    {
        parent::__construct($pid);
    }

	public function buildPageOutput()
	{
        $this->db->setStatement("SELECT * FROM t:lw_pages WHERE (nonav < 1 OR nonav IS NULL) AND (disabled < 1 OR disabled IS NULL) AND (published > 0 OR published IS NOT NULL) AND intranet < 1 ORDER BY seq");
        $pages = $this->db->pselect();
        
        $this->start = $this->params['start'];
        $this->all_pages = $pages;
        return $this->build_sitemap();
    }    
    
    function build_sitemap()
    {
        for ($i=0; $i<count($this->all_pages); $i++) {
            if ($this->all_pages[$i]['relation'] == $this->start ) {
             	if ($this->all_pages[$i]['urlname']) {
                	$url = $this->config['url']['client'].urldecode($this->all_pages[$i]['urlname'])."/";
                }
                else {
                	$url = $this->config['url']['client']."index.php?index=".$this->all_pages[$i]['id'];
                }
                $str.="<div><a href='".$url."'><font size=\"-1\"><b>".urldecode($this->all_pages[$i]['name'])."</b></font></a></div>\n";
                $str.=$this->buildSubtree($this->all_pages[$i]['id'], $indent);
            }                        
        }
        return $str;        
    }

    function buildSubtree($id, $indent)
    {
        $indent = $indent."----";
        for ($i=0; $i<count($this->all_pages); $i++) {
            if ($this->all_pages[$i]['relation'] == $id ) {
                if ($this->all_pages[$i]['urlname']) {
                   $url = $this->config['url']['client'].urldecode($this->all_pages[$i]['urlname'])."/";
                }
                else {
                    $url = $this->config['url']['client']."index.php?index=".$this->all_pages[$i]['id'];
                }
                $str.='<div>'.$indent."<a href='".$url."'><font size=\"-1\">".urldecode($this->all_pages[$i]['name'])."</font></a></div>\n";
                $str.=$this->buildSubtree($this->all_pages[$i]['id'], $indent);
            }                        
        }
        return $str;        
    }
    
    function _getStart($start)
    {
        while ($start != 1 && $start > 0) {
            $this->db->setStatement("select relation from t:lw_pages WHERE id = :id ");
            $this->db->bindParameter('id', 'i', $start);
            $result = $this->db->pselect1();
            $old_start = $start;
            $start = $result['relation'];
        }
        return $old_start;
    }
}
