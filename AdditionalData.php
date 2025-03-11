<?php

class AdditionalData
{
    private ?string $billNumber;
    private ?string $mobileNumber;
    private ?string $store;
    private ?string $loyaltyNumber;
    private ?string $reference;
    private ?string $customerLabel;
    private ?string $terminal;
    private ?string $purpose;
    private ?string $dataRequest;

    public function __construct(
        ?string $billNumber = null,
        ?string $mobileNumber = null,
        ?string $store = null,
        ?string $loyaltyNumber = null,
        ?string $reference = null,
        ?string $customerLabel = null,
        ?string $terminal = null,
        ?string $purpose = null,
        ?string $dataRequest = null
    ) {
        $this->billNumber = $billNumber;
        $this->mobileNumber = $mobileNumber;
        $this->store = $store;
        $this->loyaltyNumber = $loyaltyNumber;
        $this->reference = $reference;
        $this->customerLabel = $customerLabel;
        $this->terminal = $terminal;
        $this->purpose = $purpose;
        $this->dataRequest = $dataRequest;
    }

    // Getter and setter methods for each property
    public function getBillNumber(): ?string
    {
        return $this->billNumber;
    }

    public function setBillNumber(?string $billNumber): void
    {
        $this->billNumber = $billNumber;
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    public function setMobileNumber(?string $mobileNumber): void
    {
        $this->mobileNumber = $mobileNumber;
    }

    public function getStore(): ?string
    {
        return $this->store;
    }

    public function setStore(?string $store): void
    {
        $this->store = $store;
    }

    public function getLoyaltyNumber(): ?string
    {
        return $this->loyaltyNumber;
    }

    public function setLoyaltyNumber(?string $loyaltyNumber): void
    {
        $this->loyaltyNumber = $loyaltyNumber;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): void
    {
        $this->reference = $reference;
    }

    public function getCustomerLabel(): ?string
    {
        return $this->customerLabel;
    }

    public function setCustomerLabel(?string $customerLabel): void
    {
        $this->customerLabel = $customerLabel;
    }

    public function getTerminal(): ?string
    {
        return $this->terminal;
    }

    public function setTerminal(?string $terminal): void
    {
        $this->terminal = $terminal;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): void
    {
        $this->purpose = $purpose;
    }

    public function getDataRequest(): ?string
    {
        return $this->dataRequest;
    }

    public function setDataRequest(?string $dataRequest): void
    {
        $this->dataRequest = $dataRequest;
    }
}
