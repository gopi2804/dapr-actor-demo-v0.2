<?php

namespace Picker\Actors\Bin;

use Dapr\Actors\Actor;
use Dapr\Actors\Attributes\DaprType;

#[DaprType('Bin')]
class BinActor extends Actor implements BinInterface
{
    public function __construct(string $id, private BinState $state)
    {
        parent::__construct($id);
    }

    public function move(string $location): void
    {
        $this->state->shelf = $location;
    }

    public function addItem(string $item): void
    {
        $items = $this->state->items;
        $items[] = $item;
        $this->state->items = $items; /*array_unique($items);*/
    }

    public function removeItem(): void
    {
        $items1 = $this->state->items;
        //$items = $this->state->items;
        //$items = array_filter($items, fn($i) => $item === $i);
        array_shift($items1);
        $this->state->items = $items1;
    }

    public function listItems(): array
    {
        return $this->state->items;
    }
}