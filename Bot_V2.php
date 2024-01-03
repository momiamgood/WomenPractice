<?php

/*require __DIR__ . "/rb-mysql.php";

$bot = new Bot_V2();

$bot->init();*/

class Bot_V2
{
    private $data;

    public function init()
    {
        $this->data = json_decode(file_get_contents('php://input'), true);
        $this->_router();
        return true;
    }

    /**
     * @throws \RedBeanPHP\RedException\SQL
     */
    private function _router()
    {
        // получаю конфиг параметры из файла configs.ini
        $configs = parse_ini_file(__DIR__ . "/configs.ini", true);

        // токен телеграм бота
        define('TELEGRAM_TOKEN', $configs['system']['telegram_token']);
        define("ADMIN_CHAT_ID", -1001706592010);

        # Подключение к бд
        $mysql_ip = "localhost";
        $mysql_dbname = "satory";
        $mysql_dbuser = "root";
        $mysql_password = "";

        date_default_timezone_set($configs['system']['timezone']);

        $this->dbConnect($mysql_ip, $mysql_dbname, $mysql_dbuser, $mysql_password);
        if ($this->data) {
            $id = $this->data['message']['from']['id'];
            $first_name = $this->data['message']['from']['first_name'];
            $last_name = $this->data['message']['from']['last_name'];
            $username = $this->data['message']['from']['username'];
            $chat_id = $this->data['message']['chat']['id'];
            $message_id = $this->data['message']['message_id'];
            $text = $this->data['message']['text'];
            if (array_key_exists('callback_query', $this->data)) {
                $id = $this->data['callback_query']['from']['id'];
                $first_name = $this->data['callback_query']['from']['first_name'];
                $last_name = $this->data['callback_query']['from']['last_name'];
                $username = $this->data['callback_query']['from']['username'];
                $chat_id = $this->data['callback_query']['message']['chat']['id'];
                $message_id = $this->data['callback_query']['message']['message_id'];
                $text = $this->data['callback_query']['data'];
                $chat_username = $this->data['callback_query']['message']['from']['username'];
                $callback_query_id = $this->data['callback_query']['id'];
            }

            $command = explode(" ", $text);

            // ищу пользователя, если его не нашлось, то создаю
            /*   if ($chat_id != ADMIN_CHAT_ID) {
                   $user = R::findOne("users", "chat_id = $chat_id");
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
               }

   */
            $user = R::findOne("users", "chat_id = $chat_id");

            // если пользователь отправил файл
            if (isset($this->data['message']['photo'])) {
                $action = explode(" ", $user["action"]);
                switch ($action[0]) {
                    case "payment_confirmation":
                        // удаляю предыдущее сообщение
                        $this->DelMessageText($user["chat_id"], $message_id);
                        $this->DelMessageText($user["chat_id"], $action[1]);

                        $fileName = $this->saveFile($this->data, $user);
                        $order = R::findOne("orders", "user_id = {$user["id"]} ORDER BY id DESC");
                        if ($order) {
                            $order["status"] = 1;
                            $order["check_photo"] = $fileName;
                            R::store($order);

                            $templateUser = new Template("safety_precautions");
                            $templateUser = $templateUser->Load();
                            $templateUser->LoadButtons();

                            $this->sendMessage($user["chat_id"], $templateUser->text, $templateUser->buttons);

                            //$price = file_get_contents("modules/templates/admin/price.txt");

                            $templateAdmin = new Template("admin/process_purchase", [
                                new TemplateData(":username", $user["username"]),
                                new TemplateData(":user_id", $user["id"]),
                                new TemplateData(":orderId", $order["id"]),
                            ]);
                            $templateAdmin = $templateAdmin->Load();
                            $templateAdmin->LoadButtons();

                            $this->sendPhoto(ADMIN_CHAT_ID, "https://katyasatorinebot.online/bot/{$order["check_photo"]}", $templateAdmin->text, $templateAdmin->buttons);
                        }
                        return;
                    case "message_23_1":
                        $this->DelMessageText($user["chat_id"], $message_id);
                        $this->DelMessageText($user["chat_id"], $action[1]);

                        $fileName = $this->saveFile($this->data, $user);
                        $aphroditeMorning = R::findOne("aphroditemorning", "user_id = {$user["id"]} ORDER BY id DESC");
                        if ($aphroditeMorning) {
                            $aphroditeMorning["status"] = 1;
                            $aphroditeMorning["check_photo"] = $fileName;
                            R::store($aphroditeMorning);

                            $templateUser = new Template("payment_confirmation_success_aphro");
                            $templateUser = $templateUser->Load();

                            $response = $this->sendMessage($user["chat_id"], $templateUser->text);

                            //$user["action"] = "";
                            //R::store($user);

                            $templateAdmin = new Template("admin/aphrodite_morning/process_purchase", [
                                new TemplateData(":username", $user["username"]),
                                new TemplateData(":user_id", $user["id"]),
                                new TemplateData(":aphroditeMorningId", $aphroditeMorning["id"]),
                            ]);
                            $templateAdmin = $templateAdmin->Load();
                            $templateAdmin->LoadButtons();

                            $this->sendPhoto(ADMIN_CHAT_ID, "https://katyasatorinebot.online/bot/{$aphroditeMorning["check_photo"]}", $templateAdmin->text, $templateAdmin->buttons);
                        }
                        return;

                    case "message_25_2":
                        $this->DelMessageText($user["chat_id"], $message_id);
                        $this->DelMessageText($user["chat_id"], $action[1]);

                        $fileName = $this->saveFile($this->data, $user);
                        $daossmagick = R::findOne("daossmagick", "user_id = {$user["id"]} ORDER BY id DESC");
                        if ($daossmagick) {
                            $daossmagick["status"] = 1;
                            $daossmagick["check_photo"] = $fileName;
                            R::store($daossmagick);

                            $templateUser = new Template("payment_confirmation_success_daoss");
                            $templateUser = $templateUser->Load();

                            $response = $this->sendMessage($user["chat_id"], $templateUser->text);

                            //$user["action"] = "";
                            //R::store($user);

                            $templateAdmin = new Template("admin/daoss_magick/process_purchase", [
                                new TemplateData(":username", $user["username"]),
                                new TemplateData(":user_id", $user["id"]),
                                new TemplateData(":daossMagickId", $daossmagick["id"]),
                                //new TemplateData(":userMessageId", $response["result"]["message_id"]),
                            ]);
                            $templateAdmin = $templateAdmin->Load();
                            $templateAdmin->LoadButtons();

                            $this->sendPhoto(ADMIN_CHAT_ID, "https://katyasatorinebot.online/bot/{$daossmagick["check_photo"]}", $templateAdmin->text, $templateAdmin->buttons);
                        }
                        return;
                }
            } elseif (isset($this->data['message']['document'])) {
                $this->DelMessageText($user["chat_id"], $message_id);
                $template = new Template("file_sent");
                $template = $template->Load();
                $template->LoadButtons();
                $this->sendMessage($user["chat_id"], $template->text, $template->buttons);
                return;
            }
            foreach (glob(__DIR__ . '/modules/*.php') as $file) {
                if (is_file($file)) include_once $file;
            }
        }
    }

