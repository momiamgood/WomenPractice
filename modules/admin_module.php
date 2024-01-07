<?php
use Nutgram\Manager;

if ($chat_id == ADMIN_CHAT_ID) {

    if ($this->data['message']['voice']){
        $this->DelMessageText(ADMIN_CHAT_ID, $message_id);


        $voice = $this->data['message']['voice'];
        $file_id = $voice['file_id'];
        file_put_contents("modules/templates/admin/file_id.txt", $file_id);

        $buttons = [
            [
                $this->buildInlineKeyBoardButton("Отправить", "/send_voice"),
            ],
        ];

        $this->sendVoice(ADMIN_CHAT_ID, $file_id, $buttons);
        return;
    }
    //получить сенд файл
    $send_content = file_get_contents("modules/templates/admin/sendMsg.txt");
    if (strlen($send_content) > 0) {
        $send_action = explode(" ",$send_content);
        if (isset($send_action[1])) {
            if($send_action[0] == "send_one"){
                $this->DelMessageText(ADMIN_CHAT_ID, $send_action[2]);
                $user = R::findOne("users", "id = '{$send_action[1]}'");
                file_put_contents("modules/templates/admin/sendMsgTempl.txt",$text);
                $template = new Template("admin/sendMsgTempl");
                $template = $template->Load();
                $template->LoadButtons();
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
                $this->sendMessage(ADMIN_CHAT_ID, "Сообщение отправлено");
                file_put_contents("modules/templates/admin/sendMsg.txt", "");
                return;
            } elseif ($send_action[0] == "send_all"){
                $this->DelMessageText(ADMIN_CHAT_ID, $send_action[1]);
                $users = R::findAll("users");
                $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
                file_put_contents("modules/templates/admin/sendMsgTemplAll.txt",$text);
                $template = new Template("admin/sendMsgTemplAll");
                $template = $template->Load();
                $template->LoadButtons();
                foreach ($users as $user) {
                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                }
                $this->sendMessage(ADMIN_CHAT_ID, "Сообщение отправлено всем пользоваетлям");
                file_put_contents("modules/templates/admin/sendMsg.txt", "");
                return;
          } elseif ($send_action[0] == "other") {
                #nutgram
                if(!$command[0]) {
                    $manager = new Manager();
                    $manager->start();
                    $manager->end();
                    return;
                }
            }
        }
    }

    switch ($command[0]) {
        case "/reply":
            file_put_contents("modules/templates/admin/user_reply.txt", "$command[1] $message_id");
            file_put_contents("modules/templates/admin/file_id.txt", $file_id);
            $this->sendMessage(ADMIN_CHAT_ID, "Для ответа запишите голосове сообщение");
            return;

        case "/reply_voice":
            $this->sendMessage(ADMIN_CHAT_ID, "Для ответа запишите голосове сообщение");
            return;

        case "/send_voice":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $file_id = file_get_contents("modules/templates/admin/file_id.txt");
            $reply_to = file_get_contents("modules/templates/admin/user_reply.txt");
            $full = explode(" ", $reply_to);
            $this->sendVoice($full[0], $file_id);
            $this->sendMessage(ADMIN_CHAT_ID, "Сообщение отправлено");
            $this->DelMessageText(ADMIN_CHAT_ID, $full[1]);
            return;

        case "/1":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
            $statuses = R::findAll( 'refbuffer' , ' ORDER BY id ASC ' );
            $stats = array();
            foreach($statuses as $status){
                array_push($stats, $status["buffer"]);
            }
            $template = new Template("admin/status_stat", [
                new TemplateData(":red", $stats[0]),
                new TemplateData(":orange", $stats[1]),
                new TemplateData(":yellow",$stats[2]),
                new TemplateData(":green", $stats[3]),
                new TemplateData(":blue", $stats[4]),
                new TemplateData(":darkblue", $stats[5]),
                new TemplateData(":purple", $stats[6])
            ]);
            $template = $template->Load();
            $this->sendMessage(ADMIN_CHAT_ID, $template->text);
            return;
        case "/aphrodite_morning_process_purchase_success":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $orderId = (int)$command[1];
            //$username = $command[3];

            $aphroditeMorning = R::findOne("aphroditemorning", "id = $orderId");
            if ($aphroditeMorning) {
                $aphroditeMorning["status"] = 2;

                R::store($aphroditeMorning);

                $aphroditeMorningUser = R::findOne("users", "id = {$aphroditeMorning["user_id"]}");
                $aphroditeMorningUser["action"] = "";
                R::store($aphroditeMorningUser);

                $this->DelMessageText($aphroditeMorningUser["chat_id"], $command[2]);

                $templateUser = new Template("aphrodite_morning/order_payment_success");
                $templateUser = $templateUser->Load();

                $this->sendMessage($aphroditeMorningUser["chat_id"], $templateUser->text);

                //Создать файл с номерам заказа
                file_put_contents("modules/templates/admin/cur_order.txt", "aphr $orderId");

                $templateAdmin = new Template("admin/aphrodite_morning/process_purchase_success", [
                    new TemplateData(":aphroditeMorningId", $aphroditeMorning["id"]),
                    new TemplateData(":username", $aphroditeMorningUser["username"]),
                    new TemplateData(":user_id", $aphroditeMorning["user_id"])
                    //new TemplateData(":username", $username)

                ]);
                $templateAdmin = $templateAdmin->Load();
                $this->sendPhoto(ADMIN_CHAT_ID,"https://katyasatorinebot.online/bot/{$aphroditeMorning["check_photo"]}", $templateAdmin->text);
                //$response = $this->sendMessage(ADMIN_CHAT_ID,$templateAdmin->text);
            }

            return;
        case "/aphrodite_morning_process_purchase_deny":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $orderId = (int)$command[1];
            //$username = $command[3];

            $aphroditeMorning = R::findOne("aphroditemorning", "id = $orderId");
            if ($aphroditeMorning) {
                $aphroditeMorning["status"] = 2;

                R::store($aphroditeMorning);

                $aphroditeMorningUser = R::findOne("users", "id = {$aphroditeMorning["user_id"]}");

                $templateUser = new Template("order_payment_deny_utro");
                $templateUser = $templateUser->Load();

                //$this->DelMessageText($aphroditeMorningUser["chat_id"], $command[2]);

                $this->sendMessage($aphroditeMorningUser["chat_id"], $templateUser->text);

                $templateAdmin = new Template("admin/aphrodite_morning/process_purchase_deny", [
                    new TemplateData(":aphroditeMorningId", $aphroditeMorning["id"]),
                    new TemplateData(":username", $aphroditeMorningUser["username"]),
                    new TemplateData(":user_id", $aphroditeMorning["user_id"])
                    //new TemplateData(":username", $username)
                ]);
                $templateAdmin = $templateAdmin->Load();

                $this->sendMessage(ADMIN_CHAT_ID, $templateAdmin->text);
            }

            return;

        case "/daoss_magick_process_purchase_success":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $orderId = (int)$command[1];
            //$username = $command[3];

            $daossmagick = R::findOne("daossmagick", "id = $orderId");
            if ($daossmagick) {
                $daossmagick["status"] = 2;

                R::store($daossmagick);

                $daossmagickUser = R::findOne("users", "id = {$daossmagick["user_id"]}");
                $daossmagickUser["action"] = "";
                R::store($daossmagickUser);

                $this->DelMessageText($daossmagickUser["chat_id"], $command[2]);

                $templateUser = new Template("daoss_magick/order_payment_success");
                $templateUser = $templateUser->Load();

                $this->sendMessage($daossmagickUser["chat_id"], $templateUser->text);

                //Создать файл с номерам заказа
                file_put_contents("modules/templates/admin/cur_order.txt", "daoss $orderId");

                $templateAdmin = new Template("admin/daoss_magick/process_purchase_success", [
                    new TemplateData(":daossMagickId", $daossmagick["id"]),
                    new TemplateData(":username", $daossmagickUser["username"]),
                    new TemplateData(":user_id", $daossmagick["user_id"]),
                    //new TemplateData(":username", $username)

                ]);
                $templateAdmin = $templateAdmin->Load();
                $this->sendPhoto(ADMIN_CHAT_ID,"https://katyasatorinebot.online/bot/{$daossmagick["check_photo"]}", $templateAdmin->text);
                //$response = $this->sendMessage(ADMIN_CHAT_ID,$templateAdmin->text);
            }

            return;
        case "/daoss_magick_process_purchase_deny":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $orderId = (int)$command[1];
            //$username = $command[3];

            $daossmagick = R::findOne("daossmagick", "id = $orderId");
            if ($daossmagick) {
                $daossmagick["status"] = 2;

                R::store($daossmagick);

                $daossmagickUser = R::findOne("users", "id = {$daossmagick["user_id"]}");

                $templateUser = new Template("order_payment_deny_daoss");
                $templateUser = $templateUser->Load();

                //$this->DelMessageText($daossmagickUser["chat_id"], $command[2]);

                $this->sendMessage($daossmagickUser["chat_id"], $templateUser->text);

                $templateAdmin = new Template("admin/daoss_magick/process_purchase_deny", [
                    new TemplateData(":daossMagickId", $daossmagick["id"]),
                    new TemplateData(":username", $daossmagickUser["username"]),
                    new TemplateData(":user_id", $daossmagick["user_id"]),
                    //new TemplateData(":username", $username)
                ]);
                $templateAdmin = $templateAdmin->Load();

                $this->sendMessage(ADMIN_CHAT_ID, $templateAdmin->text);
            }

            return;
        case "/process_purchase_success":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $orderId = (int)$command[1];
            $username = $command[2];

            $order = R::findOne("orders", "id = $orderId");
            if ($order) {
                $order["status"] = 2;

                R::store($order);

                //тот, кто оплатил
                $user = R::findOne("users", "id = {$order["user_id"]}");
                $user["action"] = "";
                R::store($user);

                if ($order["marathon_stage"] > 0){
                    switch ($order["marathon_stage"]) {
                    case 1:
                        $templateUser = new Template("order_payment_success", [
                            new TemplateData(":stage", "/message_18 1"),
                        ]);
                        break;
                    case 2:
                        $templateUser = new Template("order_payment_success", [
                            new TemplateData(":stage", "/message_19 1"),
                        ]);
                        break;
                    case 3:
                        $templateUser = new Template("order_payment_success", [
                            new TemplateData(":stage", "/message_20 1"),
                        ]);
                        break;
                    }

                    $templateUser = $templateUser->Load();
                    $templateUser->LoadButtons();
                    $this->sendMessage($user["chat_id"], $templateUser->text, $templateUser->buttons);
                }


                //Создать файл с номерам заказа
                file_put_contents("modules/templates/admin/cur_order.txt", "order $orderId");

                $templateAdmin = new Template("admin/process_purchase_success", [
                    new TemplateData(":orderId", $order["id"]),
                    new TemplateData(":user_id", $order["user_id"]),
                    new TemplateData(":username", $username)
                ]);

                $templateAdmin = $templateAdmin->Load();
                $this->sendPhoto(ADMIN_CHAT_ID, "https://katyasatorinebot.online/bot/{$order["check_photo"]}", $templateAdmin->text);
                //$response = $this->sendMessage(ADMIN_CHAT_ID, $templateAdmin->text);

            }

            return;
        case "/process_purchase_deny":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);

            $orderId = (int)$command[1];
            $username = $command[2];

            $order = R::findOne("orders", "id = $orderId");
            if ($order) {
                $order["status"] = 2;

                R::store($order);

                $user = R::findOne("users", "id = {$order["user_id"]}");

                $templateUser = new Template("order_payment_deny_extaz");
                $templateUser = $templateUser->Load();

                $this->sendMessage($user["chat_id"], $templateUser->text);

                $templateAdmin = new Template("admin/process_purchase_deny", [
                    new TemplateData(":orderId", $order["id"]),
                    new TemplateData(":user_id", $order["user_id"]),
                    new TemplateData(":username", $username)
                ]);
                $templateAdmin = $templateAdmin->Load();

                $this->sendMessage(ADMIN_CHAT_ID, $templateAdmin->text);
            }

            return;
        case "/send_one":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
            $response = $this->sendMessage(ADMIN_CHAT_ID, "Введите текст для отправки пользователю");
            $send_id = $command[1];
            file_put_contents("modules/templates/admin/sendMsg.txt", "send_one {$send_id} {$response["result"]["message_id"]}");
            return;

        case "/send_all":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
            $response = $this->sendMessage(ADMIN_CHAT_ID, "Введите текст для рассылки всем пользователям");
            file_put_contents("modules/templates/admin/sendMsg.txt", "send_all {$response["result"]["message_id"]}");
            return;

        case "/send_all_photo":
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
            $response = $this->sendPhotoAdmin(ADMIN_CHAT_ID, $photo_file_id);
            file_put_contents("modules/templates/admin/all_file_id", "send_all_photo {$response["result"]["message_id"]}");
            return;

        case ((bool)preg_match("~^[0-9]+$~", $command[0])):
            $this->DelMessageText(ADMIN_CHAT_ID, $message_id);
            $price = $command[0];

            $fileOrder = file_get_contents("modules/templates/admin/cur_order.txt");

            $orderParams = explode(" ", $fileOrder);

            //$orderId = file_get_contents("modules/templates/admin/cur_order.txt");
            //$order = R::findOne("orders", "id = $orderId");

            $order = "";

            switch ($orderParams[0]){
                case "aphr":
                    $order = R::findOne("aphroditemorning", "id = $orderParams[1]");
                    break;
                case "order":
                    $order = R::findOne("orders", "id = $orderParams[1]");
                    break;
                case "daoss":
                    $order = R::findOne("daossmagick", "id = $orderParams[1]");
                    break;
            }

            $user = R::findOne("users", "id = {$order["user_id"]}");

            // Если есть файл со значением, то берем из него и обнавляем данные в базе
            $handle = fopen("modules/templates/admin/status_precent.txt", "r");
            if ($handle) {
                $i = 1;
                while (($line = fgets($handle)) !== false) {
                    $status = R::findOne('refbuffer', "id = {$i}");
                    $status["base_precent"] = $line;
                    R::store($status);
                    $i++;
                }

                fclose($handle);
            }

            // Добавить во все буферы с цены
            for ($i = 1; $i <= 7; $i++){
                $status_buffer = R::findOne('refbuffer', "id = {$i}");
                $status_buffer['buffer'] += intval(($status_buffer['base_precent']/100) * $price);
                R::store($status_buffer);
            }

            $user["spent"] += $price;
            R::store($user);

            //Если есть реферал
            if ($user["ref"]){

                $spent_limit = file_get_contents("modules/templates/admin/max_spending_limit.txt");

                if(intval($user["spent"]) >= $spent_limit && $user["role"] < 1){
                    //Получаем реферала,у которого, вероятно, нужно поменять статус
                    $parent_user = R::findOne("users", "chat_id = {$user["ref"]}");
                    //file_put_contents("REFTEST.txt","test");
                    //Сразу проверяем его статус, если уже 1, то скип
                    if($parent_user["role"] < 1){
                        //Получаем всех приглашенный рефералом
                        $all_parent_childs = R::find("users", "ref = {$user["ref"]}");
                        // ищем, есть ли ребята, у которых тоже сумма выше нужной
                        $counter = 0;
                        foreach ($all_parent_childs as $usr){
                            if ($usr["spent"] >= 2000){
                                $counter += 1;
                            }
                            if ($counter == 2){
                                //$parent_user["role"] = 1;
                                //R::store($parent_user);
                                $this->UpdateUserStatus($parent_user["chat_id"]);
                                break;
                            }
                        }

                    }

                }


                // считаю бонусы для реферала
                $percent = 10;
                $price_balance = ($price * $percent) / 100;
                $formatted_price_balance = number_format($price_balance, 0, "", ".");

                // добавляю бонусы к балансу реферала
                $ref = R::findOne('referal', "chat_id = {$user["chat_id"]}");
                $ref_user = R::findOne('users',"chat_id = {$ref["ref_id_user"]}");
                $ref_user["balance"] += $price_balance;
                //file_put_contents("testadmin4.txt", $ref_user);

                R::store($ref_user);

                $formatted_ref_user_balance = number_format($ref_user["balance"], 0, "", ".");

                // формирую сообщение рефералу
                /*$content_referal = "Та-дам 🍾\n";
                $content_referal .= "Ставка сыграла!\n\n";

                $content_referal .= "🏃Ваш реферал добавил в вашу копилку <b>$formatted_price_balance</b> бонусных рупий.\n\n";

                $content_referal .= "💰Теперь на вашем балансе <b>$formatted_ref_user_balance</b> бонусных рупий.";*/
                $templateUser = new Template("referal_notification", [
                    new TemplateData(":formattedPriceBalance", $formatted_price_balance),
                    new TemplateData(":formattedRefUserBalance", $formatted_ref_user_balance),
                    new TemplateData(":chatId", $ref_user["chat_id"]),
                ]);
                $templateUser = $templateUser->Load();
                $templateUser->LoadButtons();

                // отправляю сообщение рефералу
                $this->sendMessage($ref_user["chat_id"], $templateUser->text, $templateUser->buttons);

                // отправляю подтеврждение админу
                $response = $this->sendMessage(ADMIN_CHAT_ID, "Бонусы начислены");
            }else {
                $response = $this->sendMessage(ADMIN_CHAT_ID, "У пользователя нет реферала");
            }


            return;
        default:
            // #nutgram
            $manager = new Manager();
            $manager->start();
            $manager->end();
            file_put_contents("modules/templates/admin/sendMsg.txt", 'other 123');
            return;
    }
}