<?php
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
/*--------------------------Required field Check----------------------------------*/
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;

    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') 
    {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }

    foreach ($required_fields as $field) 
    {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) 
        {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) 
    {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(200, $response);
        $app->stop();
    }
}
/*-----------------------------End Required field Check-------------------------*/

/*-----------------------------Api key Check------------------------------------*/

function authenticate(\Slim\Route $route)
{
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) 
    {
        $db = new DbHandler();        
        $api_key = $headers['Authorization'];        
        
        if (!$key=$db->isValidApiKey($api_key)) 
        {            
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(200, $response);
            $app->stop();
        } else {
            global $get_key;
            $get_key = $key["admin_api_key"];
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(200, $response);
        $app->stop();
    }
}
function authenticate_user(\Slim\Route $route)
{
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) 
    {
        $db = new DbHandler();        
        $user_key = $headers['Authorization'];        
        
        if (!$key=$db->user_isValidApiKey($user_key)) 
        {            
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid User Api key";
            echoRespnse(200, $response);
            $app->stop();
        } else {
            global $user_get_key;
            $user_get_key = $key["user_api_key"];
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "User Api key is misssing";
        echoRespnse(200, $response);
        $app->stop();
    }
}
/*-----------------------------End Api key Check------------------------------------*/

/*---------------------------------Login Check------------------------------------*/

$app->post('/login',function () use ($app)
{
    verifyRequiredParams(array('mobile','password'));

    $mobile = $app->request->post('mobile');
    $password = $app->request->post('password');

    $db = new DbHandler();
    $base_url = $app->request->geturl() . "/apartment/images/admin_profile/";

    if($row = $db->login($mobile,$password)) 
    {        
        $response['admin_api_key']=$row[0]['admin_api_key'];
        $response['admin_first_name']=$row[0]['admin_first_name'];
        $response['admin_last_name']=$row[0]['admin_last_name'];
        $response['admin_email']=$row[0]['admin_email'];
        $response['admin_mobile']=$row[0]['admin_mobile'];
        $response['admin_apartment_name']=$row[0]['admin_apartment_name'];        
        $response['admin_profile']=$base_url.$row[0]['admin_profile'];

        $response['error'] = false;
        $response['message'] ="you are successfully login";
        echoRespnse(201, $response);
    } 
    else 
    {
        $response["error"] = true;
        $response['message'] = "Invalid UserName or password.";
        echoRespnse(400, $response);
    }
});

/*-----------------------------End Login Check------------------------------------*/

/*-------------------------------Edit Admin------------------------------------*/

$app->post('/edit_admin','authenticate',function () use ($app){
    verifyRequiredParams(array('admin_first_name','admin_last_name','admin_email','admin_apartment_name'));
    
    global $get_key;
    
    $admin_first_name = $app->request->post('admin_first_name'); 
    $admin_last_name = $app->request->post('admin_last_name');
    $admin_email = $app->request->post('admin_email');       
    $admin_apartment_name = $app->request->post('admin_apartment_name');

    $db = new DbHandler();

    $base_url = $app->request->geturl() . "/apartment/images/admin_profile/";
    $file_name = '';

    if(isset($_FILES['admin_profile'])) 
    {
        if($file_name = image($_FILES['admin_profile'])) 
        { 
            if($db->edit_admin($get_key,$admin_first_name,$admin_last_name,$admin_email,$admin_apartment_name,$file_name))
            {
                
                $response['error'] = false;
                $response['message'] = "Admin Data update successfully.";
                echoRespnse(200,$response);
            }
            else
            {
                $response['error'] = true;
                $response['message'] = "Admin Data update Not successfully!";
                echoRespnse(400,$response); 
            }
        }
        else
            {
                $response['error'] = true;
                $response['message'] = "Admin profile update Not successfully!";
                echoRespnse(400,$response); 
            } 
    }
    else{
            $response['error'] = true;
            $response['message'] = "Required field(s)  admin_profile is missing or empty";
            echoRespnse(400,$response); 
    }    
        
});


function image($file = array())
{
    $app = \Slim\Slim::getInstance();
    $dir = dirname(dirname(dirname(__FILE__))) . "/apartment/images/admin_profile/";

    $errors = array();

    $path_parts = pathinfo($_FILES["admin_profile"]["name"]);
    $file_name = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];

    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];

    $explode = explode('.', $file_name);
    $file_ext = strtolower(end($explode));
    $response = array();

    if (empty($errors) == true) 
    {
        move_uploaded_file($file_tmp, $dir . $file_name);
        return $file_name;
    }
}
/*------------------------------ End Edit Admin ------------------------------------*/

/*-------------------------------Add Apatrment------------------------------------*/

