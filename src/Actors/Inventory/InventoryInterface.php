<?php

namespace Picker\Actors\Inventory;

use Dapr\Actors\Attributes\DaprType;

#[DaprType('Inventory')]
interface InventoryInterface {
    public function order(InventoryReceiveMessage $message): void;
    public function getLocation(): string;
}