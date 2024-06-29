<?php

namespace Reelz222z\Cryptoexchange\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Reelz222z\Cryptoexchange\Model\CoinMarketCapApiClient;
use Reelz222z\Cryptoexchange\Model\TransactionHistory;

class CryptoController
{
    private $client;
    private $apiUrl;
    private $apiKey;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->apiUrl = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
        $this->apiKey = $_ENV['COINMARKETCAP_API_KEY'];
    }

    public function index(Request $request): Response
    {
        $cryptoData = new CoinMarketCapApiClient($this->client, $this->apiUrl, $this->apiKey);
        $topCryptos = $cryptoData->fetchTopCryptocurrencies();
        return new Response(json_encode($topCryptos));
    }

    public function detail(Request $request, $symbol): Response
    {
        $cryptoData = new CoinMarketCapApiClient($this->client, $this->apiUrl, $this->apiKey);
        $crypto = $cryptoData->getCryptocurrencyBySymbol($symbol);
        return new Response(json_encode($crypto));
    }

    public function portfolio(Request $request): Response
    {
        $user = $request->getSession()->get('user');
        $transactions = TransactionHistory::getTransactions($user->getId());
        return new Response(json_encode($transactions));
    }

    public function buy(Request $request): Response
    {
        $symbol = $request->request->get('symbol');
        $amount = (float) $request->request->get('amount');
        $user = $request->getSession()->get('user');
        $cryptoData = new CoinMarketCapApiClient($this->client, $this->apiUrl, $this->apiKey);
        $crypto = $cryptoData->getCryptocurrencyBySymbol($symbol);

        if ($crypto) {
            $user->getWallet()->deduct($crypto->getQuote()->getPrice() * $amount);
            TransactionHistory::addTransaction($user->getId(), $crypto->getSymbol(), $amount, 'buy', $crypto->getQuote()->getPrice());
            return new Response('', 302, ['Location' => '/portfolio']);
        }

        return new Response('Cryptocurrency not found.', 404);
    }

    public function sell(Request $request): Response
    {
        $symbol = $request->request->get('symbol');
        $amount = (float) $request->request->get('amount');
        $user = $request->getSession()->get('user');
        $cryptoData = new CoinMarketCapApiClient($this->client, $this->apiUrl, $this->apiKey);
        $crypto = $cryptoData->getCryptocurrencyBySymbol($symbol);

        // Get user's current holdings
        $transactions = TransactionHistory::getTransactions($user->getId());
        $holdings = [];
        foreach ($transactions as $transaction) {
            if ($transaction['asset'] === $symbol) {
                if ($transaction['transaction_type'] === 'buy') {
                    $holdings[$symbol] = ($holdings[$symbol] ?? 0) + $transaction['amount'];
                } elseif ($transaction['transaction_type'] === 'sell') {
                    $holdings[$symbol] = ($holdings[$symbol] ?? 0) - $transaction['amount'];
                }
            }
        }
        $currentQuantity = $holdings[$symbol] ?? 0;

        if ($crypto) {
            if ($amount > $currentQuantity) {
                return new Response('You do not have enough of this cryptocurrency to sell.', 403);
            }

            $totalEarnings = $crypto->getQuote()->getPrice() * $amount;
            $user->getWallet()->add($totalEarnings);
            TransactionHistory::addTransaction($user->getId(), $crypto->getSymbol(), $amount, 'sell', $crypto->getQuote()->getPrice());
            return new Response('', 302, ['Location' => '/portfolio']);
        }

        return new Response('Cryptocurrency not found.', 404);
    }
}
