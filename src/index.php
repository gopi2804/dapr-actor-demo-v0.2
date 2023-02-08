<?php

require_once __DIR__.'/../vendor/autoload.php';

use Dapr\Actors\ActorProxy;
use Dapr\App;
use Dapr\Attributes\FromBody;
use Dapr\PubSub\CloudEvent;
use Dapr\PubSub\Publish;
use Picker\Actors\Bin\BinInterface;
use Picker\Actors\Inventory\InventoryInterface;
use Picker\Actors\Inventory\InventoryReceiveMessage;

$app = App::create(
    configure: fn(\DI\ContainerBuilder $builder) => $builder->addDefinitions(__DIR__.'/config.php')->enableCompilation(__DIR__)
);

$app->post(
    '/order/{orderId}/{tradeId}',
    function (string $orderId, string $tradeId, ActorProxy $actorProxy, \DI\FactoryInterface $factory) {
        //$id   = uniqid('inv_');
        $orders = $actorProxy->get(InventoryInterface::class, $orderId);
        $orders->order(new InventoryReceiveMessage($orderId, $tradeId));
        /**
         * @var Publish $publisher
         */
        $publisher = $factory->make(Publish::class, ['pubsub' => 'pubsub']);
        $publisher->topic('receive')->publish(['OrderId' => $orderId, 'TradeId' => $tradeId]);

        return ['TradeId' => $tradeId];
    }
);

$app->post(
    '/events/receivedItem',
    function (#[FromBody] CloudEvent $event, ActorProxy $actorProxy) {
        ['OrderId' => $orderId, 'TradeId' => $tradeId] = $event->data;
        $bin = $actorProxy->get(BinInterface::class, $orderId);
        $bin->addItem($tradeId);
    }
);

$app->get(
    '/item/{id}/location',
    function (string $id, ActorProxy $actorProxy) {
        $item = $actorProxy->get(InventoryInterface::class, $id);

        return $item->getLocation();
    }
);

$app->post(
    '/process/{orderId}',
    function (string $orderId, ActorProxy $actorProxy) {
        $bin = $actorProxy->get(BinInterface::class, $orderId);
        $bin->removeItem();

        return ['Success!!!'];
    }
);

$app->get(
    '/order/{orderId}',
    function (ActorProxy $actorProxy, string $orderId) {
        $bin = $actorProxy->get(BinInterface::class, $orderId);
        //return [$orderId];
        return $bin->listItems();
    }
);

$app->get('/info', fn() => phpinfo(INFO_MODULES | INFO_CONFIGURATION));

$app->start();