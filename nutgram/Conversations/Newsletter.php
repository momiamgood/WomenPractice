<?php
namespace Nutgram\Conversations;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class Newsletter extends Conversation
{
    private ?string $newsletter_text = null;
    private ?string $file_id = null;
    private ?string $file_type = null;
    protected function getSerializableAttributes(): array
    {
        return [
            'newsletter_text' => $this->newsletter_text,
            'file_id' => $this->file_id,
            'file_type' => $this->file_type
        ];
    }

    public function start(Nutgram $bot)
    {
        $sentMessage = $bot->sendMessage(
            text: 'Выберите тип уведомления клиентов:',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('Фотография', callback_data: 'notification:1'))
                ->addRow(InlineKeyboardButton::make('Кружок', callback_data: 'notification:2'))
                ->addRow(InlineKeyboardButton::make('Видео', callback_data: 'notification:3'))
                ->addRow(InlineKeyboardButton::make('Отмена', callback_data: 'notification:4'))
        );
        $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
        $this->next('requestMedia');
    }

    public function requestMedia(Nutgram $bot)
    {
        if($bot->isCallbackQuery()) {
            $buttonsDataset = ['notification:1', 'notification:2', 'notification:3'];

            if(in_array($bot->callbackQuery()->data, $buttonsDataset)) {
                $text = null;
                switch ($bot->callbackQuery()->data) {
                    case 'notification:1':
                        $text = 'Отправьте в чат фотографию для рассылки:';
                        $this->file_type = 'photo';
                        break;
                    case 'notification:2':
                        $text = 'Отправьте в чат кружок для рассылки:';
                        $this->file_type = 'video_note';
                        break;
                    case 'notification:3':
                        $text = 'Отправьте в чат видео для рассылки:';
                        $this->file_type = 'video';
                        break;
                    case 'notification:4':
                        $bot->deleteMessage(chat_id: $bot->chatId(), message_id: $bot->messageId());
                        break;
                }
                $sentMessage = $bot->sendMessage(text: $text, chat_id: $bot->chatId());
                $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
                $this->next('handleMedia');
            } else $this->end();
        } else $this->end();
    }

    public function handleMedia(Nutgram $bot)
    {
        $message = $bot->message();
        if(!is_null($message)) {
            if($this->file_type === 'photo' OR $this->file_type === 'video') {
                $messageText = 'Напишите текст для рассылки:';
                $keyboard = InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make(text: 'Пропустить', callback_data: 'notification:skip'));
                if($this->file_type === 'photo') {
                    if($message->photo) {
                        $this->file_id = $message->photo[3]->file_id;
                        $this->next('handleCaption');
                    } else {
                        $sentMessage = $bot->sendMessage(text: 'Вам нужно отправить фотографию!!!');
                        $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
                        $this->next('handleMedia');
                        return;
                    }
                } else if($this->file_type === 'video') {
                    if($message->video) {
                        $this->file_id = $message->video->file_id;
                        $this->next('handleCaption');
                        file_put_contents(__DIR__.'/../../log.txt', $message->video->file_id);
                    } else {
                        $sentMessage = $bot->sendMessage(text: 'Вам нужно отправить видео!!!');
                        $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
                        $this->next('handleMedia');
                        return;
                    }
                }
                $sentMessage = $bot->sendMessage(text: $messageText, reply_markup: $keyboard);
                $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
            } else if($this->file_type === 'video_note') {
                if($message->video_note) {
                    $this->file_id = $message->video_note->file_id;
                    $this->confirmNewsletter($bot);
                } else {
                    $sentMessage = $bot->sendMessage(text: 'Вам нужно отправить кружок!!!');
                    $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
                    $this->next('handleMedia');
                }
            }
        }
    }

    public function handleCaption(Nutgram $bot)
    {
        if(!$bot->isCallbackQuery()) {
            if(!is_null($bot->message()->text)) {
                $this->newsletter_text = $bot->message()->text;
            } else {
                $sentMessage = $bot->sendMessage(text: 'Вам нужно написать текст, который будет в рассылке!');
                $bot->setGlobalData('notification', ['delete_message_id' => $sentMessage->message_id]);
                $this->next('handleCaption');
                return;
            }
        }
        $this->confirmNewsletter($bot);
    }

    public function confirmNewsletter(Nutgram $bot)
    {
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(text: 'Применить', callback_data: 'notification:send'))
            ->addRow(InlineKeyboardButton::make(text: 'Отменить', callback_data: 'notification:cancel'));
        if($this->file_type === 'photo') {
            $bot->sendPhoto(
                photo: $this->file_id,
                caption: $this->newsletter_text,
                reply_markup: $keyboard
            );
        } else if($this->file_type === 'video') {
            $bot->sendVideo(
                video: $this->file_id,
                caption: $this->newsletter_text,
                reply_markup: $keyboard
            );
        } else if($this->file_type === 'video_note') {
            $bot->sendVideoNote(
                video_note: $this->file_id,
                reply_markup: $keyboard
            );
        }

        $this->next('sendNewsletter');
    }

    public function sendNewsletter(Nutgram $bot)
    {
        if($bot->isCallbackQuery()) {
            if($bot->callbackQuery()->data === 'notification:send') {
                $users = \R::findAll('users');
                foreach ($users as $user) {
                    switch ($this->file_type) {
                        case 'photo':
                            $bot->sendPhoto(
                                photo: $this->file_id,
                                caption: $this->newsletter_text,
                                chat_id: $user->chat_id
                            );
                            break;
                        case 'video':
                            $bot->sendVideo(
                                video: $this->file_id,
                                caption: $this->newsletter_text,
                                chat_id: $user->chat_id
                            );
                            break;
                        case 'video_note':
                            $bot->sendVideoNote(
                                video_note: $this->file_id,
                                chat_id: $user->chat_id
                            );
                            break;
                    }
                }
                file_put_contents(__DIR__."/../../modules/templates/admin/sendMsg.txt", "");
                $this->end();
            } else $this->end();
        }
    }

//  file_put_contents(__DIR__.'/../../log.txt', $fileText);
}