$app->post('/add_block','authenticate',function () use ($app){
    verifyRequiredParams(array('block_name','block_no'));
    
    global $get_key;
    
    $block_name = $app->request->post('block_name'); 
    $block_no = $app->request->post('block_no');
    
    $db = new DbHandler();

    if($db->add_block($get_key,$block_name,$block_no))
    {
        
        $response['error'] = false;
        $response['message'] = "Block Data Add successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Block Data Add Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Add Apatrment------------------------------------*/

/*-------------------------------Update block------------------------------------*/

$app->post('/update_block',function () use ($app){
    verifyRequiredParams(array('block_id','block_name','block_no'));
    
        
    $block_id = $app->request->post('block_id'); 
    $block_name = $app->request->post('block_name'); 
    $block_no = $app->request->post('block_no');
    
    $db = new DbHandler();

    if($db->update_block($block_id,$block_name,$block_no))
    {        
        $response['error'] = false;
        $response['message'] = "Block Data Update successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Block Data Update Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Update block------------------------------------*/

/*-------------------------------block_list------------------------------------*/

$app->get('/block_list','authenticate',function () use ($app){
       
    global $get_key;
       
    $db = new DbHandler();
    $response = array();
    
    if($row=$db->block_list($get_key))
    {
        $response["row"] = array();
        foreach ($row as $key => $value1)  
        {
            $tmp = array();
            $tmp["block_id"] = $value1["block_id"];
            $tmp["block_name"] = $value1["block_name"];
            $tmp["block_no"] = $value1["block_no"];

            array_push($response["row"],$tmp);
        }

        $response['error'] = false;
        $response['message'] = "Block list successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Block list Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn block_list------------------------------------*/

/*-------------------------------Add User------------------------------------*/

$app->post('/add_user','authenticate',function () use ($app){
    verifyRequiredParams(array('user_first_name','user_last_name','user_email','user_mobile','user_block_no','user_block_id','user_profession'));
    
    global $get_key; 

    $user_first_name = $app->request->post('user_first_name'); 
    $user_last_name = $app->request->post('user_last_name'); 
    $user_email = $app->request->post('user_email');
    $user_mobile = $app->request->post('user_mobile');
    $user_block_no = $app->request->post('user_block_no');
    $user_block_id = $app->request->post('user_block_id');
    $user_profession = $app->request->post('user_profession');
    
    $db = new DbHandler();

    if(isset($_FILES['user_profile'])) 
    {
        if($user_profile = user_image($_FILES['user_profile'])) 
        {
            if($db->add_user($get_key,$user_first_name,$user_last_name,$user_email,$user_mobile,$user_profile,$user_block_no,$user_block_id,$user_profession))
            {        
                $response['error'] = false;
                $response['message'] = "User Data Add successfully.";
                echoRespnse(200,$response);
            }
            else
            {
                $response['error'] = true;
                $response['message'] = "User Data Add Not successfully!";
                echoRespnse(400,$response); 
            }
        }
        else{
            $response['error'] = true;
            $response['message'] = "profile Upload Not successfully!";
            echoRespnse(400,$response);
        }    
    }
    else{
            $response['error'] = true;
            $response['message'] = "Required field(s)  user_profile is missing or empty";
            echoRespnse(400,$response); 
    } 
        
});
function user_image($file = array())
{
    $app = \Slim\Slim::getInstance();
    $dir = dirname(dirname(dirname(__FILE__))) . "/apartment/images/user_profile/";

    $errors = array();

    $path_parts = pathinfo($_FILES["user_profile"]["name"]);
    $file_name = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];

    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];

    $explode = explode('.', $file_name);
    $file_ext = strtolower(end($explode));
    $response = array();

    if (empty($errors) == true) 
    {
        move_uploaded_file($file_tmp, $dir . $file_name);
        return $file_name;
    }
}
/*-------------------------------Edn Add User------------------------------------*/

/*-------------------------------user_list------------------------------------*/

$app->get('/user_list','authenticate',function () use ($app){
       
    global $get_key;
       
    $db = new DbHandler();
    $response = array();

     $base_url = $app->request->geturl() . "/apartment/images/user_profile/";

    if($row=$db->user_list($get_key))
    {
        $response["row"] = array();
        foreach ($row as $key => $value1)  
        {
            $tmp = array();
            $tmp["user_api_key"] = $value1["user_api_key"];
            $tmp["user_first_name"] = $value1["user_first_name"];
            $tmp["user_last_name"] = $value1["user_last_name"];
            $tmp["user_email"] = $value1["user_email"];
            $tmp["user_mobile"] = $value1["user_mobile"];
            $tmp["user_block_name"] = $value1["block_name"];
            $tmp["user_block_no"] = $value1["user_block_no"];
            $tmp["user_profession"] = $value1["user_profession"];
            $tmp["event_description"] = $base_url.$value1["user_profile"];
           
            array_push($response["row"],$tmp);
        }

        $response['error'] = false;
        $response['message'] = "User list successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "User list Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn user_list------------------------------------*/

/*-------------------------------Add notice------------------------------------*/

$app->post('/add_notice','authenticate',function () use ($app){
    verifyRequiredParams(array('notice_date','notice_time','notice_title','notice_description'));
    
    global $get_key;
    
    $notice_date = $app->request->post('notice_date'); 
    $notice_time = $app->request->post('notice_time');
    $notice_title = $app->request->post('notice_title');
    $notice_description = $app->request->post('notice_description');
    
    $db = new DbHandler();

    if($db->add_notice($notice_date,$notice_time,$notice_title,$notice_description,$get_key))
    {
        
        $response['error'] = false;
        $response['message'] = "Notice Data Add successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Notice Data Add Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Add notice------------------------------------*/

/*-------------------------------event_list------------------------------------*/

$app->get('/event_list','authenticate',function () use ($app){
       
    global $get_key;
       
    $db = new DbHandler();
    $response = array();

    if($row=$db->event_list($get_key))
    {
        $response["row"] = array();
        foreach ($row as $key => $value1)  
        {
            $tmp = array();
            $tmp["event_id"] = $value1["event_id"];
            $tmp["event_date"] = $value1["event_date"];
            $tmp["event_time"] = $value1["event_time"];
            $tmp["event_title"] = $value1["event_title"];
            $tmp["event_description"] = $value1["event_description"];
           
            array_push($response["row"],$tmp);
        }

        $response['error'] = false;
        $response['message'] = "Event list successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Event list Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn event_list------------------------------------*/

/*-------------------------------Add event------------------------------------*/

$app->post('/add_event','authenticate',function () use ($app){
    verifyRequiredParams(array('event_date','event_time','event_title','event_description'));
    
    global $get_key;
    
    $event_date = $app->request->post('event_date'); 
    $event_time = $app->request->post('event_time');
    $event_title = $app->request->post('event_title');
    $event_description = $app->request->post('event_description');
    
    $db = new DbHandler();

    if($db->add_event($event_date,$event_time,$event_title,$event_description,$get_key))
    {
        
        $response['error'] = false;
        $response['message'] = "Event Data Add successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Event Data Add Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Add event------------------------------------*/

/*-------------------------------event_list------------------------------------*/

$app->get('/event_list','authenticate',function () use ($app){
       
    global $get_key;
       
    $db = new DbHandler();
    $response = array();

    if($row=$db->event_list($get_key))
    {
        $response["row"] = array();
        foreach ($row as $key => $value1)  
        {
            $tmp = array();
            $tmp["event_id"] = $value1["event_id"];
            $tmp["event_date"] = $value1["event_date"];
            $tmp["event_time"] = $value1["event_time"];
            $tmp["event_title"] = $value1["event_title"];
            $tmp["event_description"] = $value1["event_description"];
           
            array_push($response["row"],$tmp);
        }

        $response['error'] = false;
        $response['message'] = "Event list successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Event list Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn event_list------------------------------------*/

/*-------------------------------Add notification------------------------------------*/

$app->post('/add_notification','authenticate',function () use ($app){
    verifyRequiredParams(array('notification_title','notification_description'));
    
    global $get_key;
    
    $notification_title = $app->request->post('notification_title'); 
    $notification_description = $app->request->post('notification_description');
        
    $db = new DbHandler();

    if($db->add_notification($notification_title,$notification_description,$get_key))
    {
        
        $response['error'] = false;
        $response['message'] = "Notification Data Add successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Notification Data Add Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Add notification------------------------------------*/

/*-------------------------------Add complaint------------------------------------*/

$app->post('/add_complaint','authenticate_user',function () use ($app){
    verifyRequiredParams(array('complaint_description'));
    
    global $user_get_key; 

    $complaint_description = $app->request->post('complaint_description');     
    
    $db = new DbHandler();

    if(isset($_FILES['complaint_images'])) 
    {
        if($complaint_images = complaint_image($_FILES['complaint_images'])) 
        {
            if($db->add_complaint($user_get_key,$complaint_description,$complaint_images))
            {        
                $response['error'] = false;
                $response['message'] = "complaint Data Add successfully.";
                echoRespnse(200,$response);
            }
            else
            {
                $response['error'] = true;
                $response['message'] = "complaint Data Add Not successfully!";
                echoRespnse(400,$response); 
            }
        }
        else{
            $response['error'] = true;
            $response['message'] = "complaint Images Upload Not successfully!";
            echoRespnse(400,$response);
        }    
    }
    else{
            $response['error'] = true;
            $response['message'] = "Required field(s)  complaint_images is missing or empty";
            echoRespnse(400,$response); 
    } 
        
});
function complaint_image($file = array())
{
    $app = \Slim\Slim::getInstance();
    $dir = dirname(dirname(dirname(__FILE__))) . "/apartment/images/complaint_images/";

    $errors = array();

    $path_parts = pathinfo($_FILES["complaint_images"]["name"]);
    $file_name = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];

    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];

    $explode = explode('.', $file_name);
    $file_ext = strtolower(end($explode));
    $response = array();

    if (empty($errors) == true) 
    {
        move_uploaded_file($file_tmp, $dir . $file_name);
        return $file_name;
    }
}
/*-------------------------------Edn Add complaint------------------------------------*/


/*-------------------------------Add complaint_comment------------------------------------*/

$app->post('/complaint_comment','authenticate_user',function () use ($app){
    verifyRequiredParams(array('comment_complaint_id','comment_comment'));
    
   global $user_get_key; 
    
    $comment_complaint_id = $app->request->post('comment_complaint_id'); 
    $comment_comment = $app->request->post('comment_comment');
        
    $db = new DbHandler();

    if($db->complaint_comment($comment_complaint_id,$comment_comment,$user_get_key))
    {
        
        $response['error'] = false;
        $response['message'] = "Complaint Comment Add successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Complaint Comment Add Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Add complaint_comment------------------------------------*/

/*-------------------------------complaint_list------------------------------------*/

$app->get('/complaint_list','authenticate',function () use ($app){
       
    global $get_key;
       
    $db = new DbHandler();
    $response = array();

    $base_url = $app->request->geturl() . "/apartment/images/complaint_images/";
    if($row=$db->complaint_list($get_key))
    {
        //print_r($row_comm); exit();
        
        $response["row"] = array();
        foreach ($row as $key => $value1)  
        {
            $complaint_id=$value1['complaint_id'];
            $row_comm=$db->complaint_comm_list($complaint_id);

            $tmp = array();
            $tmp["complaint_id"] = $value1["complaint_id"];
            $tmp["complaint_description"] = $value1["complaint_description"];
            $tmp["complaint_date"] = $value1["complaint_date"];                       
            $tmp["complaint_images"] = $base_url.$value1["complaint_images"];
            $tmp["comment"] = array();
            foreach ($row_comm as $key2 => $value2)  
            {
                $tmp1 = array();
                $tmp1["comment_complaint_id"] = $value2["comment_complaint_id"];
                $tmp1["comment_comment"] = $value2["comment_comment"];
                $tmp1["comment_date"] = $value2["comment_date"];
                $tmp1["comment_comment_by"] = $value2["user_first_name"].' '.$value2["user_last_name"];
                
                array_push($tmp["comment"],$tmp1);
            }              
            array_push($response["row"],$tmp);
        }

        $response['error'] = false;
        $response['message'] = "complaint list successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "complaint list Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn complaint_list------------------------------------*/

/*-------------------------------Add helpdesk------------------------------------*/

$app->post('/add_helpdesk','authenticate',function () use ($app){
    verifyRequiredParams(array('helpdesk_mobile','helpdesk_name','helpdesk_profession','helpdesk_address'));
    
    global $get_key;
    
    $helpdesk_mobile = $app->request->post('helpdesk_mobile'); 
    $helpdesk_name = $app->request->post('helpdesk_name');
    $helpdesk_profession = $app->request->post('helpdesk_profession');
    $helpdesk_address = $app->request->post('helpdesk_address');
    
    $db = new DbHandler();

    if($db->add_helpdesk($helpdesk_mobile,$helpdesk_name,$helpdesk_profession,$helpdesk_address,$get_key))
    {
        
        $response['error'] = false;
        $response['message'] = "helpdesk Data Add successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "helpdesk Data Add Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn Add helpdesk------------------------------------*/

/*-------------------------------helpdesk_list------------------------------------*/

$app->get('/helpdesk_list','authenticate',function () use ($app){
       
    global $get_key;
       
    $db = new DbHandler();
    $response = array();

    if($row=$db->helpdesk_list($get_key))
    {
        $response["row"] = array();
        foreach ($row as $key => $value1)  
        {
            $tmp = array();
            $tmp["helpdesk_mobile"] = $value1["helpdesk_mobile"];
            $tmp["helpdesk_name"] = $value1["helpdesk_name"];
            $tmp["helpdesk_profession"] = $value1["helpdesk_profession"];
            $tmp["helpdesk_address"] = $value1["helpdesk_address"];            
           
            array_push($response["row"],$tmp);
        }

        $response['error'] = false;
        $response['message'] = "helpdesk list successfully.";
        echoRespnse(200,$response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "helpdesk list Not successfully!";
        echoRespnse(400,$response); 
    }
      
});
/*-------------------------------Edn helpdesk_list------------------------------------*/

function echoRespnse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');


    echo json_encode($response);
}
$app->run();
?>



