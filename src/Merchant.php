<?php

namespace cuongnm\viet_qr_pay;

class Merchant
{
    private ?string $id;
    private ?string $name;

    public function __construct(?string $id = null, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    // Getter and setter methods for each property
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
