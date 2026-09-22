<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class CreateEstablishmentRequest
{
    /** @var string CNPJ da loja, somente números */
    public $document;

    /** @var string */
    public $legalName;

    /** @var string */
    public $tradeName;

    /** @var int um dos DisbursementModel::* */
    public $disbursementModel;

    /** @var EstablishmentOwner */
    public $owner;

    /** @var EstablishmentBankAccount */
    public $bankAccount;

    /** @var EstablishmentAddress|null */
    public $address;

    public function __construct(
        string $document,
        string $legalName,
        string $tradeName,
        int $disbursementModel,
        EstablishmentOwner $owner,
        EstablishmentBankAccount $bankAccount,
        ?EstablishmentAddress $address = null
    ) {
        $this->document = $document;
        $this->legalName = $legalName;
        $this->tradeName = $tradeName;
        $this->disbursementModel = $disbursementModel;
        $this->owner = $owner;
        $this->bankAccount = $bankAccount;
        $this->address = $address;
    }
}
