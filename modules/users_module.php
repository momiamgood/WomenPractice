<?php
if ($chat_id != ADMIN_CHAT_ID) {
    $action = explode(" ", $user["action"]);
    if ($action[0] == "health_check" && isset($action[1])) {
        $user["action"] = "";
        R::store($user);

        $this->DelMessageText($user["chat_id"], $message_id);
        $this->DelMessageText($user["chat_id"], $action[2]);

        $status = 0;

        if ($action[1]) {
            $buttonDeepLink = "";
            switch ($action[1]) {
                case 0:
                    $status = 0;
                    $buttonDeepLink = "/message_18";
                    break;
                case 1:
                    $status = 1;
                    $buttonDeepLink = "/message_18 1";
                    break;
                case 2:
                    $status = 2;
                    $buttonDeepLink = "/message_19 1";
                    break;
                case 3:
                    $status = 3;
                    $buttonDeepLink = "/message_20 1";
                    break;
                case 4:
                    $status = 4;
            }

            if ($status == 4) {
                $templateUser = new Template("message_21");
                $templateUser = $templateUser->Load();
                $templateUser->LoadButtons();

                $this->sendMessage($user["chat_id"], $templateUser->text, $templateUser->buttons);
            } else {
                $templateUser = new Template("health_check_success", [
                    new TemplateData(":buttonDeepLink", $buttonDeepLink)
                ]);
                $templateUser = $templateUser->Load();
                $templateUser->LoadButtons();

                $this->sendMessage($user["chat_id"], $templateUser->text, $templateUser->buttons);
            }

        }
        //file_put_contents("UsersStatus.txt",$status);
        $task = "";
        switch ($status) {
            case 1:
                $task = "И вот ты здесь!✨\nЧай для поднятия сексуальной энергии.\nТак же ты получаешь тарелку пирожкоф, которые активируют все самые важные железы и органы, в твоем теле: ☕\nЯичники, почки, надпочечники и пищеварение💆‍♀";
                break;
            case 2:
                $task = "Добро пожаловать, вторая дверь открыта🚪\nТы пришла к Красной королеве Регине👩\nКролик очень хочет, чтобы ты с ней познакомилась🙏\nОна даст тебе тайную практику для поднятия сексуальной энергии в сердечный центр, очищение легкихи и активации иммунитета.\nСкорее бери этот пирожок, это будет невероятный вкус🍰";
                break;
            case 3:
                $task = "Поздравляю🎊\nну вот и последняя дверь за ней тебя ждет Белая королева Светлана👩‍🦳\nОна готова угостить тебя своими пирожными с волшебным чаем кролика  💫!\nЗнаешь, к чему это приведет?!😃\nТы узнаешь, как через высшие гормональные центры, соединиться со своей внутренней вселенной✨\nКролик жаждет угостить тебе этими пирожными  🍽";
                break;
            case 4:
                $task = "Да-да, милая! Это еще не последнее чаепитие. Кролик  🐰 совсем не хочет с тобой прощаться, поэтому он припрятал для тебя еще один вкусный подарочек! Стража о нем ничего не знает — это сюрприз 💚";
                break;
        }
        $templateAdmin = new Template("admin/health_check", [
            new TemplateData(":status", $status),
            new TemplateData(":task", $task),
            new TemplateData(":username", $user["username"]),
            new TemplateData(":userText", $text),
            new TemplateData(":user_id", $user["id"]),
            new TemplateData(":userChatId", $user["chat_id"])
        ]);
        $templateAdmin = $templateAdmin->Load();
        $templateAdmin->LoadButtons();

        $this->sendMessage(ADMIN_CHAT_ID, $templateAdmin->text, $templateAdmin->buttons);
        if ($status == 4) {
            $usercron = R::dispense("userscron");
            $userscron["user_id"] = $user["id"];
            $userscron["timestamp_start"] = time();
            $userscron["wait_time"] = 43200;
            $userscron["message_type"] = 21;
            R::store($userscron);
        }
        return;
    }
    if ($command[0]) {
        switch ($command[0]) {
            case "/start": // стартовое сообщение или же сообщение 2
                if ($command[1]) {
                    if (preg_match("~^join[\d]+|ref[\d]+$~", $command[1], $matches)) {
                        $reference = preg_replace('/[^a-z]/', '', $matches[0]);

                        //file_put_contents("test1command.txt", $reference);
                        if ($reference == "ref") {
                            //file_put_contents("test2z.txt", "Zashel");

                            $un_text = substr($matches[0], 3);
                            $id_ref = $un_text;
                            //file_put_contents("test2.txt", $id_ref + "\n" + $chat_id);
                            if ($id_ref == $chat_id) {

                            } else {
                                # Проверяем регистрировавывались уже
                                $get_user_ref = R::findOne('referal', 'chat_id = :chat_id AND ref_id_user = :ref_id_user', [':chat_id' => $chat_id, ':ref_id_user' => $id_ref]);
                                if (!$get_user_ref) {


                                    $r_user = R::findOne('users', 'chat_id = :chat_id', [':chat_id' => $chat_id]);

                                    //file_put_contents("testbfrImin.txt", $r_user);
                                    # Если мы есть в базе, то прекращаем действия
                                    if ($r_user["ref"] == 0) {

                                    } else {
                                        //file_put_contents("testbfrImin12412.txt", $id_ref);
                                        $info_user = R::findOne('users', 'chat_id = :chat_id', [':chat_id' => $id_ref]);
                                        $info_user_nik = $info_user->username;
                                        # Если есть в базе кто пригласил, если нет, то прекращаем действия

                                        if (!$info_user) {
                                            //file_put_contents("testneinfo.txt", $r_user["ref"]);
                                        } else {
                                            //file_put_contents("testcrtTabkle.txt", $r_user);

                                            //реферальная ссылка
                                            $save = R::dispense('referal');
                                            $save->chat_id = $chat_id; // ид кто зарегистрировался
                                            $save->ref_id_user = $id_ref; // ид кто пригласил
                                            $save->nik = $info_user_nik; // ид кто пригласил
                                            $save->status = 0; // ид кто пригласил
                                            // Сохраняем объект
                                            R::store($save);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                //$this->delMessageText($user["chat_id"], $message_id);

                $user = R::findOne("users", "chat_id = $chat_id");
                if ($user) $temp_name = "message_2";
                else $temp_name = "start_message_not_registered";

                if ($user["chat_id"] != $chat_id) {
                    $user = R::dispense("users");
                    $user["chat_id"] = $chat_id;
                    $user["name"] = $first_name;
                    if ($username) {
                        //file_put_contents("testUsername.txt", "{$username}\n{$first_name}");
                        $user["username"] = $username;
                        $user["anonymous"] = false;
                    } else {
                        //file_put_contents("testNoUsername.txt", "{$username}\n{$first_name}");
                        //Берем имя, так как username у пользователя может и не быть
                        $user["username"] = "Аноним";
                        $user["anonymous"] = true;
                    }
                    //если пользователь запускает бота по рефералке, то ставлю ему реферала
                    (count($command) < 2) ? $user["ref"] = 0 : $user["ref"] = $command[1];
                    $idstore = R::store($user);
                    //file_put_contents("testRBEAns.txt", "{$idstore}\n{$username}\n{$first_name}");
                }

                $template = new Template($temp_name);
                $template = $template->Load();
                $template->LoadButtons();
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/invite":
                $this->DelMessageText($user["chat_id"], $message_id);
                $template = new Template("message_27", [
                    new TemplateData(":chatId", $user["chat_id"]),
                ]);
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;

            case "/present":
                // получаю тип подарка
                $present_type = (int)$command[1];

                // если тип подарка не указан, то ничего не делаю
                if (!$present_type) return;

                // формирую контент пользователя
                switch ($present_type) {
                    case 1:
                        $template = new Template("present_1", [
                            new TemplateData(":presentType", $present_type)
                        ]);
                        break;
                    case 2:
                        $template = new Template("present_2", [
                            new TemplateData(":presentType", $present_type)
                        ]);
                        break;
                    default: // если тип подарка неправильный, то ничего не делаю
                        return;
                }

                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $userscron = R::findOne("userscron", "user_id = {$user["id"]}");

                if (!$userscron) {
                    $userscron = R::dispense("userscron");
                    $userscron["user_id"] = $user["id"];
                    $userscron["timestamp_start"] = time();
                    $userscron["wait_time"] = 7200;
                    $userscron["message_type"] = 3;
                }

                R::store($userscron);

                $template = $template->Load();
                $template->LoadButtons();

                // отправляю сообщение пользователю
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/message_3": // ответ на сообщение 3 из кронтаба
                $button_type = (int)$command[1];

                // формирую контент пользователя
                $content = "";

                // формирую кнопки пользователя
                $buttons = [];

                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                // пользователь начал проходить марафон, удаляю его из кронтаба
                $userscron = R::findOne("userscron", "user_id = {$user["id"]}");
                R::trash("userscron", $userscron["id"]);

                // разные ответы в зависимости от выбота пользователя
                switch ($button_type) {
                    case 1: // Да /message_4
                        $preCommand = "/present 1";
                        $template = new Template("message_4");
                        $template = $template->Load();
                        $template->LoadButtons();
                        $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                        return;
                    case 2: // Нет /message_5
                        $preCommand = "/present 1";
                        $template = new Template("message_5");
                        $template = $template->Load();
                        $template->LoadButtons();

                        $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                        return;
                    case 3: // Не успела
                        $userscron = R::findOne("userscron", "user_id = {$user["id"]}");
                        if (!$userscron) {
                            $userscron = R::dispense("userscron");
                            $userscron["user_id"] = $user["id"];
                            $userscron["timestamp_start"] = time();
                            $userscron["wait_time"] = 7200;
                            $userscron["message_type"] = 3;
                        } else {
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
                        }
//                    R::store($userscron);
                        if ((int)$userscron["wait_time"] == 86400) {
                            R::store($userscron);
                        } else {
                            R::trash("userscron", $userscron["id"]);
                        }

                        // получаю тип подарка
                        $present_type = (int)$command[2];

                        // формирую контент пользователя
                        switch ($present_type) {
                            case 1:
                                $template = new Template("present_1", [
                                    new TemplateData(":presentType", $present_type)
                                ]);
                                break;
                            case 2:
                                $template = new Template("present_2", [
                                    new TemplateData(":presentType", $present_type)
                                ]);
                                break;
                            default: // если тип подарка неправильный, то ничего не делаю
                                return;
                        }

                        $template = $template->Load();
                        $template->LoadButtons();

                        $response = $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                        return;
                    case 4:
                        $preCommand = "/present " . $command[2];
                        $template = new Template("message_3", [
                            new TemplateData(":command", $command[2])
                        ]);
                        $template = $template->Load();
                        $template->LoadButtons();

                        $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                        return;
                }
                return;
            case "/message_4":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_4");
                $template = $template->Load();
                $template->LoadButtons();

                // отправляю сообщение пользователю
                $response = $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/message_6":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);
                $preCommand = "/message_4";
                $template = new Template("message_6", [
                    new TemplateData(":preCommand", $preCommand),
                ]);

                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                return;
            case "/message_6_1":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);
                $preCommand = "/message_4";
                $template = new Template("message_6_1", [
                    new TemplateData(":preCommand", $preCommand),
                ]);
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                return;
            case "/message_13":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $price = file_get_contents("modules/templates/admin/price.txt");
                $template = new Template("message_13", [
                    new TemplateData(":price", $price)
                ]);
                $template->Load();
                $template->LoadButtons();

//                $content = $this->loadTemplate("message_13");
//
//
//                $buttons = [
//                    [
//                        $this->buildInlineKeyBoardButton("Нет", "/message_8 $price"), // сообщение 8
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Есть", "/message_14"), // сообщение 14
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Назад", "/message_6_1"),
//                    ],
//                ];

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                return;
            case "/extaz_reqvezit":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("extaz_reqvezit", [
                    new TemplateData(":price", $command[1]),
                ]);

                $template->Load();
                $template->LoadButtons();

//                $buttons = [
//                    [
//                        $this->buildInlineKeyBoardButton("Подтвердить оплату", "/payment_confirmation $command[1]"),
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Назад", "/message_13"),
//                    ],
//                ];

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/payment_confirmation":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("payment_confirmation", [
                    new TemplateData(":price", $command[1]),
                ]);
                $template = $template->Load();
                $buttons = [
                    [
                        $this->buildInlineKeyBoardButton("Назад", "/message_8 $command[1]"),
                    ],
                ];

                $response = $this->sendMessage($user["chat_id"], $template->text);

                $user["action"] = "payment_confirmation {$response["result"]["message_id"]}";
                R::store($user);

                $order = R::dispense("orders");
                $order["user_id"] = $user["id"];
                R::store($order);

                return;
            case "/message_14":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $whatsup_number = file_get_contents("modules/templates/admin/whats_up_number.txt");
                $watsup_msg = "https://api.whatsapp.com/send?phone=$whatsup_number&text=" . $this->loadTemplate("message_15");

                $template = new Template("message_14");
                //$template = new Template("message_14",[
                //   new TemplateData(":whatsup", $watsup_msg)
                //]);
                $template->Load();
                $template->LoadButtons();

//                $content = $this->loadTemplate("message_14");
//
//                $buttons = [
//                    [
//                        $this->buildInlineKeyBoardButton("Записаться", "", "https://api.whatsapp.com/send?phone=79627813466&text=" . $this->loadTemplate("message_15")), // сообщение 15 сообщение с ссылкой на ватсап
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Сама разберусь", "/message_16"), // сообщение 16
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Назад", "/message_13"),
//                    ],
//                ];

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                return;

            case "/message_7":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_16");
                $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                return;
            case "/message_16":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $whatsup_number = file_get_contents("modules/templates/admin/whats_up_number.txt");
                $watsup_msg = "https://api.whatsapp.com/send?phone=$whatsup_number&text=" . $this->loadTemplate("message_15");

                $template = new Template("message_7", [
                    new TemplateData(":whatsup", $watsup_msg)
                ]);
                $template->Load();
                $template->LoadButtons();
//                $content = $this->loadTemplate("message_7");
//
//                $buttons = [
//                    [
//                        $this->buildInlineKeyBoardButton("Записаться", "", "https://api.whatsapp.com/send?phone=79627813466&text=" . $this->loadTemplate("message_15")), // сообщение 15 сообщение с ссылкой на ватсап
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Назад", "/message_14"),
//                    ],
//                ];

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                return;
            // проверка состояния, в зависимости от этапа марафона
            case "/health_check":
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("health_check_message");
                $template = $template->Load();

                $response = $this->sendMessage($user["chat_id"], $template->text);

                $user["action"] = "health_check $command[1] {$response["result"]["message_id"]}";
                R::store($user);
                return;
            case "/message_17":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_17");
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/message_18":
                $order = R::findOne("orders", "user_id = {$user["id"]} ORDER BY id DESC");
                if ($command[1] == 1) {
                    if ($order["status"] != 2) {
                        $template = new Template("payment_confirmation_deny");
                        $template = $template->Load();
                        $template->LoadButtons();

                        $this->sendMessage($user["chat_id"], $template->text, $template->buttons ?: null);
                        return;
                    }
                    $template = new Template("message_18");
                    $template = $template->Load();
                    $template->LoadButtons();
                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                } else {
                    $template = new Template("health_check_message");
                    $template = $template->Load();

                    $response = $this->sendMessage($user["chat_id"], $template->text);
                    $user["action"] = "health_check 1 {$response["result"]["message_id"]}";
                    R::store($user);

                    $order["marathon_stage"] = 1;
                    R::store($order);
                }

                $this->DelMessageText($user["chat_id"], $message_id);

                return;
            case "/message_19":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);

                $order = R::findOne("orders", "user_id = {$user["id"]} ORDER BY id DESC");
                $preCommand = "/message_18";
                if ($order["status"] != 2) {
                    $template = new Template("payment_confirmation_deny", [
                        new TemplateData(":preCommand", $preCommand),
                    ]);
                    $template = $template->Load();

                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons ?: null);
                } elseif ($command[1]) {
                    $template = new Template("message_19", [
                        new TemplateData(":preCommand", $preCommand),
                    ]);
                    $template = $template->Load();
                    $template->LoadButtons();
                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                } else {

                    $template = new Template("health_check_message");
                    $template = $template->Load();

                    $response = $this->sendMessage($user["chat_id"], $template->text);
                    $user["action"] = "health_check 2 {$response["result"]["message_id"]}";
                    R::store($user);
//                    $template = new Template("health_check", [
//                        new TemplateData(":marathonStage", 2),
//                    ]);
//                    $template = $template->Load();
//                    $template->LoadButtons();
//                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                    $order["marathon_stage"] = 2;
                    R::store($order);
                }

                return;
            case "/message_20":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);
                $preCommand = "/message_19";
                $order = R::findOne("orders", "user_id = {$user["id"]} ORDER BY id DESC");

                if ($order["status"] != 2) {
                    $template = new Template("payment_confirmation_deny", [
                        new TemplateData(":preCommand", $preCommand),
                    ]);
                    $template = $template->Load();

                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons ?: null);
                } elseif ($command[1]) {
                    $template = new Template("message_20");
                    $template = $template->Load();
                    $template->LoadButtons();
                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                } else {

                    $template = new Template("health_check_message");
                    $template = $template->Load();

                    $response = $this->sendMessage($user["chat_id"], $template->text);
                    $user["action"] = "health_check 3 {$response["result"]["message_id"]}";
                    R::store($user);
//                    $template = new Template("health_check", [
//                        new TemplateData(":marathonStage", 3),
//                    ]);
//                    $template = $template->Load();
//                    $template->LoadButtons();
//                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                    $order["marathon_stage"] = 3;
                    R::store($order);
                }
                return;
            case "/message_21":
                // удаляю предыдущее сообщение
                $this->DelMessageText($user["chat_id"], $message_id);
                $preCommand = "/message_20";
                $order = R::findOne("orders", "user_id = {$user["id"]} ORDER BY id DESC");

                if ($order["status"] != 2) {
                    $template = new Template("payment_confirmation_deny", [
                        new TemplateData(":preCommand", $preCommand),
                    ]);
                    $template = $template->Load();

                    $this->sendMessage($user["chat_id"], $template->text, $template->buttons ?: null);
                } else {

                    $template = new Template("health_check_message");
                    $template = $template->Load();

                    $response = $this->sendMessage($user["chat_id"], $template->text);
                    $user["action"] = "health_check 4 {$response["result"]["message_id"]}";
                    R::store($user);
                    $order["marathon_stage"] = 4;
                    R::store($order);
                }
                return;
            case"/message_22":
                $this->DelMessageText($user["chat_id"], $message_id);
                $template = new Template("message_22");
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case"/utro_reqvezit":

                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("utro_reqvezit");
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case"/message_23_1":
                $preCommand = "/message_22";
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_23_1");
                $template = $template->Load();

                $response = $this->sendMessage($user["chat_id"], $template->text);

                $user["action"] = "message_23_1 {$response["result"]["message_id"]}";
                R::store($user);

                $aphroditeMorning = R::dispense("aphroditemorning");
                $aphroditeMorning["user_id"] = $user["id"];

                R::store($aphroditeMorning);
                return;
            case "/message_24":
                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_24");
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;

            case "/courses":

                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_28");
                $template = $template->Load();
                $template->LoadButtons();

//                $content = $this->loadTemplate("message_28");
//
//                $buttons = [
//                    [
//                        $this->buildInlineKeyBoardButton("Утро Афродиты", "/message_22"),
//                        $this->buildInlineKeyBoardButton("Экстаз Афродиты", "/message_6_1"),
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Диагностика", "/message_26"),
//                        $this->buildInlineKeyBoardButton("Даосская магия", "/message_25"),
//
//                        //$this->buildInlineKeyBoardButton("Диагностика всего 1491р.", "", "https://wa.me/+79627813466?text=" . $this->loadTemplate("message_15")),
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Назад", "/start")
//                    ],
//                ];
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons, 3);
                return;

            case "/message_25":#Даосская магия
                $preCommand = "/courses";
                $this->DelMessageText($user["chat_id"], $message_id);
                $template = new Template("message_25", [
                    new TemplateData(":pay", "/daos_reqveziti"),
                    new TemplateData(":preCommand", $preCommand),
                ]);
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/daos_reqveziti":
                $this->DelMessageText($user["chat_id"], $message_id);
                $preCommand = "/message_25";
                $template = new Template("daos_reqveziti", [
                    new TemplateData(":preCommand", $preCommand),
                ]);
                $template = $template->Load();
                $template->LoadButtons();

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case"/message_25_2":
                $preCommand = "/daos_reqveziti";

                $this->DelMessageText($user["chat_id"], $message_id);

                $template = new Template("message_25_2");
                $template = $template->Load();
                $template->LoadButtons();

                $response = $this->sendMessage($user["chat_id"], $template->text, $template->buttons);

                $user["action"] = "message_25_2 {$response["result"]["message_id"]}";
                R::store($user);

                $daossmagick = R::dispense("daossmagick");
                $daossmagick["user_id"] = $user["id"];

                R::store($daossmagick);
                return;

            case "/message_26": //Диагностика
                $this->DelMessageText($user["chat_id"], $message_id);

                $whatsup_number = file_get_contents("modules/templates/admin/whats_up_number.txt");
                $watsup_msg = "https://api.whatsapp.com/send?phone=$whatsup_number&text=" . $this->loadTemplate("message_15");

                $template = new Template("message_26", [
                    new TemplateData(":whatsup", $watsup_msg)
                ]);
                $template->Load();
                $template->LoadButtons();

//                $content = $this->loadTemplate("message_26");
//                $buttons = [
//                    [
//                        $this->buildInlineKeyBoardButton("Диагностика всего 1491р.", "", "https://api.whatsapp.com/send?phone=79627813466&text=" . $this->loadTemplate("message_15")),
//
//                    ],
//                    [
//                        $this->buildInlineKeyBoardButton("Назад", "/start"),
//                    ],
//                ];

                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            case "/help":
                $this->DelMessageText($user["chat_id"], $message_id);
                $template = new Template("help");
                $template = $template->Load();
                $template->LoadButtons();
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;

            case "Практика":
            case "практика": // Обработчик кодового слова
                $this->DelMessageText($user["chat_id"], $message_id);
                $template = new Template("get_practice");
                $template = $template->Load();
                $template->LoadButtons();
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;

            case "/subscribe_check": // Проверка подписки на канал
            case "Подписалась":
            case "подписалась":
                $this->DelMessageText($user["chat_id"], $message_id);
                $chat_id = $configs['channel']['channel_id'];
                $result = $this->getChatMember($chat_id, $user['chat_id']);

                $this->DelMessageText($user["chat_id"], $message_id);
                if (!in_array($result['result']['status'], ['kicked', 'left'])) {
                    $template = new Template("present_2");
                } else {
                    $template = new Template("practice_not_subscribed");
                }
                $template = $template->Load();
                $template->LoadButtons();
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
        }

        return;
    }
}

