<?php
// model for user manage
class upload extends CI_Model {
    function __construct()
    {
        parent::__construct();
    }
    
    function upload_file($name)
	{
		$attArr = array();
		
		if( isset($_FILES[$name]) )
 	 	{
 	 		
			for($i=0;$i<sizeof($_FILES[$name]['name']); $i ++ )
			{
				$fName = "";
				if (!($_FILES[$name]["error"] [$i ]> 0)) 
				{
					$upfile = "upload/".$name.time()."_".hash('crc32' , $_FILES[$name]['name'][$i]);
					$upfile = $upfile.".".pathinfo($_FILES[$name]['name'][$i], PATHINFO_EXTENSION);
					if(move_uploaded_file($_FILES[$name]["tmp_name"][$i], $upfile))
					{
						$attArr[] = "/".$upfile;
						if ( $name == "photo" || $name == "attach_photo" || $name== "msg_photo" ) 
						{
							
							try {
								$org_file = FCPATH . $upfile;
								$tar_file = $org_file;
								if ( $name == "attach_photo" || $name== "msg_photo"  ) 
								{
									$tar_file .= "_";
									copy($org_file, $tar_file);
								}
								
								$this->load->library("simpleimage");
								$this->simpleimage->load($org_file);
								$this->simpleimage->thumbnail(400)->save($tar_file);
							} catch (Exception $e) {
							    tolog( $e->getMessage() );
							}
						}
					}
					
					else
					{
						return null;
					}
				} else {
					return null;
				}
			}
		}
		$attachments =  implode(",",$attArr);
		return 	$attachments;
	}
	
	function delete_file($filename)
	{
		$org_file = FCPATH . $filename;
		try {
			if ( file_exists ($org_file) )
				unlink($org_file);
		} catch (Exception $e) {
		    tolog( $e->getMessage() );
		}
		
		try {
			if ( file_exists ($org_file."_") )
				unlink($org_file."_");
		} catch (Exception $e) {
		    tolog( $e->getMessage() );
		}

	}
}
?>