<?php
const TELEGRAM_TOKEN = "6240418192:AAF_DSWhTOXqUx4c_Wk5MmLqpcOCKf-UYRA";

require __DIR__ . "/../rb-mysql.php";
require __DIR__ . "/../template.php";
require __DIR__ . "/../Bot_V2.php";

$mysql_ip = "localhost";
$mysql_dbname = "aktiviev_katyasa";
$mysql_dbuser = "aktiviev_katyasa";
$mysql_password = "qWbniV&4";

R::setup("mysql:host=$mysql_ip;dbname=$mysql_dbname",
    $mysql_dbuser, $mysql_password);

$timestamp = time();

$usercrons = R::getAll("SELECT uc.id, uc.message_type, u.chat_id, uc.last_message_id FROM userscron uc
    LEFT JOIN users u ON uc.user_id = u.id
    WHERE uc.status != 1 AND uc.timestamp_start + uc.wait_time <= '$timestamp'");
file_put_contents(__DIR__ . "/hopper.txt", json_encode($usercrons));

$bot = new Bot_V2();

if (date('Y-m-d') == date('Y-m-01')){
    $occurred = file_get_contents(__DIR__ . "/bonuses_sent.txt");
    if (!$occurred){
        $invitors = R::find("users", "role >= 1");
        $users_with_status = array(0,0,0,0,0,0,0);
        foreach ($invitors as $invitor){
            switch ($invitor["role"]){
                case 1:
                    $users_with_status[0]+=1;
                    break;
                case 2:
                    $users_with_status[1]+=1;
                    break;
                case 3:
                    $users_with_status[2]+=1;
                    break;
                case 4:
                    $users_with_status[3]+=1;
                    break;
                case 5:
                    $users_with_status[4]+=1;
                    break;
                case 6:
                    $users_with_status[5]+=1;
                    break;
                case 7:
                    $users_with_status[6]+=1;
                    break;
            }
        }
        // Получить 50% от суммы для распределения и поделить ее на количество invitors
        $all_statuses = R::findAll('refbuffer','ORDER BY id ASC');
        $current_status_buffer = array();
        foreach ($all_statuses as $status){
            if ($users_with_status[$status["id"]-1] == 0){
                continue;
            }
            $status["buffer"] *= 0.5;
            array_push($current_status_buffer, $status["buffer"]);
            R::store($status);
        }
        foreach ($invitors as $invitor){
            $bonus = 0;
            switch ($invitor["role"]){
                case 1:
                    if ($users_with_status[0] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[0]/$users_with_status[0]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
                case 2:
                    if ($users_with_status[1] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[1]/$users_with_status[1]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
                case 3:
                    if ($users_with_status[2] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[2]/$users_with_status[2]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
                case 4:
                    if ($users_with_status[3] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[3]/$users_with_status[3]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
                case 5:
                    if ($users_with_status[4] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[4]/$users_with_status[4]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
                case 6:
                    if ($users_with_status[5] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[5]/$users_with_status[5]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
                case 7:
                    if ($users_with_status[6] <= 0){
                        break;
                    }
                    $bonus = intval($current_status_buffer[6]/$users_with_status[6]);
                    $invitor["balance"] += $bonus;
                    R::store($invitor);
                    break;
            }

            $template = new Template("send_bonuses", [
                new TemplateData(":bonuses", $bonus)
            ]);
            $template = $template->Load();
            $bot->sendMessage($invitor["chat_id"], $template->text);

        }
        file_put_contents(__DIR__ . "/bonuses_sent.txt", 1);
    }
} else{
    file_put_contents(__DIR__ . "/bonuses_sent.txt", 0);
}

foreach ($usercrons as $usercron) {
    /*$content = file_get_contents(__DIR__ . "/../modules/templates/message_3.txt");

    $buttons[] = [
        $bot->buildInlineKeyboardButton("Да", "/message_3 1"), // сообщение 3, ответ Да
        $bot->buildInlineKeyBoardButton("Нет", "/message_3 2"), // сообщение 3, ответ Нет
        $bot->buildInlineKeyBoardButton("Не успела", "/message_3 3"), // сообщение 3, ответ Не успела
    ];*/

    switch ($usercron["message_type"]) {
        case 3:
            $template = new Template("message_3");
            $template = $template->Load();
            $template->LoadButtons();


            $userscron = R::findOne("userscron", "id = {$usercron["id"]}");
            if (time() >= $userscron["time_start"] + $userscron["wait_time"]) {
                $bot->DelMessageText($usercron["chat_id"], $userscron["last_message_id"]);

                /*switch ((int)$userscron["wait_time"]) {
                    case 7200:
                    case 86400:
                        $userscron["wait_time"] = 43200;
                        break;
                    case 43200:
                        $userscron["wait_time"] = 86400;
                        break;
                }*/

                switch ((int)$userscron["wait_time"]) {
                    case 7200:
                        $userscron["wait_time"] = 43200;
                        break;
                    case 43200:
                        $userscron["wait_time"] = 86400;
                        break;
                    case 86400:
                        break;
                }

                $userscron["timestamp_start"] = time();
                $userscron["status"] = 0;
                $userscron["message_type"] = 3;

                $response = $bot->sendMessage($usercron["chat_id"], $template->text, $template->buttons);
                $userscron["last_message_id"] = $response['result']['message_id'];

                if ((int)$userscron["wait_time"] == 86400) {
                    R::store($userscron);
                } else {
                    R::trash("userscron", $usercron["id"]);
                }
            }
            break;
        case 21:
            $template = new Template("message_21");
            $template = $template->Load();
            $template->LoadButtons();

            if (time() >= $usercron["time_start"] + $usercron["wait_time"]) {
                $user = R::findOne("users", "id = {$usercron["user_id"]}");

                $response = $bot->sendMessage($usercron["chat_id"], $template->text, $template->buttons);
                R::trash("userscron", $usercron["id"]);
            }
            break;
    }
}