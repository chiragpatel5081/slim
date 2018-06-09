<?php
class DbHandler
{   
    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';        
        $db = new DbConnect();
        $this->conn = $db->connect();
    }    
    /*---------------------------------Api key Check-----------------------------------*/
    public function isValidApiKey($api_key)
    {        
        $sql = "SELECT admin_api_key FROM admin WHERE admin_api_key = '$api_key'";
        $data = mysqli_query($this->conn,$sql);
       
        if(mysqli_num_rows($data)>0)
        {        
            return $key = mysqli_fetch_assoc($data);
        }
        else
        {
            return false;
        }
    }

    public function user_isValidApiKey($user_key)
    {        
        $sql = "SELECT user_api_key FROM user WHERE user_api_key = '$user_key'";
        $data = mysqli_query($this->conn,$sql);
       
        if(mysqli_num_rows($data)>0)
        {        
            return $key = mysqli_fetch_assoc($data);
        }
        else
        {
            return false;
        }
    }
    /*-----------------------------End Api key Check------------------------------------*/
    /*---------------------------------Login Check-----------------------------------*/
    public function login($mobile,$password)
    {
        $pass=md5($password);
        $sql = "select * from admin where admin_mobile = '$mobile' AND admin_password = '$password'";
        $result = mysqli_query($this->conn,$sql);        
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);
        if($row)
        {
        
            return $row;
        }
        else
        {
            return false;
        }
    }
    /*-----------------------------End Login Check------------------------------------*/
        
    /*----------------------------- Edit Admin------------------------------------*/ 

    public function edit_admin($get_key,$admin_first_name,$admin_last_name,$admin_email,$admin_apartment_name,$file_name)
    {
       $sql = "UPDATE admin set admin_first_name='$admin_first_name',admin_last_name='$admin_last_name',admin_email='$admin_email',admin_profile='$file_name',admin_apartment_name='$admin_apartment_name' where admin_api_key = '$get_key'";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }   

    /*----------------------------- End Admin ------------------------------------*/ 

    /*----------------------------- Add Apatrment------------------------------------*/ 

    public function add_block($get_key,$block_name,$block_no)
    {
        $sql = "INSERT INTO block (block_name, block_no,block_admin_key) values ('$block_name','$block_no', '$get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add Apatrment ------------------------------------*/ 

    /*----------------------------- Update Apatrment------------------------------------*/ 

    public function update_block($block_id,$block_name,$block_no)
    {
        $sql = "UPDATE block set block_name='$block_name',block_no='$block_no' where block_id = '$block_id'";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*----------------------------- Update Apatrment ------------------------------------*/ 

    /*----------------------------- Add Apatrment------------------------------------*/ 

    public function add_user($get_key,$user_first_name,$user_last_name,$user_email,$user_mobile,$user_profile,$user_block_no,$user_block_id,$user_profession)
    {
        $api_key=$this->generateApiKey();
        $sql = "INSERT INTO user (user_api_key,user_admin_api_key,user_first_name, user_last_name,user_email,user_mobile,user_profile,user_block_no,user_block_id,user_profession) values ('$api_key','$get_key','$user_first_name','$user_last_name', '$user_email','$user_mobile','$user_profile','$user_block_no','$user_block_id','$user_profession')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add Apatrment ------------------------------------*/ 

    /*----------------------------- Add notice------------------------------------*/ 

    public function add_notice($notice_date,$notice_time,$notice_title,$notice_description,$get_key)
    {        
        $sql = "INSERT INTO notice (notice_date,notice_time,notice_title, notice_description,notice_admin_api_key) values ('$notice_date','$notice_time','$notice_title','$notice_description', '$get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add notice ------------------------------------*/ 

    /*----------------------------- Add event------------------------------------*/ 

    public function add_event($event_date,$event_time,$event_title,$event_description,$get_key)
    {        
        $sql = "INSERT INTO event (event_date,event_time,event_title, event_description,event_admin_api_key) values ('$event_date','$event_time','$event_title','$event_description', '$get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add event ------------------------------------*/ 

    /*----------------------------- Add notification------------------------------------*/ 

    public function add_notification($notification_title,$notification_description,$get_key)
    {        
        $sql = "INSERT INTO notification (notification_title,notification_description,notification_admin_api_key) values ('$notification_title','$notification_description','$get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add notification ------------------------------------*/ 

    /*----------------------------- Add complaint------------------------------------*/ 

    public function add_complaint($user_get_key,$complaint_description,$complaint_images)
    {        
        $sql = "INSERT INTO complaint (complaint_description,complaint_images,complaint_user_api_key) values ('$complaint_description','$complaint_images','$user_get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add complaint ------------------------------------*/ 

    /*----------------------------- Add complaint_comment------------------------------------*/ 

    public function complaint_comment($comment_complaint_id,$comment_comment,$user_get_key)
    {        
        $sql = "INSERT INTO complaint_comment (comment_complaint_id,comment_comment,comment_comment_by) values ('$comment_complaint_id','$comment_comment','$user_get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add complaint_comment ------------------------------------*/

    /*----------------------------- complaint_list------------------------------------*/ 

    public function complaint_list($get_key)
    {        
        $sql = "SELECT  * 
                    FROM  admin a 
                    LEFT JOIN user u ON a.admin_api_key=u.user_admin_api_key
                    LEFT JOIN complaint c ON u.user_api_key=c.complaint_user_api_key
                    WHERE a.admin_api_key = '$get_key'
                    ORDER BY complaint_id DESC";
        $result = mysqli_query($this->conn,$sql);        
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);        
        if($row)
        {        
            return $row;
        }
        else
        {
            return false;
        }        
    }

    public function complaint_comm_list($complaint_id)
    {        
        
        $sql = "SELECT  * 
                FROM  complaint_comment co 
                LEFT JOIN user u ON co.comment_comment_by=u.user_api_key                    
                WHERE co.comment_complaint_id = '$complaint_id'
                ORDER BY comment_id DESC";
        $result = mysqli_query($this->conn,$sql);        
        $row_comm=mysqli_fetch_all($result,MYSQLI_ASSOC);
        if($row_comm)
        {        
            return $row_comm;
        }
        else
        {
            return false;
        }        
    }

    /*-----------------------------End complaint_list ------------------------------------*/ 

    /*----------------------------- event_list ------------------------------------*/
    public function event_list($get_key)
    {        
        
        $sql = "SELECT  * FROM  event
                WHERE event_admin_api_key = '$get_key'
                ORDER BY event_id DESC";
        $result = mysqli_query($this->conn,$sql);        
        $row_comm=mysqli_fetch_all($result,MYSQLI_ASSOC);
        if($row_comm)
        {        
            return $row_comm;
        }
        else
        {
            return false;
        }        
    }
    /*-----------------------------End event_list ------------------------------------*/ 

    /*----------------------------- user_list ------------------------------------*/
    public function user_list($get_key)
    {        
        
        $sql = "SELECT  * 
                FROM  user u
                LEFT JOIN block b ON u.user_block_id=b.block_id                    
                WHERE u.user_admin_api_key = '$get_key'
                ORDER BY user_id DESC";
        $result = mysqli_query($this->conn,$sql);        
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);
        if($row)
        {        
            return $row;
        }
        else
        {
            return false;
        }        
    }
    /*-----------------------------End user_list ------------------------------------*/

     /*----------------------------- user_list ------------------------------------*/
    public function block_list($get_key)
    {        
        
        $sql = "SELECT  * 
                FROM   block
                WHERE block_admin_key = '$get_key'";
        $result = mysqli_query($this->conn,$sql);        
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);
        if($row)
        {        
            return $row;
        }
        else
        {
            return false;
        }        
    }
    /*-----------------------------End user_list ------------------------------------*/ 

    /*----------------------------- Add helpdesk------------------------------------*/ 

    public function add_helpdesk($helpdesk_mobile,$helpdesk_name,$helpdesk_profession,$helpdesk_address,$get_key)
    {        
        $sql = "INSERT INTO helpdesk (helpdesk_mobile,helpdesk_name,helpdesk_profession,helpdesk_address,helpdesk_admin_api_key) values ('$helpdesk_mobile','$helpdesk_name','$helpdesk_profession','$helpdesk_address','$get_key')";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) 
        {           
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /*-----------------------------End Add helpdesk ------------------------------------*/

    /*----------------------------- helpdesk_list ------------------------------------*/
    public function helpdesk_list($get_key)
    {        
        
        $sql = "SELECT  * 
                FROM  helpdesk                
                WHERE helpdesk_admin_api_key = '$get_key'
                ORDER BY helpdesk_id DESC";
        $result = mysqli_query($this->conn,$sql);        
        $row=mysqli_fetch_all($result,MYSQLI_ASSOC);
        if($row)
        {        
            return $row;
        }
        else
        {
            return false;
        }        
    }
    /*-----------------------------End helpdesk_list ------------------------------------*/

    /* Generating random Unique MD5 String for user Api key */
    private function generateApiKey()
    {
        return md5(uniqid(rand(), true));
    }
}
?>