    private function dbConnect($mysql_ip, $mysql_dbname, $mysql_dbuser, $mysql_password)
    {
        R::setup("mysql:host=$mysql_ip;dbname=$mysql_dbname",
            $mysql_dbuser, $mysql_password);
    }

    public function sendMessage($chat_id, $text, $buttons = NULL, $params = 1)
    {
        $content = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'html'
        ];

        // если переданны кнопки то добавляем их к сообщению
        if ($buttons && is_array($buttons)) {
            if ($params == 1) {
                $content['reply_markup'] = $this->buildInlineKeyBoard($buttons);
            } else if ($params == 2) {
                $content['reply_markup'] = $this->ReplyKeyboardRemove();
            } else if ($params == 3) {
                $content['reply_markup'] = $this->buildCoureseButtons($buttons);
            } else {
                $content['reply_markup'] = $this->buildKeyBoard($buttons);
            }
        }

        return $this->requestToTelegram($content, "sendMessage");
    }

    private function sendPhoto($chat_id, $photo, $caption, $buttons = NULL)
    {
        $content = [
            'chat_id' => $chat_id,
            'photo' => $photo,
            'caption' => $caption,
            'parse_mode' => 'html',
        ];

        // если переданны кнопки то добавляем их к сообщению
        if (!is_null($buttons) && is_array($buttons)) {
            $content['reply_markup'] = $this->buildInlineKeyBoard($buttons);
        }

        return $send = $this->requestToTelegram($content, "sendPhoto");
    }

    public function sendVoice($chat_id, $file_id, $buttons = NULL)
    {
        $post_data = array(
            'chat_id' => $chat_id,
            'voice' => $file_id,
        );
        // если переданны кнопки то добавляем их к сообщению
        if (!is_null($buttons) && is_array($buttons)) {
            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/' . 'sendVoice');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_exec($ch);
        curl_close($ch);
    }

//для рассылки
//    public function sendPhotoAdmin($chat_id, $photo_file_id, $buttons = NULL)
//    {
//        $post_data = array(
//            'chat_id' => $chat_id,
//            'photo' => $photo_file_id,
//        );
//
//        // Если переданы кнопки, добавляем их к сообщению
//        if (!is_null($buttons) && is_array($buttons)) {
//            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
//        }
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendPhoto');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_exec($ch);
//        curl_close($ch);
//    }

//    public function sendPhotoAdmin($chatId, $photoId, $caption, $buttons = NULL) {
//        $post_data = [
//            'chat_id' => $chatId,
//            'photo' => $photoId,
//            'caption' => $caption,
//        ];

    // Если переданы кнопки, добавляем их к сообщению
//        if (!is_null($buttons) && is_array($buttons)) {
//            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
//        }

//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendPhoto');
//       curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_exec($ch);
//        curl_close($ch);
//    }

//    public function sendVideoNote($chat_id, $file_id, $duration, $buttons = NULL)
//    {
//        $post_data = array(
//            'chat_id' => $chat_id,
//            'video_note' => $file_id,
//            'duration' => $duration,
//        );

    // Если переданы кнопки, добавляем их к сообщению
//        if (!is_null($buttons) && is_array($buttons)) {
//            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
//        }

//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendVideoNote');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_exec($ch);
//        curl_close($ch);
//    }

//    public function sendDocument($chat_id, $file_id, $buttons = NULL)
//    {
//        $post_data = [
//            'chat_id' => $chat_id,
//            'document' => $file_id,
//        ];

    // Если переданы кнопки, добавляем их к сообщению
//        if (!is_null($buttons) && is_array($buttons)) {
//            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
//        }

//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendDocument');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_exec($ch);
//        curl_close($ch);
//    }

//    public function sendAudio($chat_id, $file_id, $buttons = NULL)
//    {
//       $post_data = [
//           'chat_id' => $chat_id,
//            'audio' => $file_id,
//        ];

    // Если переданы кнопки, добавляем их к сообщению
//        if (!is_null($buttons) && is_array($buttons)) {
//            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
//        }

//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendAudio');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_exec($ch);
//        curl_close($ch);
//    }

//    public function sendVideo($chat_id, $file_id, $buttons = NULL)
//    {
//        $post_data = [
//            'chat_id' => $chat_id,
//            'videos' => $file_id,
//       ];

    // Если переданы кнопки, добавляем их к сообщению
//        if (!is_null($buttons) && is_array($buttons)) {
//            $post_data['reply_markup'] = $this->buildInlineKeyBoard($buttons);
//        }

//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendVideo');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_exec($ch);
//        curl_close($ch);
//    }


    public function buildInlineKeyBoard(array $options)
    {
        // собираем кнопки
        $replyMarkup = [
            'inline_keyboard' => $options,
        ];
        // преобразуем в JSON объект
        $encodedMarkup = json_encode($replyMarkup, true);
        // возвращаем клавиатуру
        return $encodedMarkup;
//        return [
//        'inline_keyboard' => $options,
//        ];
    }

    public function buildCoureseButtons(array $options)
    {
        //file_put_contents("buttons.txt", $options[0][0]);
        // собираем кнопки
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    $options[0][0],
                    $options[1][0],
                ],
                [
                    $options[2][0],
                    $options[3][0],
                ],
                [
                    $options[4][0],
                ],
            ],
        ];
        // преобразуем в JSON объект
        $encodedMarkup = json_encode($replyMarkup, true);
        // возвращаем клавиатуру
        return $encodedMarkup;
    }


    public function buildInlineKeyboardButton($text, $callback_data = '', $url = '')
    {

        // рисуем кнопке текст
        $replyMarkup = [
            'text' => $text,
        ];
        // пишем одно из обязательных дополнений кнопке
        if ($url != '') {
            $replyMarkup['url'] = $url;
        } elseif ($callback_data != '') {
            $replyMarkup['callback_data'] = $callback_data;
        }
        // возвращаем кнопку
        return $replyMarkup;
    }

    function ReplyKeyboardRemove()
    {
        // собираем кнопки
        $replyMarkup = [
            'remove_keyboard' => true,
        ];
        // преобразуем в JSON объект
        $encodedMarkup = json_encode($replyMarkup, true);
        // возвращаем клавиатуру
        return $encodedMarkup;
    }

    private function buildKeyBoard(array $options, $onetime = false, $resize = true, $selective = true)
    {
        $replyMarkup = [
            'keyboard' => $options,
            'one_time_keyboard' => $onetime,
            'resize_keyboard' => $resize,
            'selective' => $selective,
        ];

        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }

    private function requestToTelegram($data, $type)
    {
        $result = null;

        if (is_array($data)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/' . $type);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result1 = json_decode($result, true);
    }

    public function DelMessageText($chat_id, $message_id)
    {

        // готовим данные
        $content = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ];
        // отправляем запрос на удаление
        $this->requestToTelegram($content, "deleteMessage");
    }

    public function UpdateUserStatus($user_chat_id)
    {
        $cur_user = R::findOne("users", "chat_id = {$user_chat_id}");
        if ($cur_user["role"] >= 7) {
            return;
        }
        $cur_user["role"] += 1;
        R::store($cur_user);


        $ref_status = R::findOne("refbuffer", "id = {$cur_user["role"]}");

        $templateUser = new Template("status_notification", [
            new TemplateData(":status", "{$ref_status["status_name"]}"),
        ]);
        $templateUser = $templateUser->Load();
        $this->sendMessage($cur_user["chat_id"], $templateUser->text);

        if ($cur_user["ref"]) {
            $ref_user = R::findOne("users", "chat_id = {$cur_user["ref"]}");
            $ref_user_childs = R::find("users", "ref = {$cur_user["ref"]}");
            $counter = 0;
            foreach ($ref_user_childs as $usr) {
                if ($usr["role"] >= $ref_user["role"]) {
                    $counter += 1;
                }
                if ($counter == 2) {
                    $this->UpdateUserStatus($ref_user["chat_id"]);
                    break;
                }
            }
            return;
        } else {
            return;
        }
    }

    private function saveFile($data, $user)
    {

        $fileId = $data["message"]["photo"][count($data["message"]["photo"]) - 1]["file_id"];
        $response = $this->requestToTelegram(["file_id" => $fileId], "getFile");
        if (!$response["ok"]) return;

        $filePath = $response['result']['file_path'];
        # ссылка на файл в телеграме
        $fileFromTelegram = "https://api.telegram.org/file/bot" . TELEGRAM_TOKEN . "/" . $filePath;
        $explodedFilePath = explode(".", $filePath);
        $fileExtension = end($explodedFilePath);
        if (!file_exists("img/orders/" . $user["id"])) {
            mkdir("img/orders/" . $user["id"], 0777, true);
        }

        $newFileName = time() . "." . $fileExtension;
        copy($fileFromTelegram, "img/orders/{$user["id"]}/$newFileName");

        return "img/orders/{$user["id"]}/$newFileName";
    }

    private function loadTemplate($template_name, $templateData = null)
    {
        $templateText = file_get_contents("modules/templates/$template_name.txt");

        $templateText = str_replace("\n", "", $templateText);
        $templateText = str_replace("<:n>", "\n", $templateText);

        foreach ($templateData as $template => $data) {
            $templateText = str_replace(":$template", $data, $templateText);
        }

        return $templateText;
    }

    public function getChatMember(string $chat_id, string $user_id)
    {
        return $this->requestToTelegram([
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ], 'getChatMember');
    }
}