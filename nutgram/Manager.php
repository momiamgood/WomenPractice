<?php

namespace Nutgram;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Nutgram as Bot;
use SergiX44\Nutgram\RunningMode\Webhook;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use SergiX44\Nutgram\Configuration;

class Manager
{
    private Bot $bot;

    public function __construct()
    {
        $config = parse_ini_file(__DIR__ . "/../configs.ini", true);

        $psr6Cache = new FilesystemAdapter();
        $psr16Cache = new Psr16Cache($psr6Cache);
        $bot = new Bot(
            $config['system']['telegram_token'],
            new Configuration(cache: $psr16Cache)
        );
        $bot->setRunningMode(Webhook::class);
        $this->bot = $bot;

//        # Подключение к бд
//        $mysql_ip = $config['mysql']['ip'];
//        $mysql_dbname = $config['mysql']['dbname'];
//        $mysql_dbuser = $config['mysql']['dbuser'];
//        $mysql_password = "";
//
//        $this->dbConnect($mysql_ip, $mysql_dbname, $mysql_dbuser, $mysql_password);
    }

    public function start() {
        $this->bot->middleware(function (Nutgram $bot, $next) {
            $notificationData = $bot->getGlobalData('notification', null);
            if(!is_null($notificationData)) {
                if($notificationData['delete_message_id'] == $bot->messageId()) {
                    $bot->deleteMessage(chat_id: $bot->chatId(), message_id: $bot->messageId());
                } else {
                    $bot->deleteMessage(chat_id: $bot->chatId(), message_id: $bot->messageId());
                    $bot->deleteMessage(chat_id: $bot->chatId(), message_id: $notificationData['delete_message_id']);
//                    file_put_contents(__DIR__.'/../log.txt', $notificationData['delete_message_id']);
                }
                $bot->deleteGlobalData('notification');
            } else {
                $bot->deleteMessage(chat_id: $bot->chatId(), message_id: $bot->messageId());
            }
            $next($bot);
        });

        $this->bot->onCommand('notification',Conversations\Newsletter::class);
    }

    public function end() {
        $this->bot->run();
        die();
    }

    private function saveMedia(int|string $fileId, string $storageDirPath)
    {
        $this->bot->getFile($fileId)->save($storageDirPath);
    }
}