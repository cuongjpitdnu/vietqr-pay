<?php

class Consumer
{
    private ?string $bankBin;
    private ?string $bankNumber;

    public function __construct(?string $bankBin = null, ?string $bankNumber = null)
    {
        $this->bankBin = $bankBin;
        $this->bankNumber = $bankNumber;
    }

    // Getter and setter methods for each property
    public function getBankBin(): ?string
    {
        return $this->bankBin;
    }

    public function setBankBin(?string $bankBin): void
    {
        $this->bankBin = $bankBin;
    }

    public function getBankNumber(): ?string
    {
        return $this->bankNumber;
    }

    public function setBankNumber(?string $bankNumber): void
    {
        $this->bankNumber = $bankNumber;
    }
}
