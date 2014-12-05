<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');

class CCategoryHelper
{
        protected static $row = null;

	static public function getCategories($rows)
	{


		// Reset array key
		foreach( $rows as $key=>$row)
		{
			$row				= (array)$row;
			$keyId				= $row['id'];
			$tmpRows[$keyId]	= $row;
		}

		foreach( $tmpRows as $key=>$row )
		{
			$row['nodeText']	= CCategoryHelper::_getCat( $tmpRows, $row['id'] );

			$row['nodeId']		= explode( ',',CCategoryHelper::_getCatId( $tmpRows, $row['id'] ) );
			$sort1[$key]		= $row['nodeId'][0];
			$sort2[$key]		= $row['parent'];

			$categories[]		= $row;
		}
		//array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $categories);

		return	$categories;

	}

	static private function _getCat($rows,$id)
	{
	    if($rows[$id]['parent'] > 0 && $rows[$id]['parent'] != $rows[$id]['id']) {
	        return CCategoryHelper::_getCat($rows, $rows[$id]['parent']) . ' &rsaquo; ' . JText::_( $rows[$id]['name'] );
	    }
	    else {
	    	//Return JText value if using enlish character
	    	if(!strtoupper($rows[$id]['name']) === $rows[$id]['name']){
	    		return JText::_( $rows[$id]['name'] );
	    	} else{
	    		return $rows[$id]['name'];
	    	}
	    }
	}

	static private function _getCatId($rows,$id)
	{
	    if($rows[$id]['parent'] > 0 && $rows[$id]['parent'] != $rows[$id]['id']) {
	        return CCategoryHelper::_getCatId($rows, $rows[$id]['parent']) . ',' . $rows[$id]['id'];
	    }
	    else {
			return $rows[$id]['id'];
	    }
	}

        /**
         * Generate category children
         *
         * @access  public
         * @returns Array  of category id
         * @since   Jomsocial 2.6
         **/
        static public function getCategoryChilds($rows, $catId)
        {
            // Reset array key
            foreach( $rows as $key=>$row)
            {
                $row = (array)$row;
                $tmpRows[$row['id']] = $row;
            }

            self::$row = $tmpRows;

            $catTree = self::_getCatTree($catId);

            return $catTree;
        }

        /**
         * Recursive function to get category child
         *
         * @access  public
         * @returns Array  of category id
         * @since   Jomsocial 2.6
         **/
        static private function _getCatTree($catId)
        {
            $catTree = array();

            foreach (self::$row as $id => $row) {
                if ( $row['parent'] == $catId) {
                    $catTree[] = $id;
                    $catTree = array_merge($catTree, self::_getCatTree($id));
                    unset(self::$row[$id]);
                }
            }
            return $catTree;
        }

	static public function getSelectList( $app, $options, $catid=null, $required=false, $update=false )
	{
		$attr	=   '';

		switch ($app)
		{
			case 'groups' : $name = 'categoryid'; break;
			case 'videos' : $name = 'category_id'; break;
			default : $name = 'catid';
		}

		if( $required )
		{
			$attr	.= 'class="required" ';
		}

		if( $update )
		{
			$attr	.= 'onchange="updateCategoryId()" ';
		}

        // Obtain a list of columns
        foreach ($options as $key => $row) {
            $nodeText[$key]  = $row['nodeText'];
        }
        array_multisort(array_map('strtolower', $nodeText), SORT_ASC, $options);


		return JHTML::_('select.genericlist', $options, $name, array('list.attr' =>$attr, 'option.key'=>'id', 'option.text'=>'nodeText', 'list.select'=>$catid, 'option.text.toHtml'=>false));

	}

	/*
        static public function getChildren($rows)
        {
                foreach($rows as $row)
                {
                        $row				= (array)$row;
			$keyId				= $row['id'];
			$tmpRows[$keyId]                = $row;
                }
                if(isset($tmpRows))
                {
                    foreach($tmpRows as $key=>$row)
                    {
                        if($row['parent']!=0 && count($tmpRows)>1)
                        {
                            $tmpRows = CCategoryHelper::sumCount($tmpRows);
                        }
                    }
                }
                else{
                    $tmpRows = $rows;
                }
                return $tmpRows;
        }

        static public function sumCount($rows)
        {
            foreach( $rows as $key=>$row )
            {
		if($row['parent']!= 0)
                {
                           $rows[$row['parent']]['count'] +=$row['count'];
                           $rows[$row['id']]['count'] =0;
                }
            }
            return CCategoryHelper::removeChild($rows);
        }

        static public function removeChild($rows)
        {
            foreach($rows as $key=>$row)
            {
                    if($row['parent']!=0 && $row['count'] == 0)
                    {
                        unset($rows[$row['id']]);
                    }
            }
            return CCategoryHelper::getChildren($rows);

        }
		*/

		/**
		 * Generate category count by adding it to parent's sum.
		 *
		 * @access  public
		 * @returns Array  An array of categories object
		 * @since   Jomsocial 2.4
		 **/
		static public function getParentCount($categories, $categoryid = 0)
		{
			// Add count to parent category.
			foreach ($categories as $cat) {
				$parent = $cat->parent ;
				while ($parent != 0) {
					$categories[$parent]->count += $cat->total;
					$parent = $categories[$parent]->parent;
				}
			}

			// Filter the category by parent id.
			foreach ($categories as $key => $cat) {
				if ($cat->parent != (int)$categoryid) {
					unset($categories[$key]);
				}
			}

			return $categories;
		}

}