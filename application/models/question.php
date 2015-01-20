<?php

// model for user manage
class question extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_question_by_user_and_id($userid, $queid) {
        $sql = "SELECT qid FROM " . TABLE_QUESTION . " WHERE qid='{$queid}' AND userid='{$userid}'";
        return get_sql_value($sql);
    }

    function get_questions_by_user($userid) {
        $this->load->model("user");
        $questions = array();

        $sql = "SELECT Q.qid, Q.userid, '' as username, '' as image, Q.text, TIMESTAMPDIFF(MINUTE, Q.time, now()) as time, Q.paid, Q.lock, Q.audios, " .
                "Q.photos, Q.videos, Q.lat, Q.lon, Q.contact, Q.public_access," .
                " (SELECT COUNT(*) FROM " . TABLE_QUESTION_READ . " WHERE questionId=Q.qid AND userid ='{$userid}') as 'read', " .
                " (SELECT COUNT(DISTINCT sender) FROM " . TABLE_ANSWER . " WHERE questionId=Q.qid AND sender <> '{$userid}' ) as total_answers, " .
                " (SELECT COUNT(DISTINCT sender) FROM " . TABLE_ANSWER . " WHERE questionId=Q.qid AND sender <> '{$userid}' AND `read` = 0 ) " .
                " as new_answers , '' as `answer`, 0 as `answer_type` FROM " . TABLE_QUESTION .
                " Q WHERE Q.userid='{$userid}' AND " .
                "(SELECT COUNT(*) FROM " . TABLE_QUESTION_DELETE . " WHERE questionid = Q.qid AND userid = Q.userid AND status > 0) = 0
				ORDER BY Q.qid DESC";

        $result = get_sql_result($sql);
        foreach ($result as $key => $question) {
            $question->time = convertToTime($question->time);

            $user = $this->user->is_existuserById($question->userid);

            $question->username = ($user === false) ? "Guest" : $user->username;
            $question->image = ($user === false) ? "" : $user->image;

            $questions[] = $question;
        }

        $sql = "SELECT Q.qid, Q.userid,'' as username, '' as image, Q.text, TIMESTAMPDIFF(MINUTE, Q.time, now()) as time, Q.paid, Q.lock, Q.audios, " .
                "Q.photos, Q.videos, Q.lat, Q.lon, Q.contact," .
                " (SELECT COUNT(*) FROM " . TABLE_QUESTION_READ . " WHERE questionId=Q.qid AND userid ='{$userid}') as 'read', " .
                " 0 as total_answers, " .
                "(SELECT COUNT(*) FROM " . TABLE_ANSWER . " WHERE questionId=Q.qid AND receiver= '{$userid}' AND `read` = 0 )  as new_answers, " .
                "'' as `answer`, 0 as `answer_type` " .
                " FROM " . TABLE_QUESTION . " Q WHERE Q.userid<>'{$userid}' AND " .
                "(SELECT COUNT(*) FROM " . TABLE_QUESTION_DELETE . " WHERE questionid = Q.qid AND (userid = Q.userid or userid='{$userid}')" .
                " AND status > 0) = 0 AND ((SELECT COUNT(*) FROM " . TABLE_QUESTION_ALLOWED .
                " WHERE questionid=Q.qid AND userid='{$userid}') > 0 OR Q.catid='' OR Q.catid=',,') ORDER BY Q.qid DESC";

        $result = get_sql_result($sql);
        $unread_count = 0;
        foreach ($result as $key => $question) {
            if ($question->read == 0) {
                $unread_count++;
                if ($unread_count > 5)
                    continue;
            }
            $question->time = convertToTime($question->time);

            $sql = "SELECT content, type, cost FROM " . TABLE_ANSWER . " WHERE questionid='{$question->qid}' AND sender='{$userid}' ORDER BY answerid DESC LIMIT 1";
            $answer = get_sql_value($sql);
            if ($answer != false) {
                $question->answer = $answer->content;
                $question->answer_type = $answer->type;
                if ($answer->cost > 0) {
                    $question->answer = "<$$$>" . $answer->content;
                }
            }

            $user = $this->user->is_existuserById($question->userid);
            $question->username = ($user === false) ? "Guest" : $user->username;
            $question->image = ($user === false) ? "" : $user->image;

            $questions[] = $question;
        }
        return $questions;
    }

    function add_question($userid, $catnames, $text, $audios, $photos, $videos, $lat, $lon, $contact, $public_access) {
        $this->load->model("category");
        $catids = $this->category->get_category_ids($catnames);

        $sql = "INSERT INTO " . TABLE_QUESTION . " (userid, text, audios, photos, videos, catid, lat, lon, contact, public_access) VALUES " .
                "('{$userid}', '{$text}', '{$audios}', '{$photos}', '{$videos}', '{$catids}', '{$lat}', '{$lon}', '{$contact}', '{$public_access}')";
        get_sql_query($sql);

        $questionid = mysql_insert_id();

        $cat_array = explode(',', $catids);
        $where_arg = array();

        foreach ($cat_array as $cat) {
            $where_arg[] = "saved_cat LIKE '%{$cat}%'";
        }

        $where = implode(" OR ", $where_arg);

        if (!IsNullOrEmptyString($where)) {
            $where = "(" . $where . ") AND ";
        }

        $sql = "SELECT userid, device, token, (SELECT SUM(rating) FROM " . TABLE_ANSWER . " WHERE sender= userid) as rating FROM " .
                TABLE_USERS . " WHERE {$where} userid <> '{$userid}' ORDER BY rating DESC LIMIT 100";

        $result = get_sql_result($sql);
        $android_array = array();
        $iphone_array = array();
        $data = array();

        foreach ($result as $key => $user) {
            $sql = "INSERT INTO " . TABLE_QUESTION_ALLOWED . " (questionid, userid) VALUES ( '{$questionid}',  '{$user->userid}')";
            get_sql_query($sql);
            if (!IsNullOrEmptyString($user->token)) {
                if ($user->device == "Android") {
                    $android_array[] = $user->token;
                } else {
                    $iphone_array[] = $user->token;
                }
            }
        }

        $sql = "SELECT username FROM " . TABLE_USERS . " WHERE userid='{$userid}' LIMIT 1";
        $result = get_sql_value($sql);

        $message = ($result === false ? "Guest" : $result->username) . " : " . $text;
        $type = TYPE_QUESTION;

        $this->load->model("user");
        $this->user->send_gcm($android_array, $message, $type);
        $this->user->send_apns($iphone_array, $message, $type);
    }

    function delete_question($userid, $queid, $state) {
        $sql = "SELECT userid FROM " . TABLE_QUESTION_DELETE . " WHERE userid='{$userid}' AND questionid='{$queid}' LIMIT 1";
        $result = get_sql_value($sql);

        if ($result === false) {
            $sql = "INSERT INTO " . TABLE_QUESTION_DELETE . " (userid, questionid, status) VALUES ('{$userid}','{$queid}','{$state}')";
        } else {
            $sql = "UPDATE " . TABLE_QUESTION_DELETE . " SET status='{$state}' WHERE userid='{$userid}' AND questionid='{$queid}'";
        }
        get_sql_query($sql);

        $sql = "SELECT userid FROM " . TABLE_QUESTION . " WHERE qid='{$queid}' LIMIT 1";
        $result = get_sql_value($sql);

        if ($result->userid != $userid)
            return;

        $sql = "SELECT userid, device, token FROM " . TABLE_USERS . " WHERE userid IN (SELECT userid FROM " . TABLE_QUESTION_ALLOWED .
                " WHERE questionid='{$queid}')";

        $result = get_sql_result($sql);
        $android_array = array();
        $iphone_array = array();
        $data = array();

        foreach ($result as $key => $user) {
            if ($user->device == "Android") {
                $android_array[] = $user->token;
            } else {
                $iphone_array[] = $user->token;
            }
        }

        $message = "";
        $type = TYPE_QUESTION;

        $this->load->model("user");
        $this->user->send_gcm($android_array, $message, $type);
        $this->user->send_apns($iphone_array, $message, $type);
    }

    function read_question($userid, $que_id) {
        $sql = "SELECT questionid FROM " . TABLE_QUESTION_READ . " WHERE questionid='{$que_id}' AND userid='{$userid}' LIMIT 1";
        $result = get_sql_value($sql);

        if ($result === false) {
            $sql = "INSERT INTO " . TABLE_QUESTION_READ . " (questionid, userid) VALUES ( '{$que_id}', '{$userid}') ";
            get_sql_query($sql);
        }
    }

}

?>