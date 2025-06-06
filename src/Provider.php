<?php

namespace cuongnm\viet_qr_pay;

use cuongnm\viet_qr_pay\constants\QRProvider;

class Provider
{
    private ?string $fieldId;
    private ?string $name;
    private ?string $guid;
    private ?string $service;

    public function __construct(
        ?string $fieldId = null,
        ?string $name = null,
        ?string $guid = null,
        ?string $service = null
    ) {
        $this->fieldId = $fieldId;
        $this->name = $name;
        $this->guid = $guid;
        $this->service = $service;
    }

    // Getter and setter methods for each property
    public function getFieldId(): ?string
    {
        return $this->fieldId;
    }

    public function setFieldId(?string $fieldId): void
    {
        $this->fieldId = $fieldId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(?string $guid): void
    {
        $this->guid = $guid;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): void
    {
        $this->service = $service;
    }
}
