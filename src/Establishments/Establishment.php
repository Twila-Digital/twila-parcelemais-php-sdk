<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class Establishment
{
    /** @var string */
    public $establishmentId;

    /** @var string CNPJ da loja */
    public $document;

    /** @var string */
    public $legalName;

    /** @var string */
    public $tradeName;

    /** @var bool */
    public $isActive;

    /** @var EstablishmentOwner */
    public $owner;

    /** @var int|null um dos DisbursementModel::* */
    public $disbursementModel;

    /** @var EstablishmentBankAccount|null */
    public $bankAccount;

    /** @var EstablishmentAddress|null */
    public $address;

    public function __construct(
        string $establishmentId,
        string $document,
        string $legalName,
        string $tradeName,
        bool $isActive,
        EstablishmentOwner $owner,
        ?int $disbursementModel = null,
        ?EstablishmentBankAccount $bankAccount = null,
        ?EstablishmentAddress $address = null
    ) {
        $this->establishmentId = $establishmentId;
        $this->document = $document;
        $this->legalName = $legalName;
        $this->tradeName = $tradeName;
        $this->isActive = $isActive;
        $this->owner = $owner;
        $this->disbursementModel = $disbursementModel;
        $this->bankAccount = $bankAccount;
        $this->address = $address;
    }
}
