<?php

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class api extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    //Sign Up - Register User
    public function signup() {
        $_REQ = user_request();

        $username = $_REQ["username"];
        $password = $_REQ["password"];
        $email = $_REQ["email"];
        $language = $_REQ["language"];
        $country = $_REQ["country"];
        $description = $_REQ["description"];
        $paypal_id = $_REQ["paypal_id"];

        if (IsNullOrEmptyString($username) || IsNullOrEmptyString($email)) {
            echo json_capsule(array("response" => "fail", "message" => "Incorrect request parameters"));
            return;
        }

        $this->load->model("user");

        if ($this->user->is_existuserByEmail($email)) {
            echo json_capsule(array("response" => "fail", "message" => "Email already exists."));
            return;
        }

        $this->load->model("upload");
        $photo = $this->upload->upload_file("photo");

        if ($photo === null) {
            echo json_capsule(array("response" => "fail", "message" => "Failed to upload photo"));
            return;
        }

        if ($this->user->is_existuserByName($username)) {
            echo json_capsule(array("response" => "fail", "message" => "Username already exists."));
            return;
        }

        $this->user->register_user($username, $password, $email, $language, $country, $description, $photo, $paypal_id);
        echo json_capsule(array("response" => "success", "message" => "Register user successfully."));
    }

    //Login User
    public function login() {
        $_REQ = user_request();

        $username = $_REQ["username"];
        $password = $_REQ["password"];

        $this->load->model("user");

        $user = $this->user->is_existuserByName($username);

        if ($user === false) {
            echo json_capsule(array("response" => "fail", "message" => "Username not found."));
            return;
        }

        if ($password != $user->password) {
            echo json_capsule(array("response" => "fail", "message" => "Password is wrong."));
            return;
        }
        $user->password = '';

        echo json_capsule(array("response" => "success", "message" => "Register user successfully.", "user" => $user));
    }

    //Register Push Token
    public function register_push_token() {
        $_REQ = user_request();

        $user_id = $_REQ["user_id"];
        $device_token = $_REQ["device_token"];
        $device_type = $_REQ["device_type"];

        $this->load->model("user");

        $this->user->register_push_token($user_id, $device_token, $device_type);

        echo json_capsule(array("response" => "success", "message" => "Register token successfully."));
    }

    //Get All Categories
    public function get_all_categories() {
        $_REQ = user_request();

        $user_id = $_REQ["user_id"];


        $this->load->model("category");

        $cats = $this->category->get_all_categories($user_id);

        echo json_capsule(array("response" => "success", "message" => "Categories", "categories" => $cats));
    }

    //Add Custom Category
    public function add_custom_category() {
        $_REQ = user_request();

        $user_id = $_REQ["user_id"];
        $catname = $_REQ["catname"];

        $this->load->model("category");

        $this->category->add_custom_category($user_id, $catname);

        echo json_capsule(array("response" => "success", "message" => "success for custom categories"));
    }

    //Add Marked Category by user
    public function add_marked_category_by_user() {
        $_REQ = user_request();

        $user_id = $_REQ["user_id"];
        $catids = $_REQ["catids"];

        $this->load->model("user");

        $this->user->add_marked_category_by_user($user_id, $catids);

        echo json_capsule(array("response" => "success", "message" => "success for marked categories"));
    }

    //Get Questions
    public function get_questions() {
        $_REQ = user_request();

        $user_id = $_REQ["user_id"];


        $this->load->model("question");

        $questions = $this->question->get_questions_by_user($user_id);

        echo json_capsule(array("response" => "success", "message" => "Questions", "questions" => $questions));
    }

    //Add Question by user
    public function add_question() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $catnames = $_REQ["catnames"];
        $text = $_REQ["text"];
        $lat = $_REQ["lat"];
        $lon = $_REQ["lon"];
        $contact = $_REQ["contact"];
        $public_access = $_REQ["public_access"];

        tolog("Contact = " . $contact);

        if (IsNullOrEmptyString($userid) || IsNullOrEmptyString($text)) {
            echo json_capsule(array("response" => "fail", "message" => "Incorrect request parameters"));
            return;
        }

        $this->load->model("upload");
        $audios = $this->upload->upload_file("attach_audio");
        $photos = $this->upload->upload_file("attach_photo");
        $videos = $this->upload->upload_file("attach_video");

        if ($photos === null || $audios === null || $videos === null) {
            echo json_capsule(array("response" => "fail", "message" => "Failed to upload attached files"));
            return;
        }

        $this->load->model("question");

        $this->question->add_question($userid, $catnames, $text, $audios, $photos, $videos, $lat, $lon, $contact, $public_access);

        echo json_capsule(array("response" => "success", "message" => "Success to add question"));
    }

    //Delete Question by user
    public function delete_question() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $queid = $_REQ["que_id"];
        $state = $_REQ["state"];

        $this->load->model("question");

        $this->question->delete_question($userid, $queid, $state);

        echo json_capsule(array("response" => "success", "message" => "success to remove question"));
    }

    //Read Question by user
    public function read_question() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $queid = $_REQ["que_id"];

        $this->load->model("question");

        $this->question->read_question($userid, $queid);

        echo json_capsule(array("response" => "success", "message" => "Success to mark question as read"));
    }

    //Get Answers Group By User Request
    public function get_answeres() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $queid = $_REQ["que_id"];

        $this->load->model("question");

        if ($this->question->get_question_by_user_and_id($userid, $queid) === false) {
            echo json_capsule(array("response" => "fail", "message" => "Invalid Question or User"));
        }

        $this->load->model("answer");
        $answers = $this->answer->get_answers($userid, $queid);

        echo json_capsule(array("response" => "success", "message" => "Answered Users", "answers" => $answers));
    }

    //Send Message Request
    public function send_message() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $queid = $_REQ["que_id"];
        $otherid = $_REQ["other_id"];
        $type = $_REQ["type"];
        $cost = $_REQ["cost"];
        $content = null;

        $this->load->model("upload");

        switch ($type) {
            case MSG_TYPE_AUDIO:
                $content = $this->upload->upload_file("msg_audio");
                break;
            case MSG_TYPE_PHOTO:
                $content = $this->upload->upload_file("msg_photo");
                break;
            case MSG_TYPE_VIDEO:
                $content = $this->upload->upload_file("msg_video");
                break;
            default:
                $content = $_REQ["text"];
                break;
        }

        if ($content === null) {
            echo json_capsule(array("response" => "fail", "message" => "Failed to upload attached files"));
            return;
        }

        $this->load->model("answer");

        $this->answer->add_answer($userid, $queid, $otherid, $type, $cost, $content);

        echo json_capsule(array("response" => "success", "message" => "Success to send message"));
    }

    // Get Messages Request
    public function get_messages() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $queid = $_REQ["que_id"];
        $otherid = $_REQ["other_id"];
        $last_unlock_time = $_REQ["last_unlock_time"];
        $last_answer_id = $_REQ["last_answer_id"];


        $this->load->model("answer");
        $messages = $this->answer->get_messages($userid, $queid, $otherid, $last_answer_id);
        $unlocks = $this->answer->get_unlocks($userid, $queid, $otherid, $last_unlock_time);

        echo json_capsule(array("response" => "success", "message" => "Messages list", "messages" => $messages, "unlocks" => $unlocks));
    }

    // Unlock Answer Request
    public function unlock_answer() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $answerid = $_REQ["answer_id"];

        $this->load->model("answer");
        $result = $this->answer->unlock_answer($userid, $answerid);

        if (IsNullOrEmptyString($result)) {
            echo json_capsule(array("response" => "success", "message" => "Answer is unlocked successfully"));
        } else {
            echo json_capsule(array("response" => "fail", "message" => $result));
        }
    }

    // Rate Answer Request
    public function rate_answer() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $answerid = $_REQ["answer_id"];
        $rate = $_REQ["rate"];

        if ($rate != -1 && $rate != 1) {
            echo json_capsule(array("response" => "fail", "message" => "Incorrect request paramemters"));
        }

        $this->load->model("answer");
        $this->answer->rate_answer($userid, $answerid, $rate);

        echo json_capsule(array("response" => "success", "message" => "Answer is rated successfully"));
    }

    // Report Answer Request
    public function report_answer() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $answerid = $_REQ["answer_id"];
        $text = $_REQ["text"];

        $this->load->model("report");
        $this->report->report_answer($userid, $answerid, $text);

        echo json_capsule(array("response" => "success", "message" => "Answer is reported successfully"));
    }

    // Get User Profile Request
    public function get_user_profile() {
        $_REQ = user_request();

        $user_id = $_REQ["user_id"];

        $this->load->model("user");

        $user = $this->user->get_user_profile($user_id);
        if ($user === false) {
            echo json_capsule(array("response" => "fail", "message" => "Incorrect request paramemters"));
        }
        $user->password = '';
        echo json_capsule(array("response" => "success", "message" => "User Profile", "user" => $user));
    }

    //Change Profile Request
    public function change_profile() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $language = $_REQ["language"];
        $country = $_REQ["country"];
        $description = $_REQ["description"];
        $paypal_id = $_REQ["paypal_id"];


        $this->load->model("user");
        $user = $this->user->is_existuserById($userid);
        if ($user === false) {
            echo json_capsule(array("response" => "fail", "message" => "Invalid Request Parameters."));
            return;
        }

        $this->load->model("upload");
        $photo = $this->upload->upload_file("photo");

        if ($photo === null) {
            echo json_capsule(array("response" => "fail", "message" => "Failed to upload photo"));
            return;
        }

        $this->user->update_user($userid, $language, $country, $description, $photo, $paypal_id);

        if (!IsNullOrEmptyString($user->image)) {
            $this->load->model("upload");
            $this->upload->delete_file($user->image);
        }

        echo json_capsule(array("response" => "success", "message" => "Update user successfully."));
    }

    //Change Password Request
    public function change_password() {
        $_REQ = user_request();

        $userid = $_REQ["user_id"];
        $old_password = $_REQ["old_password"];
        $new_password = $_REQ["new_password"];


        $this->load->model("user");
        $user = $this->user->is_existuserById($userid);
        if ($user === false) {
            echo json_capsule(array("response" => "fail", "message" => "Invalid Request Parameters."));
            return;
        }

        if ($user->password != $old_password) {
            echo json_capsule(array("response" => "fail", "message" => "Old password is not correct."));
            return;
        }

        $this->user->change_password($userid, $new_password);
        echo json_capsule(array("response" => "success", "message" => "Update user successfully."));
    }

    //Report Online Request
    public function report_online() {
        $_REQ = user_request();
        echo json_capsule(array("response" => "success", "message" => "Online status is reported successfully."));
    }

    //Save Payment Info
    public function save_payment() {
        $_REQ = user_request();
        $userid = $_REQ["user_id"];
        tolog($_REQ["proof"]);
        $proof = $_REQ["proof"];
        //tolog($_REQ["payment"]);
        $payment = $_REQ["payment"];
        $amount = $_REQ["amount"];

        $this->load->model("transaction");
        $this->transaction->purchase_from_device($userid, $proof, $payment, $amount);
        echo json_capsule(array("response" => "success", "message" => "Payment completed successfully."));
    }

    // Find Password Request
    public function find_password() {
        $_REQ = user_request();

        $email = $_REQ["email"];

        $this->load->model("user");
        $user = $this->user->is_existuserByEmail($email);

        if ($user === false) {
            echo json_capsule(array("response" => "fail", "message" => "Your email is not registered yet.\nPlease signup and use our service."));
            return;
        }

        $ip = "";
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $config['mailpath'] = "/usr/sbin/sendmail";
        $config['protocol'] = "sendmail";
        $config['smtp_host'] = "relay-hosting.secureserver.net";
        $config['smtp_user'] = "support@jesta4me.com";  //this address must not be a public email service provider like yahoo, gmail, hotmail... etc
        $config['smtp_pass'] = "jesta!@#$";
        $config['smtp_port'] = "25";
        $config['mailtype'] = "text";
        $config['validate'] = "TRUE";

        /* $config = Array(
          'protocol' => 'smtp',
          'smtp_host' => 'ssl://smtp.googlemail.com',
          'smtp_port' => 465,
          'smtp_user' => 'jestatester@gmail.com',
          'smtp_pass' => 'jesta_tester',
          'mailtype'  => 'text',
          'charset'   => 'iso-8859-1'
          ); */

        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");

        $this->email->from('support@jesta4me.com', 'JESTA');
        $this->email->to($email);

        $this->email->subject('JESTA | FIND PASSWORD');
        $message = "A request was made to send you your email and password for JESTA. Your details are as follows:\n\n";
        $message .= "Logon email : " . $user->email . "\n";
        $message .= "Password : " . $user->password . "\n\n";
        $message .= "This request came from IP address: " . $ip;

        $this->email->message($message);

        $this->email->send();
        echo json_capsule(array("response" => "success", "message" => "Sent password successfully."));
    }

}

?>