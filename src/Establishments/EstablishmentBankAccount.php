<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class EstablishmentBankAccount
{
    /** @var string */
    public $bankNumber;

    /** @var string */
    public $agencyNumber;

    /** @var string */
    public $accountNumber;

    /** @var string */
    public $accountDigit;

    /** @var int um dos BankAccountType::* */
    public $accountType;

    /** @var string|null */
    public $agencyDigit;

    /** @var string|null obrigatório quando o modelo de desembolso é DisbursementModel::EXTERNAL */
    public $holderName;

    /** @var string|null obrigatório quando o modelo de desembolso é DisbursementModel::EXTERNAL */
    public $holderDocument;

    public function __construct(
        string $bankNumber,
        string $agencyNumber,
        string $accountNumber,
        string $accountDigit,
        int $accountType,
        ?string $agencyDigit = null,
        ?string $holderName = null,
        ?string $holderDocument = null
    ) {
        $this->bankNumber = $bankNumber;
        $this->agencyNumber = $agencyNumber;
        $this->accountNumber = $accountNumber;
        $this->accountDigit = $accountDigit;
        $this->accountType = $accountType;
        $this->agencyDigit = $agencyDigit;
        $this->holderName = $holderName;
        $this->holderDocument = $holderDocument;
    }
}
