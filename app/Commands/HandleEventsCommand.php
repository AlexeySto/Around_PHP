<?php

namespace App\Commands;

use App\Application;

use App\Database\SQLite;

use App\EventSender\EventSender;

use App\Models\Event;
use App\Telegram\TelegramApiImpl;
use PHPUnit\Framework\Constraint\IsEmpty;
use function PHPUnit\Framework\isEmpty;

//use App\Models\EventDto;

class HandleEventsCommand extends Command

{

    protected Application $app;

    public function __construct(Application $app)

    {

        $this->app = $app;

    }

    public function run(array $options = []): void

    {

        $event = new Event(new SQLite($this->app));

        $events = $event->select();

        $eventSender = new EventSender(new TelegramApiImpl($this->app->env('TELEGRAM_TOKEN')));

        foreach ($events as $event) {

            if ($this->shouldEventBeRan($event)) {

                $eventSender->sendMessage($event['receiver_id'], $event['text']);

            }
        }
    }

    public function shouldEventBeRan($event): bool

    {
        return (empty($event['minute']) ? true : (int)$event['minute'] === (int)date("i")) &&

            (empty($event['hour']) ? true : (int)$event['hour'] === (int)date("H")) &&

            (empty($event['day']) ? true : (int)$event['day'] === (int)date("d")) &&

            (empty((int)$event['month']) ? true : (int)$event['month'] === (int)date("m")) &&

            (empty((int)$event['day_of_week']) ? true : (int)$event['day_of_week'] === (int)date("w"));
    }

}