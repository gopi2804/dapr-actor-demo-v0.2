<?php

namespace Picker\Actors\Inventory;

class InventoryReceiveMessage
{
    public function __construct(public string $binId, public string $receiverId)
    {
    }
}