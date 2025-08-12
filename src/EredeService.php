<?php

namespace Lcs13761\EredeLaravel;

use Lcs13761\EredeLaravel\Services\CancelTransactionService;
use Lcs13761\EredeLaravel\Services\CaptureTransactionService;
use Lcs13761\EredeLaravel\Services\CreateTransactionService;
use Lcs13761\EredeLaravel\Services\GetTransactionService;

class EredeService
{
    public const VERSION = '6.1.0';
    public const USER_AGENT = 'eRede/' . self::VERSION . ' (PHP %s; Store %s; %s %s) %s';

    protected Store $store;
    private ?string $platform = null;
    private ?string $platformVersion = null;

    public function __construct(private readonly ?string $filiation, private readonly ?string $token)
    {
        $this->store = new Store($this->filiation, $this->token);
    }


    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     * @see    eRede::create()
     */
    public function authorize(Transaction $transaction): Transaction
    {
        return $this->create($transaction);
    }

    /**
     * @param Transaction $transaction
     * @return Transaction
     */
    public function create(Transaction $transaction): Transaction
    {
        $service = new CreateTransactionService($this->store, $transaction);

        $service->platform($this->platform, $this->platformVersion);

        return $service->execute();
    }

    /**
     * @param string $platform
     * @param string $platformVersion
     *
     * @return $this
     */
    public function platform(string $platform, string $platformVersion): static
    {
        $this->platform = $platform;
        $this->platformVersion = $platformVersion;

        return $this;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function cancel(Transaction $transaction): Transaction
    {
        $service = new CancelTransactionService($this->store, $transaction);

        $service->platform($this->platform, $this->platformVersion);

        return $service->execute();
    }

    /**
     * @param string $tid
     *
     * @return Transaction
     * @see    eRede::get()
     */
    public function getById(string $tid): Transaction
    {
        return $this->get($tid);
    }

    /**
     * @param string $tid
     *
     * @return Transaction
     */
    public function get(string $tid): Transaction
    {
        $service = new GetTransactionService(store: $this->store);
        
        $service->platform($this->platform, $this->platformVersion);
        $service->setTid($tid);

        return $service->execute();
    }

    /**
     * @param string $reference
     *
     * @return Transaction
     */
    public function getByReference(string $reference): Transaction
    {
        $service = new GetTransactionService(store: $this->store);
        $service->platform($this->platform, $this->platformVersion);
        $service->setReference($reference);

        return $service->execute();
    }

    /**
     * @param string $tid
     *
     * @return Transaction
     */
    public function getRefunds(string $tid): Transaction
    {
        $service = new GetTransactionService(store: $this->store);
        $service->platform($this->platform, $this->platformVersion);
        $service->setTid($tid);
        $service->setRefund();

        return $service->execute();
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function zero(Transaction $transaction): Transaction
    {
        $amount = (int)$transaction->amount;
        $capture = (bool)$transaction->capture;

        $transaction->setAmount(0);
        $transaction->capture();

        $transaction = $this->create($transaction);

        $transaction->setAmount($amount);
        $transaction->capture($capture);

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function capture(Transaction $transaction): Transaction
    {
        $service = new CaptureTransactionService($this->store, $transaction);

        $service->platform($this->platform, $this->platformVersion);

        return $service->execute();
    }
}