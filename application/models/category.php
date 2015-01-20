<?php
// model for user manage
class category extends CI_Model {
	
	function __construct()
    {
	    parent::__construct();
    }
    
	function get_all_categories($user_id)
	{
		$sql = "SELECT * FROM " . TABLE_CATEGORIES . " WHERE (added_by = 0 OR added_by='{$user_id}')";
		return get_sql_result($sql);
	}
	
	function add_custom_category($user_id, $catname)
	{
		$sql = "INSERT INTO " . TABLE_CATEGORIES . " (catname, added_by) VALUES ('{$catname}', '{$user_id}')";
		tolog($sql);
		return get_sql_query($sql);
	}
	
	function get_category_ids($catnames) 
	{
		$catnames = "'" . implode("','", explode(',', $catnames)) . "'";
		$sql = "SELECT catid FROM ". TABLE_CATEGORIES . " WHERE catname IN ({$catnames})";
		$result = get_sql_result($sql);
		$catids = array();
		foreach($result as $key => $catid)
		{
			$catids[] = $catid->catid;
		}
		return implode(",",$catids);
	}
	
}
?>